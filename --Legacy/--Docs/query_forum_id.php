<?php
try {
    $db = new PDO('sqlite:database.sqlite');
    // Search for titles related to Discussion Forum
    $stmt = $db->query("SELECT item_id, title, locale FROM translations WHERE title LIKE '%Forum%' OR title LIKE '%Discussion%' OR title LIKE '%منتدى%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($results);
} catch (Exception $e) {
    echo $e->getMessage();
}
