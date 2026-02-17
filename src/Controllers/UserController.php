<?php
namespace Controllers;

use Models\User;
use Services\CartService;
use Services\OrderService;

class UserController extends BaseController
{
    private CartService $cartService;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->cartService = new CartService();
        $this->orderService = new OrderService();
    }

    public function getRegistrate(): void
    {
        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        require_once __DIR__ . '/../Views/registration.php';
    }

    public function registrate(): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect('/registration');
        }

        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        $errors = [];

        // Получаем данные из формы с правильными именами
        $name = $this->auth->getPostString('name');
        $email = $this->auth->getPostString('email');
        $password = $this->auth->getPostParam('password');
        $passwordRepeat = $this->auth->getPostParam('passwordRepeat');

        // Валидация
        if (empty($name)) {
            $errors['name'] = 'Введите имя';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Имя должно быть не меньше 2 символов';
        }

        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Неверный формат email';
        }

        if (empty($password)) {
            $errors['password'] = 'Введите пароль';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Пароль должен быть минимум 6 символов';
        }

        if (empty($passwordRepeat)) {
            $errors['passwordRepeat'] = 'Подтвердите пароль';
        } elseif ($password !== $passwordRepeat) {
            $errors['passwordRepeat'] = 'Пароли не совпадают';
        }

        if (empty($errors)) {
            // Проверяем, существует ли пользователь
            $existingUser = User::findByEmail($email);

            if ($existingUser) {
                $errors['email'] = 'Пользователь с таким email уже существует';
            } else {
                // Хешируем пароль и создаем пользователя
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $user = new User($name, $email, $passwordHash);

                if ($user->save()) {
                    $this->auth->login($user);
                    $this->auth->redirect('/catalog');
                } else {
                    $errors[] = 'Ошибка при создании пользователя';
                }
            }
        }

        // Если есть ошибки, показываем форму снова
        require_once __DIR__ . '/../Views/registration.php';
    }

    public function login(): void
    {
        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        $errors = [];

        if ($this->auth->isPostRequest()) {
            $email = $this->auth->getPostString('email');
            $password = $this->auth->getPostParam('password');

            if (empty($email)) {
                $errors['email'] = 'Введите email';
            }

            if (empty($password)) {
                $errors['password'] = 'Введите пароль';
            }

            if (empty($errors)) {
                $user = User::findByEmail($email);

                if (!$user) {
                    $errors['general'] = 'Неверный email или пароль';
                } elseif ($user->verifyPassword($password)) {
                    $this->auth->login($user);
                    $this->auth->redirect('/catalog');
                } else {
                    $errors['general'] = 'Неверный email или пароль';
                }
            }
        }

        require_once __DIR__ . '/../Views/login.php';
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->auth->redirect("/login");
    }

    public function getProfile(): void
    {
        $this->auth->requireAuth();

        $userId = $this->auth->getUserId();
        $cartItems = $this->cartService->getCartItems($userId);
        $orders = $this->orderService->getOrdersByUserId($userId);

        require_once __DIR__ . '/../Views/profile.php';
    }

    public function showEditForm(): void
    {
        $this->auth->requireAuth();

        $userData = $this->auth->getCurrentUser()->toArray();

        require_once __DIR__ . '/../Views/edit_profile.php';
    }

    public function updateProfile(): void
    {
        $this->auth->requireAuth();

        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/edit-profile");
        }

        $errors = [];
        $name = $this->auth->getPostString('name');
        $email = $this->auth->getPostString('email');
        $password = $this->auth->getPostParam('password');
        $passwordConfirm = $this->auth->getPostParam('password_confirm');

        $user = $this->auth->getCurrentUser();

        if (empty($name)) {
            $errors['name'] = 'Введите имя';
        }

        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Неверный формат email';
        }

        if ($email !== $user->getEmail()) {
            $existingUser = User::findByEmail($email);
            if ($existingUser) {
                $errors['email'] = 'Этот email уже используется другим пользователем';
            }
        }

        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors['password'] = 'Пароль должен быть не менее 6 символов';
            } elseif ($password !== $passwordConfirm) {
                $errors['password_confirm'] = 'Пароли не совпадают';
            }
        }

        if (empty($errors)) {
            try {
                $user->updateProfile($name, $email, !empty($password) ? $password : null);

                $this->auth->setSessionValue('userName', $name);

                $this->auth->setSessionValue('success_message', 'Профиль успешно обновлен');
                $this->auth->redirect("/profile");
            } catch (\Exception $e) {
                $errors[] = 'Ошибка при обновлении профиля: ' . $e->getMessage();
            }
        }

        $userData = $user->toArray();
        require_once __DIR__ . '/../Views/edit_profile.php';
    }
}