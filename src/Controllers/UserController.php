<?php

declare(strict_types=1);

namespace Controllers;

use Services\UserService;
use Services\CartService;
use Services\OrderService;
use Request\RegisterRequest;
use Request\LoginRequest;
use Request\UpdateProfileRequest;

class UserController extends Controller
{
    private UserService $userService;
    private CartService $cartService;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
        $this->cartService = new CartService();
        $this->orderService = new OrderService();
    }

    public function getRegistrate(): void
    {
        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }
        require_once __DIR__ . '/../Views/registration.php';
    }

    public function registrate(?RegisterRequest $request = null): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect('/registration');
        }

        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        if ($request === null) {
            $request = new RegisterRequest($_POST);
        }

        $errors = $request->getErrors();

        if (empty($errors)) {
            try {
                $user = $this->userService->register(
                    $request->getName(),
                    $request->getEmail(),
                    $request->getPassword()
                );
                $this->auth->login($user);
                $this->auth->redirect('/catalog');
                return;
            } catch (\DomainException $e) {
                $errors['email'] = $e->getMessage();
            } catch (\Exception $e) {
                $errors[] = 'Ошибка при создании пользователя';
            }
        }

        require_once __DIR__ . '/../Views/registration.php';
    }

    public function login(?LoginRequest $request = null): void
    {
        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        if ($request === null) {
            $request = new LoginRequest($_POST);
        }

        $errors = $request->getErrors();

        if ($this->auth->isPostRequest() && empty($errors)) {
            try {
                $user = $this->userService->authenticate(
                    $request->getEmail(),
                    $request->getPassword()
                );
                $this->auth->login($user);
                $this->auth->redirect('/catalog');
                return;
            } catch (\DomainException $e) {
                $errors['general'] = $e->getMessage();
            }
        }

        require_once __DIR__ . '/../Views/login.php';
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->auth->redirect("/login");
    }

    public function getProfile(): void
    {
        $this->auth->requireAuth();

        $userId = $this->auth->getUserId();
        $userData = $this->userService->getUserProfileData($userId);
        $orders = $this->orderService->getOrdersByUserId($userId);

        require_once __DIR__ . '/../Views/profile.php';
    }

    public function showEditForm(): void
    {
        $this->auth->requireAuth();
        $userData = $this->auth->getCurrentUser()->toArray();
        require_once __DIR__ . '/../Views/edit_profile.php';
    }

    public function updateProfile(UpdateProfileRequest $request): void
    {
        $this->auth->requireAuth();

        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/edit-profile");
        }

        $errors = $request->getErrors();

        if (empty($errors)) {
            $userId = $this->auth->getUserId();
            $name = $request->getName();
            $email = $request->getEmail();
            $password = $request->getPassword();

            try {
                $user = $this->userService->updateProfile(
                    $userId,
                    $name,
                    $email,
                    !empty($password) ? $password : null
                );

                $this->auth->setSessionValue('userName', $name);
                $this->auth->setSessionValue('success_message', 'Профиль успешно обновлен');
                $this->auth->redirect("/profile");
                return;
            } catch (\DomainException $e) {
                $errors['email'] = $e->getMessage();
            } catch (\Exception $e) {
                $errors[] = 'Ошибка при обновлении профиля: ' . $e->getMessage();
            }
        }

        $userData = $this->auth->getCurrentUser()->toArray();
        require_once __DIR__ . '/../Views/edit_profile.php';
    }
}
