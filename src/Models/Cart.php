<?php



class Cart extends Model
{
    public function getUserCart($userId)
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*, up.amount, up.id as cart_id 
            FROM user_products up 
            JOIN products p ON up.product_id = p.id 
            WHERE up.user_id = :user_id
        ');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function addToCart($userId, $productId, $amount)
    {
        $stmt = $this->pdo->prepare("INSERT INTO user_products (user_id, product_id, amount) VALUES (:user_id, :product_id, :amount)");
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':amount' => $amount
        ]);
        return true;
    }

    public function getUserProducts($userId)
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*, up.amount 
            FROM user_products up 
            JOIN products p ON up.product_id = p.id 
            WHERE up.user_id = :user_id
        ');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}