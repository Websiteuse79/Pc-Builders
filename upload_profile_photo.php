<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in.']);
    exit;
}

if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded.']);
    exit;
}

$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type.']);
    exit;
}

if ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 2MB).']);
    exit;
}

$uploadDir = 'assets/images/profile_photos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$userId = $_SESSION['user']['id'];
$filename = 'user_' . $userId . '_' . time() . '.' . $ext;
$target = $uploadDir . $filename;

if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
    // Save path in DB
    $conn = new mysqli('localhost', 'root', '', 'pc-builder');
    if (!$conn->connect_error) {
        $url = $target;
        $stmt = $conn->prepare('UPDATE users SET profile_icon=? WHERE id=?');
        $stmt->bind_param('si', $url, $userId);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        // Update session
        $_SESSION['user']['profile_icon'] = $url;
        echo json_encode(['success' => true, 'url' => $url]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'DB error.']);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Upload failed.']);
    exit;
}
