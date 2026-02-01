<?php
namespace Controllers;

use Models\User;
use Models\Cart;
use Models\Order;

class UserController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function getRegistrate(): void
    {
        if (isset($_SESSION['userId'])) {
            header('Location: /catalog');
            exit;
        }

        require_once __DIR__ . '/../Views/registration.php';
    }

    public function registrate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /registration');
            exit;
        }

        $errors = $this->validate($_POST);

        if (empty($errors)) {
            $name = htmlspecialchars(trim($_POST["name"]));
            $email = htmlspecialchars(trim($_POST["email"]));
            $password = $_POST["password"];

            // Проверяем, существует ли пользователь
            $existingUser = User::findByEmail($email);

            if ($existingUser) {
                $errors['email'] = 'Пользователь с таким email уже существует';
            } else {
                // Создаем нового пользователя
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $user = new User($name, $email, $passwordHash);

                if ($user->save()) {
                    // Автоматический вход после регистрации
                    $_SESSION['userId'] = $user->getId();
                    $_SESSION['userName'] = $user->getName();

                    header('Location: /catalog');
                    exit;
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
        if (isset($_SESSION['userId'])) {
            header('Location: /catalog');
            exit;
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = htmlspecialchars(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';

            // Валидация
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
                    // Успешный вход
                    $_SESSION['userId'] = $user->getId();
                    $_SESSION['userName'] = $user->getName();

                    header('Location: /catalog');
                    exit;
                } else {
                    $errors['general'] = 'Неверный email или пароль';
                }
            }
        }

        require_once __DIR__ . '/../Views/login.php';
    }

    public function logout(): void
    {
        // Очищаем сессию
        $_SESSION = [];

        // Удаляем куки сессии
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: /login");
        exit;
    }

    public function getProfile(): void
    {
        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $user = User::findById($userId);

        if (!$user) {
            $this->logout();
            exit;
        }

        // Получаем корзину пользователя
        $cart = new Cart($userId);
        $cartItems = $cart->getItems();

        // Получаем заказы пользователя
        $orders = Order::findByUserId($userId);

        require_once __DIR__ . '/../Views/profile.php';
    }

    public function showEditForm(): void
    {
        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $user = User::findById($userId);

        if (!$user) {
            $this->logout();
            exit;
        }

        // Передаем данные пользователя в форму
        $userData = $user->toArray();

        require_once __DIR__ . '/../Views/edit_profile.php';
    }

    public function updateProfile(): void
    {
        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $user = User::findById($userId);

        if (!$user) {
            $this->logout();
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /edit-profile");
            exit;
        }

        $errors = [];
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Валидация
        if (empty($name)) {
            $errors['name'] = 'Введите имя';
        }

        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Неверный формат email';
        }

        // Проверка email на уникальность (если изменился)
        if ($email !== $user->getEmail()) {
            $existingUser = User::findByEmail($email);
            if ($existingUser) {
                $errors['email'] = 'Этот email уже используется другим пользователем';
            }
        }

        // Проверка пароля (если указан)
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors['password'] = 'Пароль должен быть не менее 6 символов';
            } elseif ($password !== $passwordConfirm) {
                $errors['password_confirm'] = 'Пароли не совпадают';
            }
        }

        if (empty($errors)) {
            // Обновляем профиль
            try {
                $user->updateProfile($name, $email, !empty($password) ? $password : null);

                // Обновляем имя в сессии
                $_SESSION['userName'] = $name;

                // Редирект на профиль с сообщением об успехе
                $_SESSION['success_message'] = 'Профиль успешно обновлен';
                header("Location: /profile");
                exit;
            } catch (\Exception $e) {
                $errors[] = 'Ошибка при обновлении профиля: ' . $e->getMessage();
            }
        }

        // Если есть ошибки, показываем форму снова
        require_once __DIR__ . '/../Views/edit_profile.php';
    }

    private function validate(array $data): array
    {
        $errors = [];

        // Валидация имени
        if (isset($data['name'])) {
            $name = htmlspecialchars(trim($data["name"]));
            if (empty($name)) {
                $errors['name'] = 'Введите имя';
            } elseif (strlen($name) < 2) {
                $errors['name'] = 'Имя должно быть не меньше 2 символов';
            }
        } else {
            $errors['name'] = 'Заполните поле Name';
        }

        // Валидация email
        if (isset($data['email'])) {
            $email = htmlspecialchars(trim($data["email"]));
            if (empty($email)) {
                $errors['email'] = 'Введите Email';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Неверный формат Email';
            }
        } else {
            $errors['email'] = 'Введите Email';
        }

        // Валидация пароля
        if (isset($data['password'])) {
            $password = $data["password"];
            if (empty($password)) {
                $errors['password'] = 'Пароль должен быть заполнен';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Пароль должен быть минимум из 6 символов';
            }
        } else {
            $errors['password'] = 'Пароль должен быть заполнен';
        }

        // Подтверждение пароля
        if (isset($data['passwordRepeat'])) {
            $passwordRepeat = $data['passwordRepeat'];
            if (empty($passwordRepeat)) {
                $errors['password_confirm'] = 'Подтвердите пароль';
            } elseif (isset($password) && $password !== $passwordRepeat) {
                $errors['passwordRepeat'] = 'Пароли не совпадают';
            }
        } else {
            $errors['passwordRepeat'] = 'Подтвердите пароль';
        }

        return $errors;
    }
}