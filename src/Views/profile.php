<div class="profile-container">
    <h1>My Profile</h1>

    <div class="user-info">
        <h2>User Information</h2>
        <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
        <a href="/edit-profile">Edit Profile</a>
    </div>

    <div class="user-products">
        <h2>My Products</h2>
        <?php if (count($userProducts) > 0): ?>
            <table>
                <tr>
                    <th>Product</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                </tr>
                <?php foreach ($userProducts as $product): ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['description']; ?></td>
                        <td>$<?php echo $product['price']; ?></td>
                        <td><?php echo $product['amount']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>You haven't added any products yet.</p>
        <?php endif; ?>
    </div>

    <br>
    <a href="/catalog">Back to Catalog</a>
</div>
<a href="/logout">Logout</a>

<style>
    .profile-container {
        padding: 20px;
    }

    .user-info {
        background: #f5f5f5;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
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
</style>
