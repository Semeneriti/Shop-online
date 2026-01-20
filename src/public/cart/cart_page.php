<div class="cart-container">
    <h1>My Cart</h1>

    <?php if (count($cartItems) > 0): ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
            <?php foreach ($cartItems as $item): ?>
                <?php $subtotal = $item['price'] * $item['amount']; ?>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td>$<?php echo $item['price']; ?></td>
                    <td><?php echo $item['amount']; ?></td>
                    <td>$<?php echo $subtotal; ?></td>
                    <td>
                        <a href="/remove-from-cart?id=<?php echo $item['cart_id']; ?>">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong>$<?php echo $totalPrice; ?></strong></td>
                <td></td>
            </tr>
        </table>

        <br>
        <button onclick="alert('Checkout feature coming soon!')">Checkout</button>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>

    <br>
    <a href="/catalog">Continue Shopping</a>
</div>

<style>
    .cart-container {
        padding: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th, td {
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    a {
        color: dodgerblue;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    button {
        background-color: green;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

    button:hover {
        background-color: darkgreen;
    }
</style>