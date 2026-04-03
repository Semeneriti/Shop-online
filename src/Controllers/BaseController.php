<?php
// src/Controllers/BaseController.php # что за комментарий? Мёртвый комментарий — личные заметки не должны попадать в код. Удалить.
namespace Controllers;

use Services\Auth\AuthInterface;
use Services\Auth\SessionAuthService;
use Services\Loggers\LoggerInterface;
use Services\Loggers\LoggerService;

abstract class BaseController // Это дефолтный контроллер. Его нужно назвать Controller. Чем короче, понятнее и без лишних слов названы классы, методы, тем лучше.
{
     protected AuthInterface $auth;
    protected LoggerInterface $logger;

    public function __construct()
    {
        $this->auth = new SessionAuthService();
        $this->logger = new LoggerService();
    }
}

// PSR-12 §4: Любой код, комментарии или блоки вне тела класса НЕДОПУСТИМЫ.
// Файл должен содержать только одно объявление (класс), без побочных инструкций снаружи.
// Этот блок следует полностью убрать из файла
/** Заменить на запись в БД:
public function __construct()
{
    $this->auth = new SessionAuthService();
    $pdo = \Models\Model::getConnection();
    $this->logger = new DatabaseLogger($pdo); // ← теперь пишет в БД
}
**/
