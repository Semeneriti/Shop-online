<?php

declare(strict_types=1);

namespace Controllers;

use Models\User;
use Services\CartService;
use Services\OrderService;
use Request\RegisterRequest;
use Request\LoginRequest;
use Request\UpdateProfileRequest;

class UserController extends Controller
{
    private CartService $cartService;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct();
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

    public function registrate(RegisterRequest $request = null): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect('/registration');
        }

        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        if ($request == null) {
            $request = new RegisterRequest($_POST);
        }

        $errors = $request->getErrors();

        if (empty($errors)) {
            $name = $request->getName();
            $email = $request->getEmail();
            $password = $request->getPassword();

            $existingUser = User::findByEmail($email);

            if ($existingUser) {
                $errors['email'] = 'Пользователь с таким email уже существует';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $user = new User($name, $email, $passwordHash);

                if ($user->save()) {
                    $this->auth->login($user);
                    $this->auth->redirect('/catalog');
                } else {
                    $errors[] = 'Ошибка при создании пользователя';
                }
            }
        }

        require_once __DIR__ . '/../Views/registration.php';
    }

    public function login(LoginRequest $request = null): void
    {
        if (!$this->auth->isGuest()) {
            $this->auth->redirect('/catalog');
        }

        if ($request == null) {
            $request = new LoginRequest($_POST);
        }

        $errors = $request->getErrors();

        if ($this->auth->isPostRequest() && empty($errors)) {
            $email = $request->getEmail();
            $password = $request->getPassword();

            $user = User::findByEmail($email);

            if (!$user) {
                $errors['general'] = 'Неверный email или пароль';
            } elseif ($user->verifyPassword($password)) {
                $this->auth->login($user);
                $this->auth->redirect('/catalog');
            } else {
                $errors['general'] = 'Неверный email или пароль';
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
        $user = $this->auth->getCurrentUser();
        $userData = $user ? $user->toArray() : [];
        $cartItems = $this->cartService->getCartItems($userId);
        $ordersData = $this->orderService->getOrdersByUserId($userId);

        $orders = [];
        foreach ($ordersData as $orderData) {
            $orders[] = [
                'id' => $orderData['order']->getId(),
                'address' => $orderData['order']->getAddress(),
                'phone' => $orderData['order']->getPhone(),
                'total_price' => $orderData['order']->getTotalPrice(),
                'status' => $orderData['order']->getStatus(),
                'created_at' => $orderData['order']->getCreatedAt()->format('Y-m-d H:i:s'),
                'items_count' => $orderData['items_count'],
                'total_items' => $orderData['total_items']
            ];
        }

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
        $user = $this->auth->getCurrentUser();

        if (empty($errors)) {
            $name = $request->getName();
            $email = $request->getEmail();

            if ($email !== $user->getEmail()) {
                $existingUser = User::findByEmail($email);
                if ($existingUser) {
                    $errors['email'] = 'Этот email уже используется другим пользователем';
                }
            }

            if (empty($errors)) {
                try {
                    $password = $request->getPassword();
                    $user->updateProfile($name, $email, !empty($password) ? $password : null);

                    $this->auth->setSessionValue('userName', $name);
                    $this->auth->setSessionValue('success_message', 'Профиль успешно обновлен');
                    $this->auth->redirect("/profile");
                } catch (\Exception $e) {
                    $errors[] = 'Ошибка при обновлении профиля: ' . $e->getMessage();
                }
            }
        }

        $userData = $user->toArray();
        require_once __DIR__ . '/../Views/edit_profile.php';
    }
}
