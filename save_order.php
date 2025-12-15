<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}
include 'db.php'; // include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $total = $_POST['total'];
    $items = $_POST['items']; // JSON or string

    // Get user_id from session
    $user_id = null;
    if (isset($_SESSION['user']['id'])) {
        $user_id = $_SESSION['user']['id'];
    } elseif (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }

    // Insert into database (with user_id)
    $sql = "INSERT INTO orders (user_id, customer_name, phone, address, items, total_price)
        VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isssss', $user_id, $name, $phone, $address, $items, $total);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true]);
        exit();
    } else {
        $error = $stmt->error;
        $stmt->close();
        echo json_encode(['success' => false, 'error' => $error]);
        exit();
    }
}
?>
