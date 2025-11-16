<?php

namespace App\Controllers;

use App\Lib\View;
use App\Lib\Auth;

class GeneralController
{
    private $view;
    private $auth;

    public function __construct()
    {
        $this->view = new View();
        $this->auth = new Auth();
    }
    
    private function requireAuth()
    {
        // Check if user is authenticated
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
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
}
