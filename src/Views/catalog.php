<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог товаров</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .cart-badge { background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; margin-left: 5px; }
        .cart-info { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .product-card { background: white; border-radius: 8px; overflow: hidden; transition: transform 0.3s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .product-image { text-align: center; padding: 15px; background: #f9f9f9; }
        .product-image img { max-width: 180px; max-height: 180px; object-fit: contain; }
        .product-body { padding: 20px; }
        .product-name { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #2c3e50; }
        .product-description { color: #7f8c8d; font-size: 13px; margin-bottom: 15px; height: 60px; overflow: hidden; }
        .product-footer { padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #dee2e6; }
        .product-price { font-size: 20px; font-weight: bold; color: #27ae60; margin-bottom: 10px; }
        .cart-control { display: flex; align-items: center; gap: 10px; justify-content: center; }
        .cart-amount { padding: 5px 15px; background: #27ae60; color: white; border-radius: 3px; min-width: 40px; text-align: center; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-green { background: #27ae60; color: white; }
        .btn-red { background: #e74c3c; color: white; }
        .btn-sm { padding: 5px 10px; }
        .rating { color: #f39c12; margin-top: 5px; font-size: 14px; }
        .product-link { text-decoration: none; color: inherit; }
        .add-form { display: flex; gap: 5px; align-items: center; }
        .add-form input { width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Каталог товаров</h1>
        <div class="nav">
            <a href="/cart">Корзина <span id="cart-badge" class="cart-badge" style="display: <?= $cartItemsCount > 0 ? 'inline-block' : 'none' ?>"><?= $cartItemsCount ?></span></a>
            <?php if (isset($_SESSION['userId'])): ?>
                <a href="/profile">Профиль</a>
                <a href="/logout">Выход</a>
            <?php else: ?>
                <a href="/login">Вход</a>
                <a href="/registration">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['userId'])): ?>
        <div class="cart-info">
            <?php if ($cartItemsCount > 0): ?>
                <span>В корзине: <span class="cart-count"><?= $cartItemsCount ?></span> товаров на сумму <span class="cart-total"><?= number_format($cartTotalPrice, 2, '.', ' ') ?></span> ₽</span>
                <a href="/cart">Перейти в корзину →</a>
            <?php else: ?>
                <span>Корзина пуста</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="products-grid" id="products-grid">
        <?php foreach ($products as $product): ?>
            <?php
            $inCart = false;
            $cartAmount = 0;
            foreach ($cartItems as $cartItem) {
                $cartItemProduct = $cartItem['product'];
                $cartItemId = is_array($cartItemProduct) ? ($cartItemProduct['id'] ?? 0) : $cartItemProduct->getId();
                if ($cartItemId == $product->getId()) {
                    $inCart = true;
                    $cartAmount = $cartItem['amount'];
                    break;
                }
            }
            ?>
            <div class="product-card" data-product-id="<?= $product->getId() ?>">
                <div class="product-image">
                    <a href="/product?id=<?= $product->getId() ?>">
                        <img src="<?= htmlspecialchars($product->getImageUrl()) ?>" alt="<?= htmlspecialchars($product->getName()) ?>">
                    </a>
                </div>
                <a href="/product?id=<?= $product->getId() ?>" class="product-link">
                    <div class="product-body">
                        <div class="product-name"><?= htmlspecialchars($product->getName()) ?></div>
                        <div class="product-description"><?= htmlspecialchars(substr($product->getDescription(), 0, 100)) ?>...</div>
                        <div class="rating">⭐ <?= $product->getAverageRating() ?> (<?= count($product->getReviews()) ?> отзывов)</div>
                    </div>
                </a>
                <div class="product-footer">
                    <div class="product-price"><?= number_format($product->getPrice(), 2, '.', ' ') ?> ₽</div>
                    <?php if (isset($_SESSION['userId'])): ?>
                        <div class="product-actions">
                            <?php if ($inCart && $cartAmount > 0): ?>
                                <div class="cart-control">
                                    <button class="btn btn-red btn-sm ajax-decrease" data-product-id="<?= $product->getId() ?>">-</button>
                                    <span class="cart-amount" data-product-id="<?= $product->getId() ?>"><?= $cartAmount ?></span>
                                    <button class="btn btn-green btn-sm ajax-increase" data-product-id="<?= $product->getId() ?>">+</button>
                                    <button class="btn btn-red btn-sm ajax-remove" data-product-id="<?= $product->getId() ?>">🗑</button>
                                </div>
                            <?php else: ?>
                                <div class="add-form">
                                    <input type="number" class="amount-input" data-product-id="<?= $product->getId() ?>" value="1" min="1" style="width: 60px; padding: 5px;">
                                    <button class="btn btn-green btn-add" data-product-id="<?= $product->getId() ?>">Добавить</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        function updateCartBadge(count, total) {
            $('#cart-badge').text(count).css('display', count > 0 ? 'inline-block' : 'none');
            $('.cart-count').text(count);
            $('.cart-total').text(total.toFixed(2).replace('.', ',') + ' ₽');
        }

        function updateProductCard(productId, newAmount, cartCount, cartTotal) {
            var card = $('.product-card[data-product-id="' + productId + '"]');
            var actionsDiv = card.find('.product-actions');

            if (newAmount > 0) {
                var newHtml = '<div class="cart-control">' +
                    '<button class="btn btn-red btn-sm ajax-decrease" data-product-id="' + productId + '">-</button>' +
                    '<span class="cart-amount" data-product-id="' + productId + '">' + newAmount + '</span>' +
                    '<button class="btn btn-green btn-sm ajax-increase" data-product-id="' + productId + '">+</button>' +
                    '<button class="btn btn-red btn-sm ajax-remove" data-product-id="' + productId + '">🗑</button>' +
                    '</div>';
                actionsDiv.html(newHtml);
                bindCardEvents(card);
            } else {
                var newHtml = '<div class="add-form">' +
                    '<input type="number" class="amount-input" data-product-id="' + productId + '" value="1" min="1" style="width: 60px; padding: 5px;">' +
                    '<button class="btn btn-green btn-add" data-product-id="' + productId + '">Добавить</button>' +
                    '</div>';
                actionsDiv.html(newHtml);
                card.find('.btn-add').off('click').click(function(e) {
                    e.preventDefault();
                    var pid = $(this).data('product-id');
                    var amount = card.find('.amount-input').val();
                    addToCart(pid, amount);
                });
            }
            updateCartBadge(cartCount, cartTotal);
        }

        function addToCart(productId, amount) {
            $.ajax({
                type: "POST",
                url: "/api/cart/add",
                data: { product_id: productId, amount: amount },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Ошибка при добавлении товара');
                }
            });
        }

        function bindCardEvents(card) {
            card.find('.ajax-increase').off('click').click(function() {
                var productId = $(this).data('product-id');
                $.ajax({
                    type: "POST", url: "/api/cart/increase", data: { product_id: productId }, dataType: 'json',
                    success: function(response) { if (response.success) location.reload(); else alert(response.error); }
                });
            });
            card.find('.ajax-decrease').off('click').click(function() {
                var productId = $(this).data('product-id');
                $.ajax({
                    type: "POST", url: "/api/cart/decrease", data: { product_id: productId }, dataType: 'json',
                    success: function(response) { if (response.success) location.reload(); else alert(response.error); }
                });
            });
            card.find('.ajax-remove').off('click').click(function() {
                var productId = $(this).data('product-id');
                if (confirm('Удалить товар?')) {
                    $.ajax({
                        type: "POST", url: "/api/cart/remove", data: { product_id: productId }, dataType: 'json',
                        success: function(response) { if (response.success) location.reload(); else alert(response.error); }
                    });
                }
            });
        }

        $('.btn-add').click(function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var amount = $(this).closest('.product-card').find('.amount-input').val();
            addToCart(productId, amount);
        });
    });
</script>
</body>
</html>

