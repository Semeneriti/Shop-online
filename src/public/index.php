<?php

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestUri == '/registration') {
    if($requestMethod == 'GET') {
        require_once './registration_form.php';
    }else if($requestMethod == 'POST') {
        require_once './handle_registration_form.php';
    }
} elseif ($requestUri == '/login') {
    if($requestMethod == 'GET') {
        require_once './login_form.php';
    } else if($requestMethod == 'POST') {
        require_once './handle_login_form.php';
    }
} elseif ($requestUri == '/catalog') {
    if($requestMethod == 'GET') {
        require_once './catalog.php';
    }
    else{
        http_response_code(404);
        require_once './404.php';
    }
}