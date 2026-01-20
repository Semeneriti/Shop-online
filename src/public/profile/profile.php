<?php

// profile.php
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

require_once './profile_page.php';
