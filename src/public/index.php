<?php

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestUri == '/registration') {
    if ($requestMethod == 'GET') {
        require_once 'Views/registration_form.php';
    } else if ($requestMethod == 'POST') {
        require_once 'classes/User.php';
        $user = new User();
        $user->registrate();
    }
} elseif ($requestUri == '/login') {
    if ($requestMethod == 'GET') {
        require_once 'Views/login_form.php';
    } else if ($requestMethod == 'POST') {
        require_once 'classes/User.php';
        $user = new User();
        $user->login();
    }
} elseif ($requestUri == '/catalog') {
    if ($requestMethod == 'GET') {
        require_once 'classes/Catalog.php';
        $catalog = new Catalog();
    }
} elseif ($requestUri == '/add-product') {
    if ($requestMethod == 'GET') {
        require_once 'Views/add_product_form.php';
    } else if ($requestMethod == 'POST') {
        require_once 'classes/Product.php';
        $product = new Product();
        $product->addToCart();
    }
} elseif ($requestUri == '/cart') {
    if ($requestMethod == 'GET') {
        require_once 'classes/Cart.php';
        $cart = new Cart();
    }
} elseif ($requestUri == '/profile') {
    if ($requestMethod == 'GET') {
        require_once 'classes/User.php';
        $user = new User();
        $user->getProfile();
    }
} elseif ($requestUri == '/edit-profile') {
    if ($requestMethod == 'GET') {
        require_once 'Views/edit_profile_form.php';
    } else if ($requestMethod == 'POST') {
        require_once 'classes/User.php';
        $user = new User();
        $user->updateProfile();
    }
} else {
    http_response_code(404);
    require_once 'Views/404.php';
}
?>
