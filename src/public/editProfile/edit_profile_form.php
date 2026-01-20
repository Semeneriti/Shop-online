<?php

session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /login");
    exit;
}

$userId = $_SESSION['userId'];

$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();
?>

<form action="/update-profile" method="POST">
    <h2>Edit Profile</h2>

    <div class="input-container">
        <i class="fa fa-user icon"></i>
        <input class="input-field" type="text" placeholder="Name" name="name" value="<?php echo $user['name']; ?>">
    </div>

    <div class="input-container">
        <i class="fa fa-envelope icon"></i>
        <input class="input-field" type="text" placeholder="Email" name="email" value="<?php echo $user['email']; ?>">
    </div>

    <div class="input-container">
        <i class="fa fa-key icon"></i>
        <input class="input-field" type="password" placeholder="New Password (leave empty to keep current)" name="password">
    </div>

    <button type="submit" class="btn">Update Profile</button>
</form>

<style>
    * {box-sizing: border-box;}

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
