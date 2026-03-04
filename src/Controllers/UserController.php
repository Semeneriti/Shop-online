<?php
namespace Controllers;

// Импортируем необходимые классы
use Models\User;                       // Модель пользователя - для работы с пользователями
use Services\CartService;               // Сервис корзины - для получения информации о корзине
use Services\OrderService;              // Сервис заказов - для получения истории заказов
use Request\RegisterRequest;            // Класс-запрос для регистрации
use Request\LoginRequest;               // Класс-запрос для входа
use Request\UpdateProfileRequest;       // Класс-запрос для обновления профиля

// Контроллер пользователя - наследуется от BaseController
class UserController extends BaseController
{
    // Свойства для хранения сервисов
    private CartService $cartService;    // Для работы с корзиной
    private OrderService $orderService;  // Для работы с заказами

    /**
     * Конструктор - вызывается при создании объекта контроллера
     */
    public function __construct()
    {
        // Вызываем конструктор родительского класса (там создается $this->auth)
        parent::__construct();

        // Создаем объекты сервисов и сохраняем в свойства
        $this->cartService = new CartService();
        $this->orderService = new OrderService();
    }

    /**
     * Отображение формы регистрации
     * GET /registration
     */
    public function getRegistrate(): void
    {
        // Если пользователь уже авторизован - отправляем в каталог
        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        // Подключаем шаблон регистрации
        require_once __DIR__ . '/../Views/registration.php';
    }

    /**
     * Обработка регистрации нового пользователя
     * POST /registration
     */
    public function registrate(RegisterRequest $request): void
    {
        // Проверяем, что запрос отправлен методом POST
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect('/registration');
        }

        // Если пользователь уже авторизован - отправляем в каталог
        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        // Получаем ошибки валидации из объекта запроса
        $errors = $request->getErrors();

        // Если ошибок нет - пытаемся зарегистрировать пользователя
        if (empty($errors)) {
            // Получаем данные из запроса
            $name = $request->getName();
            $email = $request->getEmail();
            $password = $request->getPassword();

            // Проверяем, не занят ли email
            $existingUser = User::findByEmail($email);

            if ($existingUser) {
                // Если email уже используется - добавляем ошибку
                $errors['email'] = 'Пользователь с таким email уже существует';
            } else {
                // Хешируем пароль
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Создаем нового пользователя
                $user = new User($name, $email, $passwordHash);

                // Сохраняем пользователя в базу данных
                if ($user->save()) {
                    // Если успешно - авторизуем пользователя и отправляем в каталог
                    $this->auth->login($user);
                    $this->auth->redirect('/catalog');
                } else {
                    // Если ошибка при сохранении
                    $errors[] = 'Ошибка при создании пользователя';
                }
            }
        }

        // Если есть ошибки - показываем форму регистрации снова
        require_once __DIR__ . '/../Views/registration.php';
    }

    /**
     * Вход пользователя в систему
     * GET/POST /login
     */
    public function login(LoginRequest $request): void
    {
        // Если пользователь уже авторизован - отправляем в каталог
        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        // Получаем ошибки валидации из запроса
        $errors = $request->getErrors();

        // Если это POST-запрос и нет ошибок валидации
        if ($this->auth->isPostRequest() && empty($errors)) {
            // Получаем данные из запроса
            $email = $request->getEmail();
            $password = $request->getPassword();

            // Ищем пользователя по email
            $user = User::findByEmail($email);

            // Проверяем, существует ли пользователь и правильный ли пароль
            if (!$user) {
                // Пользователь не найден
                $errors['general'] = 'Неверный email или пароль';
            } elseif ($user->verifyPassword($password)) {
                // Пароль правильный - авторизуем пользователя
                $this->auth->login($user);
                $this->auth->redirect('/catalog');
            } else {
                // Пароль неправильный
                $errors['general'] = 'Неверный email или пароль';
            }
        }

        // Подключаем шаблон входа (для GET-запроса или если есть ошибки)
        require_once __DIR__ . '/../Views/login.php';
    }

    /**
     * Выход пользователя из системы
     * GET /logout
     */
    public function logout(): void
    {
        // Разлогиниваем пользователя
        $this->auth->logout();

        // Отправляем на страницу входа
        $this->auth->redirect("/login");
    }

    /**
     * Отображение профиля пользователя
     * GET /profile
     */
    public function getProfile(): void
    {
        // Проверяем, что пользователь авторизован
        $this->auth->requireAuth();

        // Получаем ID текущего пользователя
        $userId = $this->auth->getUserId();

        // Получаем товары в корзине пользователя
        $cartItems = $this->cartService->getCartItems($userId);

        // Получаем историю заказов пользователя
        $orders = $this->orderService->getOrdersByUserId($userId);

        // Подключаем шаблон профиля
        require_once __DIR__ . '/../Views/profile.php';
    }

    /**
     * Отображение формы редактирования профиля
     * GET /edit-profile
     */
    public function showEditForm(): void
    {
        // Проверяем, что пользователь авторизован
        $this->auth->requireAuth();

        // Получаем данные текущего пользователя в виде массива
        $userData = $this->auth->getCurrentUser()->toArray();

        // Подключаем шаблон редактирования профиля
        require_once __DIR__ . '/../Views/edit_profile.php';
    }

    /**
     * Обработка обновления профиля
     * POST /edit-profile
     */
    public function updateProfile(UpdateProfileRequest $request): void
    {
        // Проверяем, что пользователь авторизован
        $this->auth->requireAuth();

        // Проверяем, что запрос отправлен методом POST
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/edit-profile");
        }

        // Получаем ошибки валидации из запроса
        $errors = $request->getErrors();

        // Получаем текущего пользователя
        $user = $this->auth->getCurrentUser();

        // Если ошибок валидации нет
        if (empty($errors)) {
            // Получаем данные из запроса
            $name = $request->getName();
            $email = $request->getEmail();

            // Если email изменился - проверяем, не занят ли он другим пользователем
            if ($email !== $user->getEmail()) {
                $existingUser = User::findByEmail($email);
                if ($existingUser) {
                    // Email уже используется
                    $errors['email'] = 'Этот email уже используется другим пользователем';
                }
            }

            // Если после проверки email ошибок нет
            if (empty($errors)) {
                try {
                    // Получаем пароль (если ввели новый)
                    $password = $request->getPassword();

                    // Обновляем профиль пользователя
                    $user->updateProfile($name, $email, !empty($password) ? $password : null);

                    // Обновляем имя в сессии
                    $this->auth->setSessionValue('userName', $name);

                    // Устанавливаем сообщение об успехе
                    $this->auth->setSessionValue('success_message', 'Профиль успешно обновлен');

                    // Отправляем на страницу профиля
                    $this->auth->redirect("/profile");
                } catch (\Exception $e) {
                    // Ошибка при обновлении
                    $errors[] = 'Ошибка при обновлении профиля: ' . $e->getMessage();
                }
            }
        }

        // Если есть ошибки - показываем форму редактирования снова
        $userData = $user->toArray();
        require_once __DIR__ . '/../Views/edit_profile.php';
    }
}