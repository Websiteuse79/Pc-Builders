<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="logo">Pc Modification</div>
    <nav>
        <ul>
            <li><a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php" class="<?= $currentPage === 'products.php' ? 'active' : '' ?>"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="orders.php" class="<?= $currentPage === 'orders.php' ? 'active' : '' ?>"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="maintenance_orders.php" class="<?= $currentPage === 'maintenance_orders.php' ? 'active' : '' ?>"><i class="fas fa-tools"></i> Maintenance Orders</a></li>
            <li><a href="../index.php"><i class="fas fa-home"></i> Main Website</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</aside>
