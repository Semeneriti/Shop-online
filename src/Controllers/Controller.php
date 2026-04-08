<?php

declare(strict_types=1);

namespace Controllers;

use Services\Auth\AuthInterface;
use Services\Auth\SessionAuthService;
use Services\Loggers\DataBaseLogger;
use Services\Loggers\LoggerInterface;

abstract class Controller
{
    protected AuthInterface $auth;
    protected LoggerInterface $logger;

    public function __construct()
    {
        $this->auth = new SessionAuthService();
        $pdo = \Models\Model::getConnection();
        $this->logger = new DataBaseLogger($pdo);
    }
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require_once __DIR__ . '/../Views/' . $view . '.php';
    }
}
