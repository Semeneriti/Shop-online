<?php
/** @var array $cartData */
$cartItems = $cartData['items'] ?? [];
$totalPrice = $cartData['total_price'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 25px;
            font-size: 28px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            color: #666;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .total-row td {
            font-weight: bold;
            color: #2c3e50;
            font-size: 16px;
            border-top: 2px solid #dee2e6;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-green {
            background-color: #27ae60;
        }

        .btn-green:hover {
            background-color: #229954;
        }

        .btn-red {
            background-color: #e74c3c;
        }

        .btn-red:hover {
            background-color: #c0392b;
        }

        .btn-clear {
            background-color: #e74c3c;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-clear:hover {
            background-color: #c0392b;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }

        .actions-left {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .checkout-link {
            display: inline-block;
            padding: 12px 30px;
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .checkout-link:hover {
            background-color: #229954;
        }

        .continue-link {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
        }

        .continue-link:hover {
            text-decoration: underline;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-form {
            display: inline;
        }

        .quantity-btn {
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🛒 Моя корзина</h1>

    <?php
    $successMessage = $_SESSION['success_message'] ?? '';
    $errorMessage = $_SESSION['error_message'] ?? '';

    if ($successMessage):
        unset($_SESSION['success_message']);
        ?>
        <div class="success-message"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage):
        unset($_SESSION['error_message']);
        ?>
        <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($cartItems)): ?>
        <table>
            <thead>
            <tr>
                <th>Товар</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Сумма</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($cartItems as $item): ?>
                <?php
                // Инициализируем переменные значениями по умолчанию
                $product = null;
                $amount = 1;
                $subtotal = 0;

                // Пытаемся извлечь данные из разных форматов
                if (is_object($item)) {
                    if (isset($item->product) && is_object($item->product)) {
                        // Объект со свойством product
                        $product = $item->product;
                        $amount = $item->amount ?? 1;
                    } elseif (method_exists($item, 'getName')) {
                        // Прямой объект Product
                        $product = $item;
                    }
                } elseif (is_array($item)) {
                    if (isset($item['product']) && is_object($item['product'])) {
                        // Массив с ключом 'product'
                        $product = $item['product'];
                        $amount = $item['amount'] ?? 1;
                    } elseif (isset($item['name'])) {
                        // Массив с данными товара
                        $product = (object)$item; // Преобразуем в объект
                    }
                }

                // Если не удалось получить продукт, пропускаем итерацию
                if (!$product || !method_exists($product, 'getName')) {
                    continue;
                }

                // Вычисляем subtotal если не задан
                if (!isset($subtotal) || $subtotal == 0) {
                    $subtotal = $product->getPrice() * $amount;
                }
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($product->getName()) ?></strong>
                    </td>
                    <td><?= number_format($product->getPrice(), 2, '.', ' ') ?> ₽</td>
                    <td>
                        <div class="quantity-control">
                            <form action="/cart/decrease" method="POST" class="quantity-form">
                                <input type="hidden" name="product_id" value="<?= $product->getId() ?>">
                                <button type="submit" class="btn btn-red quantity-btn">−</button>
                            </form>
                            <span style="font-weight: bold;"><?= $amount ?></span>
                            <form action="/cart/increase" method="POST" class="quantity-form">
                                <input type="hidden" name="product_id" value="<?= $product->getId() ?>">
                                <button type="submit" class="btn btn-green quantity-btn">+</button>
                            </form>
                        </div>
                    </td>
                    <td><strong><?= number_format($subtotal, 2, '.', ' ') ?> ₽</strong></td>
                    <td>
                        <form action="/cart/remove" method="POST">
                            <input type="hidden" name="product_id" value="<?= $product->getId() ?>">
                            <button type="submit" class="btn btn-red">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;"><strong>Итого:</strong></td>
                <td><strong><?= number_format($totalPrice, 2, '.', ' ') ?> ₽</strong></td>
                <td></td>
            </tr>
            </tbody>
        </table>

        <div class="actions">
            <div class="actions-left">
                <a href="/catalog" class="continue-link">← Продолжить покупки</a>
                <form action="/cart/clear" method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите очистить корзину?');">
                    <button type="submit" class="btn-clear">🗑️ Очистить корзину</button>
                </form>
            </div>
            <a href="/checkout" class="checkout-link">✅ Оформить заказ</a>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <p style="font-size: 18px; margin-bottom: 20px;">Ваша корзина пуста</p>
            <p style="margin-bottom: 30px;">Добавьте товары из каталога</p>
            <a href="/catalog" class="btn">Перейти в каталог</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>