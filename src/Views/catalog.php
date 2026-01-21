<div class="container">
    <!-- Блок информации о корзине -->
    <?php if (isset($userID) && $cartItemsCount > 0): ?>
        <div class="cart-info" style="background: #e8f5e9; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #4caf50;">
            <h4 style="margin: 0; color: #2e7d32;">
                🛒 В корзине <?php echo $cartItemsCount; ?> товар(ов) на $<?php echo $cartTotalPrice; ?>
                <a href="/cart" style="float: right; color: #fff; background: #4caf50; padding: 5px 15px; border-radius: 3px; text-decoration: none;">
                    Перейти в корзину →
                </a>
            </h4>
        </div>
    <?php elseif (isset($userID)): ?>
        <div class="cart-info" style="background: #fff3e0; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ff9800;">
            <h4 style="margin: 0; color: #ef6c00;">
                🛒 Ваша корзина пуста
                <a href="#products" style="float: right; color: #fff; background: #ff9800; padding: 5px 15px; border-radius: 3px; text-decoration: none;">
                    Выберите товары ↓
                </a>
            </h4>
        </div>
    <?php else: ?>
        <div class="cart-info" style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #9e9e9e;">
            <h4 style="margin: 0; color: #616161;">
                🔐 Авторизуйтесь для добавления товаров в корзину
                <a href="/login" style="float: right; color: #fff; background: #2196f3; padding: 5px 15px; border-radius: 3px; text-decoration: none;">
                    Войти в систему
                </a>
            </h4>
        </div>
    <?php endif; ?>

    <h3>Catalog</h3>

    <!-- Форма добавления товара -->
    <div class="add-product-form" style="margin-bottom: 30px; padding: 20px; background: #f5f5f5; border-radius: 5px;">
        <h4>Add Product to Cart</h4>
        <form action="/add-product" method="POST">
            <div class="input-container">
                <i class="fa fa-shopping-cart icon"></i>
                <input class="input-field" type="text" placeholder="Product ID" name="product-id" required>
            </div>

            <div class="input-container">
                <i class="fa fa-hashtag icon"></i>
                <input class="input-field" type="number" placeholder="Amount" name="amount" required min="1">
            </div>

            <button type="submit" class="btn">Add to Cart</button>
        </form>
    </div>

    <div class="card-deck">
        <?php foreach ($products as $product): ?>
            <div class="card text-center">
                <a href="#">
                    <div class="card-header">
                        Hit!
                    </div>
                    <img class="card-img-top" src="<?php echo $product['image_url']; ?>" alt="Card image cap">
                    <div class="card-body">
                        <p class="card-text text-muted"><?php echo $product['name']; ?></p>
                        <a href="#"><h5 class="card-title"><?php echo $product['description']; ?></h5></a>
                        <div class="card-footer">
                            $<?php echo $product['price']; ?>
                        </div>
                        <!-- Кнопка быстрого добавления товара -->
                        <form action="/add-product" method="POST" style="margin-top: 10px; display: inline-block;">
                            <input type="hidden" name="product-id" value="<?php echo $product['id']; ?>">
                            <input type="number" name="amount" value="1" min="1" style="width: 60px; padding: 5px; margin-right: 10px;">
                            <button type="submit" class="btn" style="padding: 5px 15px; font-size: 14px;">
                                <?php
                                // Проверяем, есть ли товар уже в корзине
                                $inCart = false;
                                $cartAmount = 0;
                                if (isset($userID)) {
                                    foreach ($cartItems as $cartItem) {
                                        if ($cartItem['id'] == $product['id']) {
                                            $inCart = true;
                                            $cartAmount = $cartItem['amount'];
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <?php if ($inCart): ?>
                                    ✅ В корзине (<?php echo $cartAmount; ?>)
                                <?php else: ?>
                                    ➕ Добавить
                                <?php endif; ?>
                            </button>
                        </form>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <br>
    <a href="/cart">View Cart</a>
    <a href="/profile">My Profile</a>


    <style>
        body {
            font-style: sans-serif;
        }

        a {
            text-decoration: none;
        }

        a:hover {
            text-decoration: none;
        }

        h3 {
            line-height: 3em;
        }

        .card {
            max-width: 16rem;
            margin: 10px;
            display: inline-block;
        }

        .card:hover {
            box-shadow: 1px 2px 10px lightgray;
            transition: 0.2s;
        }

        .card-header {
            font-size: 13px;
            color: gray;
            background-color: white;
        }

        .text-muted {
            font-size: 11px;
        }

        .card-footer{
            font-weight: bold;
            font-size: 18px;
            background-color: white;
        }

        /* Стили для формы добавления товара */
        .input-container {
            display: flex;
            width: 100%;
            margin-bottom: 15px;
        }

        .icon {
            padding: 10px;
            background: dodgerblue;
            color: white;
            min-width: 50px;
            text-align: center;
        }

        .input-field {
            width: 100%;
            padding: 10px;
            outline: none;
        }

        .input-field:focus {
            border: 2px solid dodgerblue;
        }

        .btn {
            background-color: dodgerblue;
            color: white;
            padding: 15px 20px;
            border: none;
            cursor: pointer;
            width: 100%;
            opacity: 0.9;
        }

        .btn:hover {
            opacity: 1;
        }
    </style>