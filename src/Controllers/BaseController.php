<?php
// src/Controllers/BaseController.php
namespace Controllers;

use Services\Auth\AuthInterface;
use Services\Loggers\LoggerInterface;
use Services\Loggers\LoggerService;
use Services\SessionAuthService;

abstract class BaseController
{
    protected AuthInterface $auth;
    protected LoggerInterface $logger; // Тип - интерфейс, а не конкретный класс

    public function __construct()
    {
        $this->auth = new SessionAuthService();
        $this->logger = new LoggerService(); // Используем сервис
    }
}