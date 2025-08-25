<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Lib\Validator;

class AuthController
{
    private $auth;
    private $view;
    private $validator;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->validator = new Validator();
    }

    public function showLogin()
    {
        // Redirect if already logged in
        if ($this->auth->check()) {
            $user = $this->auth->user();
            $this->redirectByRole($user['role']);
        }

        echo $this->view->render('auth/login');
    }

    public function login()
    {
        try {
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                throw new \Exception('Invalid CSRF token');
            }

            // Validate input
            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:1'
            ];

            $data = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? ''
            ];

            if (!$this->validator->validate($data, $rules)) {
                throw new \Exception('Please fill in all required fields');
            }

            // Attempt login
            $user = $this->auth->login($data['email'], $data['password']);

            // Set remember me if requested
            if (isset($_POST['remember_me']) && $_POST['remember_me']) {
                $this->auth->setRememberMe($user['id']);
            }

            // Redirect based on role
            $this->redirectByRole($user['role']);

        } catch (\Exception $e) {
            $error = $e->getMessage();
            echo $this->view->render('auth/login', [
                'error' => $error,
                'email' => $_POST['email'] ?? ''
            ]);
        }
    }

    public function logout()
    {
        $this->auth->logout();
        header('Location: /login');
        exit;
    }

    private function redirectByRole($role)
    {
        switch ($role) {
            case 'doctor':
                header('Location: /doctor/dashboard');
                break;
            case 'secretary':
                header('Location: /secretary/dashboard');
                break;
            case 'admin':
                header('Location: /admin/dashboard');
                break;
            default:
                header('Location: /login');
        }
        exit;
    }

    private function validateCsrfToken()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}
