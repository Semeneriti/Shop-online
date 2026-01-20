<form action="/registration" method = "POST">
    <h2>Register Form</h2>
    <div class="input-container">
        <i class="fa fa-user icon"></i>
        <?php if(isset($_GET['product-id'])): ?>
            <?php echo $errors['product-id']; ?>
        <?php endif;?>

        <input class="input-field" type="text" placeholder="product-id" name="product-id">
    </div>

    <div class="input-container">
        <i class="fa fa-envelope icon"></i>
        <?php if(isset($_GET['amount'])):?>
            <?php echo $errors['amount']; ?>
        <?php endif;?>
        <input class="input-field" type="text" placeholder="amount" name="amount">
    </div>

    <button type="submit" class="btn">Add product</button>
</form>
<style>
    * {box-sizing: border-box;}

    /* Style the input container */
    .input-container {
        display: flex;
        width: 100%;
        margin-bottom: 15px;
    }

    /* Style the form icons */
    .icon {
        padding: 10px;
        background: dodgerblue;
        color: white;
        min-width: 50px;
        text-align: center;
    }

    /* Style the input fields */
    .input-field {
        width: 100%;
        padding: 10px;
        outline: none;
    }

    .input-field:focus {
        border: 2px solid dodgerblue;
    }

    /* Set a style for the submit button */
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