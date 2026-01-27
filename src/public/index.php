<?php

$autoloadCore = function (string $className) {
    $path = "../Core/" . $className . ".php";
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    else {
        return false;
    }
};

$autoloadController = function (string $className) {
    $path = "../Controller/" . $className . ".php";
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    else {
        return false;
    }
};

$autoloadModels = function (string $className) {
    $path = "../Models/" . $className . ".php";
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    else {
        return false;
    }
};

$autoloadControllers = function (string $className) {
    $path = "../Controllers/" . $className . ".php";
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    else {
        return false;
    }
};

spl_autoload_register($autoloadCore, true);
spl_autoload_register($autoloadController, true);
spl_autoload_register($autoloadModels, true);
spl_autoload_register($autoloadControllers, true);

$app = new App();
$app->run();