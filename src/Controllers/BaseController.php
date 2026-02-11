<?php
namespace Controllers;

use Models\User;
use Services\AuthService;

abstract class BaseController
{
    protected AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }
}