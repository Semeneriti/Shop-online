<?php

print_r($_GET);

$name = $_GET['name'];
$email = $_GET['email'];
$password = $_GET['password'];
$passwordRepeat = $_GET['passwordRepeat'];

$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");

$pdo->exec("INSERT INTO users (name, email, password) VALUES ('name', 'email', 'password','passwordRepeat')");