<?php

function checkAuth($pdo) {
    if (!isset($_COOKIE['admin_token'])) {
        http_response_code(403);
        echo json_encode(array('error' => 'Unauthorized'));
        exit();
    }

    $token = $_COOKIE['admin_token'];
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $admin = $stmt->fetch();

    if (!$admin) {
        http_response_code(403);
        echo json_encode(array('error' => 'Unauthorized'));
        exit();
    }
}

?>
