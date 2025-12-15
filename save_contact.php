

<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['Customer_Name']) ? trim($_POST['Customer_Name']) : '';
    $phone = isset($_POST['Customer_Phone_Number']) ? trim($_POST['Customer_Phone_Number']) : '';
    // Validate phone: must be exactly 10 digits
    if (!preg_match('/^\d{10}$/', $phone)) {
        echo "<p style='color:red;'>Phone number must be exactly 10 digits.</p>";
        exit();
    }
    $email = isset($_POST['Customer_Email']) ? trim($_POST['Customer_Email']) : '';
    $address = isset($_POST['Customer_Address']) ? trim($_POST['Customer_Address']) : '';
    $service = isset($_POST['Service_Needed']) ? trim($_POST['Service_Needed']) : '';
    $message = isset($_POST['Message']) ? trim($_POST['Message']) : '';

    // Basic validation
    if ($name && $phone && $email && $address && $service && $message) {
        session_start();
        $user_id = null;
        if (isset($_SESSION['user']['id'])) {
            $user_id = $_SESSION['user']['id'];
        } elseif (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        $stmt = $conn->prepare("INSERT INTO maintenance_orders (Customer_Name, Customer_Phone_Number, Customer_Email, Customer_Address, Service_Needed, Message, Status, created_at, user_id) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW(), ?)");
        $stmt->bind_param("ssssssi", $name, $phone, $email, $address, $service, $message, $user_id);
        if ($stmt->execute()) {
            header("Location: thank_you.php");
            exit();
        } else {
            echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color:red;'>Please fill in all required fields.</p>";
    }
}
?>
