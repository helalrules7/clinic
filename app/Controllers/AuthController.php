<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Lib\Validator;
use App\Lib\UrlHelper;

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
                'username' => 'required|min:3',
                'password' => 'required|min:1'
            ];

            $data = [
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? ''
            ];

            if (!$this->validator->validate($data, $rules)) {
                throw new \Exception('Please fill in all required fields');
            }

            // Attempt login
            $user = $this->auth->login($data['username'], $data['password']);

            // Set remember me if requested
            if (isset($_POST['remember_me']) && $_POST['remember_me']) {
                $this->auth->setRememberMe($user['id']);
            }

            // Create login notification
            try {
                \App\Controllers\NotificationController::create(
                    $user['id'],
                    'system',
                    'Login Successful',
                    "You have successfully logged in to the system",
                    null,
                    null,
                    null
                );
            } catch (\Exception $e) {
                // Continue even if notification creation fails
            }

            // Redirect based on role
            $this->redirectByRole($user['role']);

        } catch (\Exception $e) {
            $error = $e->getMessage();
            echo $this->view->render('auth/login', [
                'error' => $error,
                'username' => $_POST['username'] ?? ''
            ]);
        }
    }

    public function logout()
    {
        // Create logout notification before destroying session
        try {
            $user = $this->auth->user();
            if ($user) {
                \App\Controllers\NotificationController::create(
                    $user['id'],
                    'system',
                    'Logout Successful',
                    "You have successfully logged out from the system",
                    null,
                    null,
                    null
                );
            }
        } catch (\Exception $e) {
            // Continue even if notification creation fails
        }
        
        $this->auth->logout();
        UrlHelper::redirect('/login');
    }

    private function redirectByRole($role)
    {
        // Ensure session is saved before redirect
        session_write_close();
        
        switch ($role) {
            case 'doctor':
                UrlHelper::redirect('/doctor/dashboard');
                break;
            case 'secretary':
                UrlHelper::redirect('/secretary/dashboard');
                break;
            case 'admin':
                UrlHelper::redirect('/admin/dashboard');
                break;
            default:
                UrlHelper::redirect('/login');
        }
    }

    private function validateCsrfToken()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}
