<?php
session_start();

$conn = new mysqli("localhost", "root", "", "pc-builder");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
    $success = "Order status updated successfully!";
}

// Fetch orders from database
$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = [];
while ($row = $result->fetch_assoc()) {
    // If items is a JSON string, decode it
    if (isset($row['items'])) {
        $row['items'] = json_decode($row['items'], true);
    }
    $orders[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - TechBuild</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="admin-header">
                <h1>Manage Orders</h1>
                <div class="user-info">
                    <span>Welcome,
                        <?php
                        if (isset($_SESSION['user'])) {
                            if (is_array($_SESSION['user']) && isset($_SESSION['user']['username'])) {
                                echo htmlspecialchars($_SESSION['user']['username']);
                            } else {
                                echo htmlspecialchars($_SESSION['user']);
                            }
                        } else {
                            echo 'Admin';
                        }
                        ?>
                    </span>
                </div>
            </header>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($order['id']) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td>
                                    <?php
                                    if (isset($order['created_at']) && strtotime($order['created_at'])) {
                                        echo date('M d, Y', strtotime($order['created_at']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td><?= count($order['items']) ?></td>
                                <td>₹<?php
                                if (isset($order['total_price']) && is_numeric($order['total_price'])) {
                                    echo number_format($order['total_price'], 2);
                                } else {
                                    echo 'N/A';
                                }
                                ?></td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <?php $currentStatus = isset($order['status']) ? $order['status'] : 'Pending'; ?>
                                            <option value="Pending" <?= $currentStatus === 'Pending' ? 'selected' : '' ?>>
                                                Pending</option>
                                            <option value="Processing" <?= $currentStatus === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="Shipped" <?= $currentStatus === 'Shipped' ? 'selected' : '' ?>>
                                                Shipped</option>
                                            <option value="Delivered" <?= $currentStatus === 'Delivered' ? 'selected' : '' ?>>
                                                Delivered</option>
                                            <option value="Cancelled" <?= $currentStatus === 'Cancelled' ? 'selected' : '' ?>>
                                                Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <button class="btn-view" data-order='<?= json_encode($order) ?>'>
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div class="modal" id="order-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label">Order ID:</span>
                        <span class="info-value" id="modal-order-id"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Customer:</span>
                        <span class="info-value" id="modal-customer-name"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span class="info-value" id="modal-order-date"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value" id="modal-order-status"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total:</span>
                        <span class="info-value" id="modal-order-total"></span>
                    </div>
                </div>

                <h3>Order Items</h3>
                <div class="order-items" id="modal-order-items">
                    <!-- Items will be added here by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>