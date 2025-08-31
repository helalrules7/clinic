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
        
        // Check if user is authenticated
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }
    }

    public function about()
    {
        $user = $this->auth->user();
        
        // Render the about page content
        $content = $this->view->render('general/about', [
            'version' => 'Beta 1.0',
            'releaseDate' => '2025',
            'developer' => [
                'name' => 'Ahmed Helal',
                'website' => 'https://ahmedhelal.dev',
                'title' => 'Full Stack Developer'
            ]
        ]);
        
        // Render the content within the main layout
        echo $this->view->render('layouts/main', [
            'title' => 'About System - Roaya Clinic',
            'pageTitle' => 'About System',
            'pageSubtitle' => 'System information and details',
            'content' => $content
        ]);
    }
}
