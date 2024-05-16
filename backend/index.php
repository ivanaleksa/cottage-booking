<?php

include __DIR__ . '/db_conn.php';
include __DIR__ . '/auth.php';

$pdo = connect_to_db();

header('Access-Control-Allow-Origin: http://localhost:8000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


if ($method === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if ($method === 'POST' && $path === '/login') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['login']) && isset($data['password'])) {
        $login = $data['login'];
        $password = $data['password'];

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_login = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['admin_password'])) {
            $token = bin2hex(random_bytes(16));
            setcookie('admin_token', $token, time() + 3600, "/");
            $stmt = $pdo->prepare("UPDATE admins SET token = :token WHERE admin_login = :login");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':login', $login);
            $stmt->execute();

            echo json_encode(array('status' => 'ok', 'token' => $token));
        } else {
            http_response_code(401);
            echo json_encode(array('error' => 'Invalid credentials'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Login and password are required'));
    }
} 
else if ($method === 'POST' && $path === '/logout') {
    setcookie('admin_token', '', time() - 3600);
    echo json_encode(array('status' => 'logged out'));
} 
else if ($method === 'POST' && $path === '/register') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['login']) && isset($data['password'])) {
        $login = $data['login'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);

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
else if ($method === 'GET' && $path === '/get_cottages') {
    $stmt = $pdo->query("SELECT * FROM cottage_house");
    echo json_encode($stmt->fetchAll());
} 
else if ($method === 'GET' && $path === '/get_cottage') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM cottage_house WHERE cottage_id = :id");
        $stmt->bindParam(':id', $_GET['id']);
        $stmt->execute();
        echo json_encode($stmt->fetch());
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Enter the cottage\'s id'));
    }
} 
else if ($method === 'GET' && $path === '/get_booking_dates') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT booking_start_at, booking_end_at
                                FROM cottage_booking
                                WHERE cottage_id = :cottage_id AND
                                      booking_start_at BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '3 months'
                                      AND booking_confirmation_date IS NOT NULL");
        $stmt->bindParam(':cottage_id', $_GET['id']);
        $stmt->execute();
        echo json_encode($stmt->fetchAll());
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Enter the cottage\'s id'));
    }
} 
else if ($method === 'GET' && $path === '/get_bookings') {
    checkAuth($pdo);

    $stmt = $pdo->query("SELECT booking_id, cottage_booking.cottage_id as cottage_id, cottage_name, client_name, client_phone_number, 
                                booking_start_at, booking_end_at, booking_confirmation_date
                            FROM cottage_booking 
                            JOIN cottage_house ON cottage_house.cottage_id = cottage_booking.cottage_id;");
    echo json_encode($stmt->fetchAll());
} 
else if ($method === 'POST' && $path === '/add_booking') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['cottageId']) && isset($data['name']) && isset($data['phoneNumber']) && isset($data['startDate']) && isset($data['endDate'])) {
        $stmt = $pdo->prepare("INSERT INTO cottage_booking (cottage_id, client_name, client_phone_number, booking_start_at, booking_end_at)
                               VALUES (:cottage_id, :client_name, :client_phone_number, :booking_start_at, :booking_end_at)");
        $stmt->bindParam(':cottage_id', $data['cottageId']);
        $stmt->bindParam(':client_name', $data['name']);
        $stmt->bindParam(':client_phone_number', $data['phoneNumber']);
        $stmt->bindParam(':booking_start_at', $data['startDate']);
        $stmt->bindParam(':booking_end_at', $data['endDate']);
        $stmt->execute();
        echo json_encode(array('status' => 'ok'));
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Unprocessable entity'));
    }
} 
else if ($method === 'DELETE' && $path === '/delete_booking') {
    checkAuth($pdo);

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
else if ($method === 'PATCH' && strpos($_SERVER['REQUEST_URI'], '/update_booking/') !== false) {
    checkAuth($pdo);

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
