<?php
// src/Controllers/BaseController.php
namespace Controllers;

use Services\Auth\AuthInterface;
use Services\Auth\SessionAuthService;
use Services\Loggers\LoggerInterface;
use Services\Loggers\LoggerService;

abstract class BaseController
{
    protected AuthInterface $auth;
    protected LoggerInterface $logger;

    public function __construct()
    {
        $this->auth = new SessionAuthService();
        $this->logger = new LoggerService();
    }

    // Тут был Алдар
}

/** Заменить на запись в БД:
public function __construct()
{
    $this->auth = new SessionAuthService();
    $pdo = \Models\Model::getConnection();
    $this->logger = new DatabaseLogger($pdo); // ← теперь пишет в БД
}
**/
