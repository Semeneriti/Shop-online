<?php

function validation()
{
    $errors = [];

    // Проверка имени
    if (isset($_GET['name'])) {
        $name = ($_GET["name"]);
        if (empty($name)) {
            $errors['name'] = 'Введите имя';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Имя должно быть не меньше 2 символов';
        }
    } else {
        $errors['name'] = 'Заполните поле Name';
    }

    // Проверка email
    if (isset($_GET['email'])) {
        $email = ($_GET["email"]);
        if (empty($email)) {
            $errors['email'] = 'Введите Email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Неверный формат Email';
        }
    } else {
        $errors['email'] = 'Введите Email';
    }

    // Проверка пароля
    if (isset($_GET['password'])) {
        $password = $_GET["password"];
        if (empty($password)) {
            $errors['password'] = 'Пароль должен быть заполнен';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Пароль должен быть минимум из 6 символов';
        }
    } else {
        $errors['password'] = 'Пароль должен быть заполнен';
    }

    // Проверка повтора пароля
    if (isset($_GET['passwordRepeat'])) {
        $passwordRepeat = $_GET['passwordRepeat'];
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

// Проверяем отправку формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateUser($_POST);

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

require_once './registration_form.php';
?>