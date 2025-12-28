<?php

namespace App\Controllers;

use App\Models\Post;

class ApiController {
    public function store() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            try {
                // Fallback for form-data if someone submits standard form
                $input = $_POST;
            } catch (\Exception $e) {}
        }
        
        if (empty($input['title_en'])) {
            http_response_code(400);
            echo json_encode(['error' => 'English Title is required']);
            return;
        }

        try {
            $post = new Post();
            
            if (!empty($input['id'])) {
                $post->update($input['id'], $input);
                echo json_encode(['success' => true, 'id' => $input['id'], 'action' => 'updated']);
            } else {
                $id = $post->create($input);
                echo json_encode(['success' => true, 'id' => $id, 'action' => 'created']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function search() {
        header('Content-Type: application/json');
        $query = $_GET['q'] ?? '';
        $lang = $_GET['lang'] ?? 'en';

        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }

        try {
            $post = new Post();
            $results = $post->search($query, $lang);
            echo json_encode($results);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function delete() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
            return;
        }

        try {
            $post = new Post();
            $post->delete($input['id']);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
