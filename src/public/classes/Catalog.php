<?php

class Catalog
{
    public function __construct()
    {
        session_start();
        $userID = $_SESSION['userId'] ?? null;

        $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");
        $stmt = $pdo->query('SELECT * FROM products');
        $products = $stmt->fetchAll();

        require_once __DIR__ . '/../Views/catalog_page.php';
    }
}
