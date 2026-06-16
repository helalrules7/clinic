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
        $this->auth->logout();
        UrlHelper::redirect('/login');
    }

    /**
     * Mobile → web auto-login bridge. The app opens this URL in its in-app
     * browser with its Bearer token + a target path; we mint a real cookie-backed
     * web session and redirect there, so the user lands already signed in (CSV /
     * PDF export, full reports, etc.) without retyping credentials.
     */
    public function mobileWebLogin()
    {
        $token = $_GET['token'] ?? '';
        $redirect = $this->sanitizeRedirect($_GET['redirect'] ?? '/doctor/dashboard');

        if ($this->auth->loginWithMobileToken($token)) {
            session_write_close();
            UrlHelper::redirect($redirect);
        } else {
            UrlHelper::redirect('/login?expired=1');
        }
    }

    /** Only allow a same-origin absolute path (no scheme / protocol-relative / backslash). */
    private function sanitizeRedirect($path)
    {
        $path = (string) $path;
        if ($path === '' || $path[0] !== '/' || strncmp($path, '//', 2) === 0 || strpos($path, '\\') !== false) {
            return '/doctor/dashboard';
        }
        return $path;
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
    
    /**
     * API endpoint to get remaining session time
     */
    public function getSessionTime()
    {
        // Clear output buffers first
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Content-Type: application/json; charset=utf-8');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Use checkWithoutUpdate to avoid resetting the timer
        if (!$this->auth->checkWithoutUpdate()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'remaining' => 0,
                'message' => 'Not authenticated'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $remaining = $this->auth->getRemainingSessionTime();
        $timeout = $this->auth->getSessionTimeout();

        echo json_encode([
            'success' => true,
            'remaining' => $remaining,
            'timeout' => $timeout,
            'last_activity' => $_SESSION['last_activity'] ?? null
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
