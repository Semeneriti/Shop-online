<?php

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
?>