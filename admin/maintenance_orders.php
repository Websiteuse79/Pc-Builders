<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "pc-builder");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle status updates for maintenance orders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    // Update the 'maintenance_orders' table
    // Assuming database column name is 'Status' or 'status'. Adjust if necessary.
    $stmt = $conn->prepare("UPDATE maintenance_orders SET Status=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
    $success = "Maintenance order status updated successfully!";
}

// Fetch maintenance orders from the database
$result = $conn->query("SELECT * FROM maintenance_orders ORDER BY created_at DESC");
$orders = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Maintenance Orders - TechBuild</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="admin-header">
                <h1>Manage Maintenance Orders</h1>
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
                            <th>Phone Number</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($order['id']) ?></td>
                                <td><?= htmlspecialchars($order['Customer_Name']) ?></td>
                                <td><?= htmlspecialchars($order['Customer_Phone_Number']) ?></td>
                               
                                <td>
                                    <?php
                                    if (isset($order['created_at']) && strtotime($order['created_at'])) {
                                        echo date('M d, Y', strtotime($order['created_at']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <?php $currentStatus = isset($order['Status']) ? $order['Status'] : 'Pending'; ?>
                                            <option value="Pending" <?= $currentStatus === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="In Progress" <?= $currentStatus === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="Completed" <?= $currentStatus === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="Cancelled" <?= $currentStatus === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <?php
                                    // Encode order as JSON then base64 to safely include in a data attribute
                                    $jsonOrder = json_encode($order, JSON_UNESCAPED_UNICODE);
                                    $b64Order = base64_encode($jsonOrder);
                                    ?>
                                    <button class="btn-view" data-order="<?= htmlspecialchars($b64Order, ENT_QUOTES, 'UTF-8') ?>">
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

    <div class="modal" id="order-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Maintenance Request Details</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="order-info">
                    <div class="info-row"><span class="info-label">Order ID:</span> <span class="info-value" id="modal-order-id"></span></div>
                    <div class="info-row"><span class="info-label">Customer:</span> <span class="info-value" id="modal-customer-name"></span></div>
                    <div class="info-row"><span class="info-label">Phone:</span> <span class="info-value" id="modal-customer-phone"></span></div>
                    <div class="info-row"><span class="info-label">Email:</span> <span class="info-value" id="modal-customer-email"></span></div>
                    <div class="info-row"><span class="info-label">Address:</span> <span class="info-value" id="modal-customer-address"></span></div>
                    <div class="info-row"><span class="info-label">Date:</span> <span class="info-value" id="modal-order-date"></span></div>
                    <div class="info-row"><span class="info-label">Status:</span> <span class="info-value" id="modal-order-status"></span></div>
                    <hr>
                    <div class="info-row"><span class="info-label">Service Needed:</span> <span class="info-value" id="modal-service-needed"></span></div>
                    <div class="info-row"><span class="info-label">Message:</span> <p class="info-value" id="modal-message"></p></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
        // Wrap everything in a try/catch so errors are visible in console
        try {
            console.log('maintenance_orders: script init');

            const modal = document.getElementById('order-modal');
            if (!modal) {
                console.warn('maintenance_orders: modal element not found');
                return;
            }

            const closeModalBtn = modal.querySelector('.close-modal');
            const setModalField = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            };

            // Safer base64 -> UTF-8 decode
            const b64ToUtf8 = (b64) => {
                try {
                    // atob gives binary string; convert to percent-encoding then decode
                    const binary = window.atob(b64);
                    let bytes = [];
                    for (let i = 0; i < binary.length; i++) {
                        bytes.push(binary.charCodeAt(i));
                    }
                    // Decode bytes to string
                    const decoder = new TextDecoder();
                    return decoder.decode(new Uint8Array(bytes));
                } catch (e) {
                    console.warn('b64ToUtf8 failed, falling back to decodeURIComponent trick', e);
                    try {
                        return decodeURIComponent(escape(window.atob(b64)));
                    } catch (e2) {
                        console.error('all base64 decode attempts failed', e2);
                        throw e2;
                    }
                }
            };

            // Use event delegation so clicks are always handled
            document.addEventListener('click', function(e) {
                const btn = e.target.closest && e.target.closest('.btn-view');
                if (!btn) return; // not a view button

                console.log('maintenance_orders: View button clicked');

                const b64 = btn.getAttribute('data-order') || '';
                if (!b64) {
                    console.error('maintenance_orders: data-order attribute empty');
                    alert('Order details not available.');
                    return;
                }

                let orderData = null;
                try {
                    const jsonStr = b64ToUtf8(b64);
                    orderData = JSON.parse(jsonStr);
                } catch (err) {
                    console.error('Failed to decode/parse order data:', err);
                    alert('Unable to load order details. See console for details.');
                    return;
                }

                const get = (key, fallback = 'N/A') => (orderData && orderData.hasOwnProperty(key) && orderData[key] !== null && orderData[key] !== '') ? orderData[key] : fallback;

                setModalField('modal-order-id', '#' + get('id'));
                setModalField('modal-customer-name', get('Customer_Name'));
                setModalField('modal-customer-phone', get('Customer_Phone_Number'));
                setModalField('modal-customer-email', get('Customer_Email'));
                setModalField('modal-customer-address', get('Customer_Address'));
                setModalField('modal-service-needed', get('Service_Needed'));
                setModalField('modal-message', get('Message'));
                setModalField('modal-order-status', get('Status'));

                const createdAt = get('created_at', null);
                if (createdAt) {
                    const date = new Date(createdAt);
                    if (!isNaN(date.getTime())) {
                        const formattedDate = date.toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' });
                        setModalField('modal-order-date', formattedDate);
                    } else {
                        setModalField('modal-order-date', 'N/A');
                    }
                } else {
                    setModalField('modal-order-date', 'N/A');
                }

                // Show modal — force visibility styles in case stylesheet hides it
                try {
                    const cs = window.getComputedStyle(modal);
                    console.log('maintenance_orders: modal computedStyle before show', {
                        display: cs.display,
                        visibility: cs.visibility,
                        opacity: cs.opacity,
                        zIndex: cs.zIndex
                    });
                } catch (e) {
                    console.warn('maintenance_orders: getComputedStyle failed', e);
                }

                modal.style.display = 'block';
                modal.style.visibility = 'visible';
                modal.style.opacity = '1';
                modal.style.zIndex = '9999';

                // Force modal-content to be centered via inline styles (overrides CSS)
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.style.position = 'fixed';
                    modalContent.style.left = '50%';
                    modalContent.style.top = '50%';
                    modalContent.style.transform = 'translate(-50%, -50%)';
                    modalContent.style.maxHeight = '90vh';
                    modalContent.style.overflowY = 'auto';
                    modalContent.style.zIndex = '10000';
                }

                try {
                    const cs2 = window.getComputedStyle(modal);
                    console.log('maintenance_orders: modal computedStyle after show', {
                        display: cs2.display,
                        visibility: cs2.visibility,
                        opacity: cs2.opacity,
                        zIndex: cs2.zIndex
                    });
                } catch (e) {
                    console.warn('maintenance_orders: getComputedStyle failed (after show)', e);
                }
            });

            // Function to close the modal
            const closeModal = () => {
                if (!modal) return;
                modal.style.display = 'none';
                modal.style.visibility = '';
                modal.style.opacity = '';
                modal.style.zIndex = '';
                // remove inline centering styles from modal-content
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.style.position = '';
                    modalContent.style.left = '';
                    modalContent.style.top = '';
                    modalContent.style.transform = '';
                    modalContent.style.maxHeight = '';
                    modalContent.style.overflowY = '';
                    modalContent.style.zIndex = '';
                }
            };

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeModal);
            } else {
                console.warn('maintenance_orders: close modal button not found');
            }

            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            console.log('maintenance_orders: script ready');

        } catch (err) {
            console.error('maintenance_orders: unexpected error in script', err);
        }
    })();
    </script>
</body>
</html>