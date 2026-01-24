<?php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Cart.php';

class UserController
{
    private $userModel;
    private $cartModel;

    public function __construct()
    {
        session_start();
        $this->userModel = new User();
        $this->cartModel = new Cart();
    }

    public function getRegistrate()
    {
        if (isset($_SESSION['userId'])) {
            header('Location: /catalog');
        }

        require_once __DIR__ . '/../Views/registration.php';
    }

    public function registrate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validate($_POST);

            if (empty($errors)) {
                $name = ($_POST["name"]);
                $email = ($_POST["email"]);
                $password = $_POST["password"];

                $existingUser = $this->userModel->findByEmail($email);

                if ($existingUser) {
                    $errors['email'] = 'Пользователь с таким email уже существует';
                } else {
                    $this->userModel->create($name, $email, $password);
                    exit;
                }
            }
        }

        require_once __DIR__ . '/../Views/registration.php';
    }

    public function login()
    {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email)) {
                $errors['email'] = 'Введите email';
            }

            if (empty($password)) {
                $errors['password'] = 'Введите пароль';
            }

            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);

                if ($user === false) {
                    $errors[] = "Пользователь не найден";
                } else {
                    $passwordDB = $user['password'];

                    if (password_verify($password, $passwordDB)) {
                        $_SESSION['userId'] = $user['id'];
                        header("Location: /catalog");
                        exit;
                    } else {
                        $errors[] = 'Неверный email или пароль';
                    }
                }
            }
        }

        require_once __DIR__ . '/../Views/login.php';
    }

    public function logout()
    {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        header("Location: /login");
        exit;
    }

    public function getProfile()
    {
        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $user = $this->userModel->findById($userId);
        $userProducts = $this->cartModel->getUserProducts($userId);

        require_once __DIR__ . '/../Views/profile.php';
    }

    public function showEditForm()
    {
        require_once __DIR__ . '/../Views/edit_profile.php';
    }

    public function updateProfile()
    {
        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $this->userModel->update($userId, $name, $email, $password);

        echo "Profile updated successfully!";
        echo "<br><a href='/profile'>Back to Profile</a>";
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (isset($data['name'])) {
            $name = ($data["name"]);
            if (empty($name)) {
                $errors['name'] = 'Введите имя';
            } elseif (strlen($name) < 2) {
                $errors['name'] = 'Имя должно быть не меньше 2 символов';
            }
        } else {
            $errors['name'] = 'Заполните поле Name';
        }

        if (isset($data['email'])) {
            $email = ($data["email"]);
            if (empty($email)) {
                $errors['email'] = 'Введите Email';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Неверный формат Email';
            }
        } else {
            $errors['email'] = 'Введите Email';
        }

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