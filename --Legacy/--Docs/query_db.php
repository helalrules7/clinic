<?php
try {
    $db = new PDO('sqlite:database.sqlite');
    $stmt = $db->query("SELECT item_id, title, locale FROM translations WHERE title LIKE '%Login%' OR title LIKE '%تسجيل الدخول%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($results);
} catch (Exception $e) {
    echo $e->getMessage();
}
