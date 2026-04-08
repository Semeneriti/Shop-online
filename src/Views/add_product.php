<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить товар в корзину</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        form { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h2 { text-align: center; margin-bottom: 25px; }
        .input-container { margin-bottom: 15px; }
        .input-field { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .error { color: red; margin-bottom: 5px; font-size: 13px; }
    </style>
</head>
<body>
<form action="/add-product" method="POST">
    <h2>Добавить товар в корзину</h2>

    <?php if (isset($errors['product-id'])): ?>
        <div class="error"><?= $errors['product-id'] ?></div>
    <?php endif; ?>
    <div class="input-container">
        <input class="input-field" type="text" placeholder="ID товара" name="product-id" required>
    </div>

    <?php if (isset($errors['amount'])): ?>
        <div class="error"><?= $errors['amount'] ?></div>
    <?php endif; ?>
    <div class="input-container">
        <input class="input-field" type="number" placeholder="Количество" name="amount" required min="1">
    </div>

    <button type="submit" class="btn">Добавить в корзину</button>
</form>
</body>
</html>
42. Views/product.php