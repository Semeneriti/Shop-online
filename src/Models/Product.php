<?php

require_once __DIR__ . '/Model.php';

class Product extends Model
{
    public function getAll()
    {
        $stmt = $this->pdo->query('SELECT * FROM products');
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
