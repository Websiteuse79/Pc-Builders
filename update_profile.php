<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in.']);
    exit;
}

// Validate input
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
if ($name === '' || $address === '') {
    echo json_encode(['success' => false, 'error' => 'Name and address required.']);
    exit;
}

// DB Connection
$conn = new mysqli('localhost', 'root', '', 'pc-builder');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed.']);
    exit;
}

$userId = $_SESSION['user']['id'];
// Update username and address
$stmt = $conn->prepare('UPDATE users SET username=?, address=? WHERE id=?');
$stmt->bind_param('ssi', $name, $address, $userId);
if ($stmt->execute()) {
    // Update session
    $_SESSION['user']['username'] = $name;
    $_SESSION['user']['address'] = $address;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Update failed.']);
}
$stmt->close();
$conn->close();
