<?php

session_start();
$userID = $_SESSION['userID'];



$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");

$stmt = $pdo->query('SELECT * FROM products');


$products =$stmt ->fetchAll();



require_once './catalog_page.php';