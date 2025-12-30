<?php

namespace App\Controllers;

use App\Models\Post;

class DashboardController {
    public function index() {
        $postModel = new Post();
        $items = $postModel->getTree('en');
        
        $editingPost = null;
        if (isset($_GET['id'])) {
            $editingPost = $postModel->getWithTranslations($_GET['id']);
        }
        
        $data = [
            'title' => 'ROYA HCLINIC DOCHUB | Dashboard',
            'lang' => 'en',
            'existingItems' => $items,
            'menuTree' => $items,
            'isDashboard' => true,
            'editingPost' => $editingPost
        ];
        
        extract($data);
        ob_start();
        include __DIR__ . '/../../views/dashboard.php';
        $content = ob_get_clean();
        
        // Ensure isDashboard is available in layout context
        $isDashboard = true; 
        include __DIR__ . '/../../views/layout.php';
    }
}
