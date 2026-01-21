<?php

class User
{
    public function getRegistrate()
    {
        session_start();
        if(isset($_SESSION['userID'])){
            header('Location: ./catalog');
        }

        require_once 'registration/Registrate.php';
    }

    public function registrate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validate($_POST);

            if (empty($errors)) {
                $name = ($_POST["name"]);
                $email = ($_POST["email"]);
                $password = $_POST["password"];

                try {
                    $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Проверяем, не существует ли уже пользователь с таким email
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
                    $stmt->execute([':email' => $email]);

                    if ($stmt->fetch()) {
                        $errors['email'] = 'Пользователь с таким email уже существует';
                    } else {
                        // Вставляем нового пользователя
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
                        $stmt->execute([
                            ':name' => $name,
                            ':email' => $email,
                            ':password' => password_hash($password, PASSWORD_DEFAULT)
                        ]);

                        exit;
                    }
                } catch (PDOException $e) {
                    $errors['database'] = 'Ошибка базы данных: ' . $e->getMessage();
                }
            }
        }

        require_once 'registration_form.php';
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
                $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");
                $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
                $stmt->execute(['email' => $email]);

                $user = $stmt->fetch();

                if ($user === false) {
                    $errors[] = "Пользователь не найден";
                } else {
                    $passwordDB = $user['password'];

                    if (password_verify($password, $passwordDB)) {
                        session_start();
                        $_SESSION['userId'] = $user['id'];
                        header("Location: /catalog");
                        exit;
                    } else {
                        $errors[] = 'Неверный email или пароль';
                    }
                }
            }
        }

        require_once 'login_form.php';
    }

    public function getProfile()
    {
        session_start();

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];

        $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");

        // Получаем данные пользователя
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        // Получаем товары пользователя
        $stmt = $pdo->prepare('
            SELECT p.*, up.amount 
            FROM user_products up 
            JOIN products p ON up.product_id = p.id 
            WHERE up.user_id = :user_id
        ');
        $stmt->execute(['user_id' => $userId]);
        $userProducts = $stmt->fetchAll();

        require_once __DIR__ . '/../profile/profile_page.php';
    }

    public function updateProfile()
    {
        session_start();

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");

        if (!empty($password)) {
            // Обновляем с паролем
            $sql = "UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':id' => $userId
            ]);
        } else {
            // Обновляем без пароля
            $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':id' => $userId
            ]);
        }

        echo "Profile updated successfully!";
        echo "<br><a href='/profile'>Back to Profile</a>";
    }

    private function validate(array $data): array
    {
        $errors = [];

        // Проверка имени
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

        // Проверка email
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

        // Проверка пароля
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

        // Проверка повтора пароля
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