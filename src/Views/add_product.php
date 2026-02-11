<form action="/add-product" method="POST">
    <h2>Добавить продукт</h2>
    <div class="input-container">
        <i class="fa fa-user icon"></i>
        <?php if (isset($errors['product-id'])): ?>
            <span style="color: red;"><?php echo $errors['product-id']; ?></span>
        <?php endif; ?>
        <input class="input-field" type="text" placeholder="Product ID" name="product-id" required>
    </div>

    <div class="input-container">
        <i class="fa fa-envelope icon"></i>
        <?php if (isset($errors['amount'])): ?>
            <span style="color: red;"><?php echo $errors['amount']; ?></span>
        <?php endif; ?>
        <input class="input-field" type="number" placeholder="Amount" name="amount" required min="1">
    </div>

    <button type="submit" class="btn">Add product</button>
</form>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        padding: 20px;
    }

    form {
        max-width: 500px;
        margin: 0 auto;
        background-color: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 25px;
    }

    .input-container {
        display: flex;
        flex-direction: column;
        width: 100%;
        margin-bottom: 15px;
    }

    .icon {
        display: none;
    }

    .input-field {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s;
    }

    .input-field:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52,152,219,0.1);
    }

    .btn {
        background-color: #3498db;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        transition: background-color 0.3s;
    }

    .btn:hover {
        background-color: #2980b9;
    }

    span[style*="color: red"] {
        margin-bottom: 5px;
        font-size: 13px;
    }
</style>