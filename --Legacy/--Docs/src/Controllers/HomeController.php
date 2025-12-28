<?php

namespace App\Controllers;

class HomeController {
    public function index() {
        $lang = $_GET['lang'] ?? 'en';
        $postModel = new \App\Models\Post();
        
        // Data for Sidebar
        $menuTree = $postModel->getTree($lang);
        
        // Data for Content
        $currentPost = null;
        $id = $_GET['id'] ?? 1; // Default to ID 1 (Getting Started)
        $currentPost = $postModel->getById($id, $lang);

        $title = $currentPost ? 'ROYA HCLINIC DOCHUB | ' . $currentPost['title'] : 'ROYA HCLINIC DOCHUB';
        
        // Render view
        $data = compact('title', 'lang', 'menuTree', 'currentPost');
        extract($data);
        
        ob_start();
        include __DIR__ . '/../../views/home.php';
        $content = ob_get_clean();
        
        include __DIR__ . '/../../views/layout.php';
    }
}
