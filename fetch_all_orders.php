<?php
session_start();
if (!isset($_SESSION['user']['id']) && !isset($_SESSION['user_id'])) {
    echo '<div style="color:red;text-align:center;">Please login to view your orders.</div>';
    exit();
}
$userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'pc-builder');
if ($conn->connect_error) {
    echo '<div style="color:red;text-align:center;">Database connection failed.</div>';
    exit();
}
// Fetch all normal orders
$orders = [];
$stmt = $conn->prepare('SELECT id, items, total_price, created_at, status FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['items'] = json_decode($row['items'], true);
    $orders[] = $row;
}
$stmt->close();
// Fetch all maintenance orders
$maintenanceOrders = [];
$stmt2 = $conn->prepare('SELECT id, Service_Needed, created_at, Status FROM maintenance_orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt2->bind_param('i', $userId);
$stmt2->execute();
$result2 = $stmt2->get_result();
while ($row2 = $result2->fetch_assoc()) {
    $maintenanceOrders[] = $row2;
}
$stmt2->close();
$conn->close();
?>
<div>
    <h3>Normal Orders</h3>
    <?php if (count($orders) > 0): ?>
    <table class="orders-table" style="width:100%;margin-bottom:24px;">
        <thead>
            <tr><th>Order ID</th><th>Items</th><th>Date</th><th>Status</th><th>Total</th></tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?= htmlspecialchars($order['id']) ?></td>
                <td>
                    <?php
                    $itemList = '';
                    if (!empty($order['items'])) {
                        foreach ($order['items'] as $item) {
                            if (isset($item['name'])) {
                                $itemList .= htmlspecialchars($item['name']) . ' (x' . (isset($item['quantity']) ? (int)$item['quantity'] : 1) . '), ';
                            } elseif (isset($item['title'])) {
                                $itemList .= htmlspecialchars($item['title']) . ' - ₹' . htmlspecialchars($item['price']) . ', ';
                            }
                        }
                        $itemList = rtrim($itemList, ', ');
                    }
                    echo $itemList ? $itemList : 'No items';
                    ?>
                </td>
                <td><?= htmlspecialchars(date('M d, Y', strtotime($order['created_at']))) ?></td>
                <td><?= htmlspecialchars($order['status'] ?: 'Pending') ?></td>
                <td>₹<?= htmlspecialchars(number_format($order['total_price'], 2)) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No normal orders found.</p>
    <?php endif; ?>
    <h3 style="margin-top:32px;">Maintenance Orders</h3>
    <?php if (count($maintenanceOrders) > 0): ?>
    <table class="orders-table" style="width:100%;">
        <thead>
            <tr><th>ID</th><th>Service Needed</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($maintenanceOrders as $morder): ?>
            <tr>
                <td>#<?= htmlspecialchars($morder['id']) ?></td>
                <td><?= htmlspecialchars($morder['Service_Needed']) ?></td>
                <td><?= htmlspecialchars(date('M d, Y', strtotime($morder['created_at']))) ?></td>
                <td><?= htmlspecialchars($morder['Status'] ?: 'Pending') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No maintenance orders found.</p>
    <?php endif; ?>
</div>
