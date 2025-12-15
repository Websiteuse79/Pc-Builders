<!-- Shared navbar for all pages -->
<header>
    <link rel="stylesheet" href="style.css">
    <div class="container">
    <!-- Mobile Menu Button -->
    <div class="mobile-menu" onclick="(function(btn){var nav=document.querySelector('nav'); if(!nav) return; nav.classList.toggle('active'); var isActive=nav.classList.contains('active'); btn.innerHTML = isActive?'<i class=\'fas fa-times\'></i>':'<i class=\'fas fa-bars\'></i>'; })(this);"><i class="fas fa-bars"></i></div>
        <!-- Navbar -->
        <nav>
            <ul>
                <a href="index.php">
                    <img src="./assets/images/pc_modification.png" alt="" style="height: 50px; width: 150px;">
                </a>
                <li><a href="index.php#home">Home</a></li>
                <li><a href="index.php#prebuild">Pre-Build PC</a></li>
                <li><a href="index.php#custom">Custom Pc Build</a></li>
                <li><a href="index.php#services">Services</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="Maintenance.php">Maintenance</a></li>
            </ul>
        </nav>
        <div class="right-icons">
            <?php
            $isAdmin = false;
            if (isset($_SESSION['user'])) {
                // If using array for user session
                if ((isset($_SESSION['user']['username']) && 
                    ($_SESSION['user']['username'] === 'Mayank.admin' || $_SESSION['user']['username'] === 'smit.admin')) ||
                    // If using string for user session (legacy)
                    ($_SESSION['user'] === 'Mayank.admin' || $_SESSION['user'] === 'smit.admin')) {
                    $isAdmin = true;
                }
            }
            ?>
            <?php if (!$isAdmin): ?>
                <div class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </div>
                <a href="#" id="navbarProfileIcon">
                    <i class="fa fa-user"></i>
                </a>
            <?php else: ?>
                <a href="admin/index.php" id="navbarDashboardIcon" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Profile Sidebar (shared) -->
<div class="profile-overlay" id="profileOverlay"></div>
<div class="profile-sidebar" id="profileSidebar">
    <div class="profile-header">
        <h2>Profile</h2>
        <div class="close-profile" id="closeProfile">
            <i class="fas fa-times"></i>
        </div>
    </div>
    <div class="profile-picture" id="profilePictureBox" style="position:relative; cursor:pointer;">
        <?php
        $profilePhoto = '';
        if (isset($dbUser['profile_icon']) && $dbUser['profile_icon']) {
            $profilePhoto = htmlspecialchars($dbUser['profile_icon']);
        } elseif (isset($_SESSION['user']['profile_icon']) && $_SESSION['user']['profile_icon']) {
            $profilePhoto = htmlspecialchars($_SESSION['user']['profile_icon']);
        }
        ?>
        <img id="profilePhotoImg" src="<?php echo $profilePhoto ? $profilePhoto : 'assets/images/default-user.png'; ?>" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:<?php echo $profilePhoto ? 'block' : 'none'; ?>;" />
        <i class="fas fa-user" id="profilePhotoIcon" style="display:<?php echo $profilePhoto ? 'none' : 'block'; ?>;"></i>
        <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" style="display:none;" />
        <div id="photoUploadMsg" style="position:absolute;bottom:-18px;left:0;width:100%;text-align:center;font-size:12px;color:#10b981;"></div>
    </div>
    <?php
    $dbUser = null;
    if (isset($_SESSION['user']['id'])) {
        $conn = new mysqli('localhost', 'root', '', 'pc-builder');
        if (!$conn->connect_error) {
            $uid = $_SESSION['user']['id'];
            $stmt = $conn->prepare('SELECT username, email, address FROM users WHERE id=?');
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $dbUser = $row;
            }
            $stmt->close();
        }
        $conn->close();
    }
    ?>
    <div class="user-info">
        <h2>
            <?php echo $dbUser ? htmlspecialchars($dbUser['username']) : (isset($_SESSION['user']['username']) ? htmlspecialchars($_SESSION['user']['username']) : 'Guest'); ?>
        </h2>
        <p>
            <?php echo $dbUser ? htmlspecialchars($dbUser['email']) : (isset($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : ''); ?>
        </p>
    </div>
    <div class="profile-section">
        <h3>Address</h3>
        <p><?php echo $dbUser ? htmlspecialchars($dbUser['address']) : (isset($_SESSION['user']['address']) ? htmlspecialchars($_SESSION['user']['address']) : ''); ?></p>
    </div>
    <div class="profile-section">
        <h3>Recent Orders</h3>
        <?php
        $recentAll = [];
        $userId = null;
        if (isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
        } elseif (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        if ($userId) {
            $conn = new mysqli('localhost', 'root', '', 'pc-builder');
            if (!$conn->connect_error) {
                // Normal orders
                $sql = "SELECT id, items, total_price, created_at, status FROM orders WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $row['type'] = 'normal';
                    $row['items'] = json_decode($row['items'], true);
                    $recentAll[] = $row;
                }
                $stmt->close();
                // Maintenance orders
                $sql2 = "SELECT id, Service_Needed, created_at, Status FROM maintenance_orders WHERE user_id = ?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param('i', $userId);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                while ($row2 = $result2->fetch_assoc()) {
                    $row2['type'] = 'maintenance';
                    $recentAll[] = $row2;
                }
                $stmt2->close();
            }
            $conn->close();
        }
        // Sort all by created_at desc
        usort($recentAll, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        $recentAll = array_slice($recentAll, 0, 4); // Show top 4 recent orders (any type)
        if (count($recentAll) > 0): ?>
            <table class="orders-table">
                <?php foreach ($recentAll as $order): ?>
                    <?php if ($order['type'] === 'normal'): ?>
                        <tr>
                            <td colspan="2" class="order-items" style="color: blanchedalmond;">
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
                        </tr>
                        <tr>
                            <td class="order-date"><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at']))); ?></td>
                            <td class="order-status-price">
                                <span class="order-status"><?php echo htmlspecialchars($order['status'] ?: 'Pending'); ?></span><br>
                                <span class="order-price">₹<?php echo htmlspecialchars(number_format($order['total_price'], 2)); ?></span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="order-items" style="color: #f59e0b;">
                                Maintenance: <?php echo htmlspecialchars($order['Service_Needed']); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="order-date"><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at']))); ?></td>
                            <td class="order-status-price">
                                <span class="order-status"><?php echo htmlspecialchars($order['Status'] ?: 'Pending'); ?></span><br>
                                <span class="order-price">-</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
            <a href="#" class="view-orders" id="openAllOrdersModal">View All Orders</a>
        <?php else: ?>
            <p>No recent orders.</p>
        <?php endif; ?>
    </div>
    <!-- All Orders Modal -->
    <div id="allOrdersModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:#fff; padding:24px; border-radius:8px; max-width:900px; width:95vw; margin:auto; position:relative; max-height:90vh; overflow-y:auto;">
            <h2>Your Orders</h2>
            <span id="closeAllOrdersModal" style="position:absolute;top:12px;right:18px;font-size:28px;cursor:pointer;">&times;</span>
            <div id="allOrdersContent">
                <div style="text-align:center;">Loading...</div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['user'])): ?>
        <button class="edit-btn" id="editProfileBtn">Edit Details</button>
        <button class="logout-btn" id="logoutBtn">Logout</button>
        <!-- Edit Profile Modal/Form -->
        <div id="editProfileModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
            <div style="background:#fff; padding:24px; border-radius:8px; max-width:400px; margin:auto; position:relative;">
                <h3>Edit Profile</h3>
                <form id="editProfileForm">
                    <label for="editName">Name</label>
                    <input type="text" id="editName" name="name" value="<?php echo isset($_SESSION['user']['username']) ? htmlspecialchars($_SESSION['user']['username']) : ''; ?>" required style="width:100%;margin-bottom:10px;">
                    <label for="editAddress">Address</label>
                    <input type="text" id="editAddress" name="address" value="<?php echo isset($_SESSION['user']['address']) ? htmlspecialchars($_SESSION['user']['address']) : ''; ?>" required style="width:100%;margin-bottom:10px;">
                    <button type="submit" class="btn">Save</button>
                    <button type="button" id="closeEditProfile" class="btn btn-outline" style="margin-left:10px;">Cancel</button>
                </form>
                <div id="editProfileMsg" style="color:green; margin-top:10px;"></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Cart Sidebar (shared) -->
<div class="cart-sidebar">
    <div class="cart-header">
        <h3>Your Cart</h3>
        <button class="close-cart">&times;</button>
    </div>
    <div class="cart-items">
        <!-- Cart items will be added here dynamically -->
    </div>
    <div class="cart-total">
        <span>Total: ₹<span class="total-amount">0</span></span>
        <button class="checkout-btn">Checkout</button>
    </div>
</div>



<!-- Checkout Modal -->
    <div id="checkoutModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:#fff; padding:24px; border-radius:8px; max-width:400px; margin:auto; position:relative;">
            <h3>Checkout</h3>
            <form id="checkoutForm">
                <div style="margin-bottom:10px;">
                    <label for="checkoutName">Name</label>
                    <input type="text" id="checkoutName" name="name" required style="width:100%;">
                </div>
                <div style="margin-bottom:10px;">
                    <label for="checkoutPhone">Phone</label>
                    <input type="text" id="checkoutPhone" name="phone" required pattern="[0-9]{10,}" title="Enter a valid phone number" style="width:100%;">
                </div>
                <div style="margin-bottom:10px;">
                    <label for="checkoutAddress">Address</label>
                    <input type="text" id="checkoutAddress" name="address" required style="width:100%;">
                </div>
                <div style="margin-bottom:10px;">
                    <h4>Cart Details</h4>
                    <div id="checkoutCartDetails" style="max-height:120px;overflow-y:auto;font-size:14px;"></div>
                    <div style="margin-top:8px;">Total: ₹<span id="checkoutTotal">0</span></div>
                </div>
                <button type="submit" class="btn">Place Order</button>
                <button type="button" id="closeCheckoutModal" class="btn btn-outline" style="margin-left:10px;">Cancel</button>
            </form>
            <div id="checkoutMsg" style="color:red; margin-top:10px;"></div>
        </div>
    </div>





 <script>
    // Expose login status to JS for client-side checks
    window.isLoggedIn = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;
    </script>
    <script src="script.js"></script>
    <script>
    // Prevent checkout/add-to-cart if not logged in
    document.addEventListener('DOMContentLoaded', function() {
        var isLoggedIn = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;
        // Prevent checkout
        var checkoutBtn = document.querySelector('.checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', function(e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('Please login to place an order.');
                    window.location.href = 'Login/login.php';
                    return false;
                }
            });
        }
        // Prevent add-to-cart for custom build
        var addToCartBuild = document.querySelector('.add-to-cart-build');
        if (addToCartBuild) {
            addToCartBuild.addEventListener('click', function(e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('Please login to add items to cart.');
                    window.location.href = 'Login/login.php';
                    return false;
                }
            });
        }

        // Prevent opening profile sidebar if not logged in
        var profileIcon = document.querySelector('.fa-user');
        if (profileIcon) {
            profileIcon.addEventListener('click', function(e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    alert('Please login to view your profile.');
                    window.location.href = 'Login/login.php';
                    return false;
                }
                // If logged in, let script.js handle opening the sidebar
            });
        }

        // All Orders Modal logic
        var openAllOrdersBtn = document.getElementById('openAllOrdersModal');
        var allOrdersModal = document.getElementById('allOrdersModal');
        var closeAllOrdersModal = document.getElementById('closeAllOrdersModal');
        var allOrdersContent = document.getElementById('allOrdersContent');
        if (openAllOrdersBtn && allOrdersModal && closeAllOrdersModal && allOrdersContent) {
            openAllOrdersBtn.addEventListener('click', function(e) {
                e.preventDefault();
                allOrdersModal.style.display = 'flex';
                allOrdersContent.innerHTML = '<div style="text-align:center;">Loading...</div>';
                fetch('fetch_all_orders.php')
                    .then(response => response.text())
                    .then(html => {
                        allOrdersContent.innerHTML = html;
                    })
                    .catch(() => {
                        allOrdersContent.innerHTML = '<div style="color:red;text-align:center;">Failed to load orders.</div>';
                    });
            });
            closeAllOrdersModal.addEventListener('click', function() {
                allOrdersModal.style.display = 'none';
            });
            // Close modal on overlay click
            allOrdersModal.addEventListener('click', function(e) {
                if (e.target === allOrdersModal) {
                    allOrdersModal.style.display = 'none';
                }
            });
        }

        // Edit Profile Modal logic
        var editBtn = document.getElementById('editProfileBtn');
        var modal = document.getElementById('editProfileModal');
        var closeBtn = document.getElementById('closeEditProfile');
        if (editBtn && modal && closeBtn) {
            editBtn.addEventListener('click', function() {
                modal.style.display = 'flex';
            });
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
        // Handle form submit
        var form = document.getElementById('editProfileForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(form);
                fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    var msg = document.getElementById('editProfileMsg');
                    if (data.success) {
                        msg.textContent = 'Profile updated! Reloading...';
                        setTimeout(function(){ location.reload(); }, 1200);
                    } else {
                        msg.textContent = data.error || 'Update failed.';
                        msg.style.color = 'red';
                    }
                })
                .catch(() => {
                    var msg = document.getElementById('editProfileMsg');
                    msg.textContent = 'Update failed.';
                    msg.style.color = 'red';
                });
            });
        }
        // Profile photo upload logic
        var profilePictureBox = document.getElementById('profilePictureBox');
        var profilePhotoInput = document.getElementById('profilePhotoInput');
        var profilePhotoImg = document.getElementById('profilePhotoImg');
        var profilePhotoIcon = document.getElementById('profilePhotoIcon');
        var photoUploadMsg = document.getElementById('photoUploadMsg');
        if (profilePictureBox && profilePhotoInput) {
            profilePictureBox.addEventListener('click', function() {
                profilePhotoInput.click();
            });
            profilePhotoInput.addEventListener('change', function() {
                if (profilePhotoInput.files && profilePhotoInput.files[0]) {
                    var formData = new FormData();
                    formData.append('profile_photo', profilePhotoInput.files[0]);
                    fetch('upload_profile_photo.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.url) {
                            profilePhotoImg.src = data.url;
                            profilePhotoImg.style.display = 'block';
                            if (profilePhotoIcon) profilePhotoIcon.style.display = 'none';
                            if (photoUploadMsg) {
                                photoUploadMsg.textContent = 'Photo updated!';
                                setTimeout(()=>{photoUploadMsg.textContent='';}, 1500);
                            }
                        } else {
                            if (photoUploadMsg) {
                                photoUploadMsg.textContent = data.error || 'Upload failed.';
                                photoUploadMsg.style.color = 'red';
                                setTimeout(()=>{photoUploadMsg.textContent='';photoUploadMsg.style.color='#10b981';}, 2000);
                            }
                        }
                    })
                    .catch(()=>{
                        if (photoUploadMsg) {
                            photoUploadMsg.textContent = 'Upload failed.';
                            photoUploadMsg.style.color = 'red';
                            setTimeout(()=>{photoUploadMsg.textContent='';photoUploadMsg.style.color='#10b981';}, 2000);
                        }
                    });
                }
            });
        }
    });
    </script>
