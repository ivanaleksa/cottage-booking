<?php

include __DIR__ . '/db_conn.php';
$pdo = connect_to_db();

header('Content-Type: application/json');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/get_cottages') {
    // Вывод всех коттеджей

    $stmt = $pdo->query("SELECT * FROM cottage_house");
    $cottages = $stmt->fetchAll();

    echo json_encode($cottages);
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/get_cottage') {
    // Вывод определенного коттеджа по id

    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM cottage_house WHERE cottage_id = :id");
        $stmt->bindParam(':id', $_GET['id']);
        $stmt->execute();
        echo json_encode($stmt->fetch());
    } 
    else {
        http_response_code(400);
        echo json_encode(array('error' => 'Enter the cottage\'s id'));
    }
} 
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/get_bookings') {
    // Вывод бронирования на 3 месяца для определенного коттеджа

    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT booking_start_at, booking_end_at
                                FROM cottage_booking
                                WHERE cottage_id = :cottage_id AND
                                        booking_start_at BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '3 months'");
        $stmt->bindParam(':cottage_id', $_GET['id']);
        $stmt->execute();

        echo json_encode($stmt->fetchAll());
    }
    else {
        http_response_code(400);
        echo json_encode(array('error' => 'Enter the cottage\'s id'));
    }
} 
else {
    http_response_code(404);
    echo json_encode(array('error' => 'Page not found'));
}
?>
