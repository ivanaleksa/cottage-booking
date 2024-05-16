<?php

include __DIR__ . '/db_conn.php';
$pdo = connect_to_db();

header('Content-Type: application/json');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/login') {
    // Авторизация

    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_login = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['admin_password'])) {
            echo json_encode(array('status' => 'ok'));
        } else {
            http_response_code(401);
            echo json_encode(array('error' => 'Invalid credentials'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Login and password are required'));
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/register') {
    // Регистрация

    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = $_POST['login'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO admins (admin_login, admin_password) VALUES (:login, :password)");
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            echo json_encode(array('status' => 'ok'));
        } else {
            http_response_code(500);
            echo json_encode(array('error' => 'Registration failed'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Login and password are required'));
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/get_cottages') {
    // Вывод всех коттеджей

    $stmt = $pdo->query("SELECT * FROM cottage_house");
    echo json_encode($stmt->fetchAll());
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
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/get_booking_dates') {
    // Вывод бронирования на 3 месяца для определенного коттеджа

    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT booking_start_at, booking_end_at
                                FROM cottage_booking
                                WHERE cottage_id = :cottage_id AND
                                        booking_start_at BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '3 months'
                                        AND booking_confirmation_date IS NOT NULL");
        $stmt->bindParam(':cottage_id', $_GET['id']);
        $stmt->execute();

        echo json_encode($stmt->fetchAll());
    }
    else {
        http_response_code(400);
        echo json_encode(array('error' => 'Enter the cottage\'s id'));
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/get_bookings') {
    // Вывод всех бронирований для административной панели

    $stmt = $pdo->query("SELECT * FROM cottage_booking");
    echo json_encode($stmt->fetchAll());
}
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/add_booking') {
    // добавление нового бронировния

    if (isset($_POST['cottageId']) && isset($_POST['name']) && isset($_POST['phoneNumber']) && isset($_POST['startDate']) && isset($_POST['endDate'])) {
        $stmp = $pdo->prepare("INSERT INTO cottage_booking (cottage_id, client_name, client_phone_number, booking_start_at, booking_end_at)
                                    VALUES (:cottage_id, :client_name, :client_phone_number, :booking_start_at, :booking_end_at)");
        
        $stmp->bindParam(':cottage_id', $_POST['cottageId']);
        $stmp->bindParam(':client_name', $_POST['name']);
        $stmp->bindParam(':client_phone_number', $_POST['phoneNumber']);
        $stmp->bindParam(':booking_start_at', $_POST['startDate']);
        $stmp->bindParam(':booking_end_at', $_POST['endDate']);
        
        $stmp->execute();
        echo json_encode(array('status' => 'ok'));
    }
    else {
        http_response_code(400);
        echo json_encode(array('error' => 'Unprocessable entity'));
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $path === '/delete_booking') {
    // Удаление бронирования
    parse_str(file_get_contents("php://input"), $delete_vars);

    if (isset($delete_vars['booking_id'])) {
        $booking_id = $delete_vars['booking_id'];

        $stmt = $pdo->prepare("DELETE FROM cottage_booking WHERE booking_id = :booking_id");
        $stmt->bindParam(':booking_id', $booking_id);

        if ($stmt->execute()) {
            echo json_encode(array('status' => 'ok'));
        } else {
            http_response_code(500);
            echo json_encode(array('error' => 'Failed to delete booking'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Booking ID is required'));
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && strpos($_SERVER['REQUEST_URI'], '/update_booking/') !== false) {
    // Обновление бронирования

    $bookingId = substr($path, strlen('/update_booking/'));

    if (isset($bookingId)) {
        $stmt = $pdo->prepare("UPDATE cottage_booking SET booking_confirmation_date = CURRENT_DATE WHERE booking_id = :id");
        $stmt->bindParam(':id', $bookingId);

        if ($stmt->execute()) {
            echo json_encode(array('status' => 'ok'));
        } else {
            http_response_code(500);
            echo json_encode(array('error' => 'Failed to update booking'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Id is required'));
    }
}
else {
    http_response_code(404);
    echo json_encode(array('error' => 'Page not found'));
}
?>
