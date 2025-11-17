<?php

namespace App\Controllers;

use App\Lib\View;
use App\Lib\Auth;
use App\Lib\UrlHelper;

class GeneralController
{
    private $view;
    private $auth;

    public function __construct()
    {
        // Ensure session is started before creating Auth instance
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->view = new View();
        $this->auth = new Auth();
    }
    
    private function requireAuth()
    {
        // Check if user is authenticated
        if (!$this->auth->check()) {
            UrlHelper::redirect('/login');
        }
    }

    public function about()
    {
        $this->requireAuth();
        $user = $this->auth->user();
        
        // Render the about page content
        $content = $this->view->render('general/about', [
            'version' => 'v6.1',
            'releaseDate' => '2025',
            'developer' => [
                'name' => 'Ahmed Helal',
                'website' => 'https://ahmedhelal.dev',
                'title' => 'Full Stack Developer'
            ]
        ]);
        
        // Render the content within the main layout
        echo $this->view->render('layouts/main', [
            'title' => 'About System - HClinic / Roaya Clinic',
            'pageTitle' => 'About System',
            'pageSubtitle' => 'System information and details',
            'content' => $content
        ]);
    }

    public function whatsNew()
    {
        // This page doesn't require authentication - it's public
        // Render standalone page similar to index.html
        include __DIR__ . '/../Views/whats-new/index.php';
        exit;
    }

    public function olderVersions()
    {
        // This page doesn't require authentication - it's public
        // Render standalone page for older versions features
        include __DIR__ . '/../Views/whats-new/older-versions.php';
        exit;
    }
    public function fullfeatures()
    {
        // This page doesn't require authentication - it's public
        // Render standalone page for older versions features
        include __DIR__ . '/../Views/whats-new/features.php';
        exit;
    }

    public function home()
    {
        // Debug: Log session status
        error_log("GeneralController@home - Session status: " . session_status());
        error_log("GeneralController@home - Session data: " . json_encode($_SESSION ?? []));
        
        // Check if user is already logged in
        $isLoggedIn = $this->auth->check();
        error_log("GeneralController@home - Auth check result: " . ($isLoggedIn ? 'true' : 'false'));
        
        if ($isLoggedIn) {
            $user = $this->auth->user();
            error_log("GeneralController@home - User data: " . json_encode($user));
            
            if ($user && isset($user['role'])) {
                error_log("GeneralController@home - Redirecting user with role: " . $user['role']);
                // Redirect to appropriate dashboard based on role
                $this->redirectByRole($user['role']);
                return;
            } else {
                error_log("GeneralController@home - User data invalid or missing role");
            }
        } else {
            error_log("GeneralController@home - User not logged in, showing welcome page");
        }
        
        // If not logged in, show welcome page
        include __DIR__ . '/../Views/general/welcome.php';
        exit;
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
                break;
        }
    }
}
