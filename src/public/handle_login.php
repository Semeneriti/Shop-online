<?php

$errors = [];

// Проверка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {

    // Проверка имени
    if (empty($_GET['usrnm'])) {
        $errors['name'] = 'Напишите имя';
    }

    // Проверка email
    if (empty($_GET['email'])) {
        $errors['email'] = 'Напишите email';
    } elseif (!strpos($_GET['email'], '@')) {
        $errors['email'] = 'Email должен содержать @';
    }

    // Проверка пароля
    if (empty($_GET['psw'])) {
        $errors['password'] = 'Напишите пароль';
    } elseif (strlen($_GET['psw']) < 3) {
        $errors['password'] = 'Пароль слишком короткий';
    }

    // Проверка повтора пароля
    if (empty($_GET['psw-repeat'])) {
        $errors['passwordRepeat'] = 'Повторите пароль';
    } elseif ($_GET['psw'] !== $_GET['psw-repeat']) {
        $errors['passwordRepeat'] = 'Пароли не совпадают';
    }


$email = $_POST['email'];
$password = $_POST['password'];

$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');

$stmt->execute(['email' => $email]);

$user = $stmt->fetch();

$errors = [];

if($user === false){
    $errors[] = "Пользователь не найден";
}
else{
    $passwordDB = $user['password'];

    if(password_verify($password, $passwordDB)) {

        header("Location: /.catalog.php");
    }else{
        $errors['username'] = 'username or password incorrect';
    }
}
return $errors;

require_once './login_form.php';}