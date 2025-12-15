
<?php
session_start();
// Only allow admin users to access admin panel
$isAdmin = false;
if (isset($_SESSION['user'])) {
    if (is_array($_SESSION['user']) && isset($_SESSION['user']['username']) &&
        ($_SESSION['user']['username'] === 'Mayank.admin' || $_SESSION['user']['username'] === 'smit.admin')) {
        $isAdmin = true;
    } elseif ($_SESSION['user'] === 'Mayank.admin' || $_SESSION['user'] === 'smit.admin') {
        $isAdmin = true;
    }
}
if (!$isAdmin) {
    header('Location: ../index.php');
    exit();
}
// Connect to MySQL database
$conn = new mysqli("localhost", "root", "", "pc-builder");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Fetch product count from individual component tables
$componentTables = ['cpu','gpu','motherboards','power_supplies','ram','storage','cabinets'];
$product_count = 0;
foreach ($componentTables as $t) {
    $safe = $conn->real_escape_string($t);
    $res = $conn->query("SHOW TABLES LIKE '" . $safe . "'");
    if ($res && $res->num_rows > 0) {
        $r = $conn->query("SELECT COUNT(*) as cnt FROM `" . $safe . "`");
        if ($r) {
            $row = $r->fetch_assoc();
            $product_count += (int)$row['cnt'];
        }
    }
}

// Fetch orders
$orders = [];
$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // If items is a JSON string, decode it
        if (isset($row['items'])) {
            $row['items'] = json_decode($row['items'], true);
        }
        $orders[] = $row;
    }
}
// Only count orders with a valid id
$order_count = 0;
foreach ($orders as $order) {
    if (isset($order['id'])) $order_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pc Modification</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="admin-header">
                <h1>Dashboard</h1>
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
            
            <div class="stats-grid">
                <?php // ...existing code now handled above... ?>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #3b82f6;">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $product_count ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #10b981;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= (int)$order_count ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #f59e0b;">
                       <i class='fas fa-rupee-sign'></i>
                    </div>
                    <div class="stat-info">
                        <h3>₹<?= number_format(array_reduce($orders, function($carry, $order) {
                            return $carry + (isset($order['total_price']) ? $order['total_price'] : 0);
                        }, 0), 2) ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <div class="recent-orders">
                <h2>Recent Orders <span style="font-size:14px;color:#888;">(Showing <?= min(5, (int)$order_count) ?> of <?= (int)$order_count ?>)</span></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent_orders = array_slice($orders, 0, 5);
                        foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td>
                                <?php
                                if (isset($order['created_at']) && strtotime($order['created_at'])) {
                                    echo date('M d, Y', strtotime($order['created_at']));
                                } elseif (isset($order['date']) && strtotime($order['date'])) {
                                    echo date('M d, Y', strtotime($order['date']));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>₹<?= isset($order['total_price']) ? number_format($order['total_price'], 2) : (isset($order['total']) ? number_format($order['total'], 2) : 'N/A') ?></td>
                            <td><span class="status-badge"><?= isset($order['status']) ? htmlspecialchars($order['status']) : 'Pending' ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php $conn->close(); ?>
                    </tbody>
                </table>
                </div>
        </main>
    </div>
</body>
</html>