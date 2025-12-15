<?php
session_start();

$conn = new mysqli("localhost", "root", "", "pc-builder");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Allowed component tables (whitelist)
$componentTables = [
    'cpu', 'gpu', 'motherboards', 'power_supplies', 'ram', 'storage', 'cabinets'
];

// Explicit field mapping per table (order matters for form)
$tableFieldMap = [
    'cpu' => ['name','cores','threads','speed','price'],
    'gpu' => ['name','vram','price'],
    'motherboards' => ['name','chipset','socket','price'],
    'power_supplies' => ['name','wattage','efficiency','price'],
    'ram' => ['name','size','speed','price'],
    'storage' => ['name','type','capacity','price'],
    'cabinets' => ['name','price']
];

// Numeric fields heuristic
$numericFields = ['price','cores','threads','vram','wattage'];

// Helper: get columns for a table
function get_table_columns($conn, $table) {
    $cols = [];
    $safe = $conn->real_escape_string($table);
    $res = $conn->query("SHOW COLUMNS FROM `" . $safe . "`");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $cols[] = $row['Field'];
        }
    }
    return $cols;
}

// Helper: pick first matching column from candidates
function pick_col($cols, $candidates) {
    foreach ($candidates as $c) {
        foreach ($cols as $col) {
            if (strcasecmp($col, $c) === 0) return $col;
        }
    }
    return null;
}

// Handle add product -> insert into selected component table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $table = isset($_POST['table']) ? $_POST['table'] : '';
    if (!in_array($table, $componentTables)) {
        $error = 'Invalid target table selected.';
    } else {
        $cols = get_table_columns($conn, $table);
        $mapping = $tableFieldMap[$table] ?? [];

        $values = [];
        $types = '';
        $insertCols = [];
        foreach ($mapping as $field) {
            // only insert if the column exists in the table
            if (in_array($field, $cols)) {
                $insertCols[] = "`$field`";
                $val = $_POST[$field] ?? '';
                if (in_array($field, $numericFields)) {
                    $val = $val === '' ? 0 : (float) $val;
                    $types .= 'd';
                } else {
                    $val = $conn->real_escape_string($val);
                    $types .= 's';
                }
                $values[] = $val;
            }
        }

        if (empty($insertCols)) {
            $error = 'No suitable columns found in target table to insert.';
        } 
        else {
            $sql = "INSERT INTO `" . $conn->real_escape_string($table) . "` (" . implode(',', $insertCols) . ") VALUES (" . implode(',', array_fill(0, count($values), '?')) . ")";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // bind dynamically
                $bindNames = [];
                $bindParams = [];
                $bindNames[] = $types;
                for ($i = 0; $i < count($values); $i++) {
                    $bindParams[] = &$values[$i];
                }
                array_unshift($bindParams, $types);
                call_user_func_array([$stmt, 'bind_param'], $bindParams);
                if ($stmt->execute()) {
                    $success = "Product added successfully!";
                } else {
                    $error = 'Insert failed: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Insert prepare failed: ' . $conn->error;
            }
        }
    }
}

// Handle edit button (pre-fill form)
$edit_product = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    // Expect product_id and table to be provided in the form row
    $edit_product = [
        'id' => $_POST['product_id'],
        'table' => $_POST['table'],
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? ''
    ];
}

// Handle update product -> update selected component table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = (int) $_POST['product_id'];
    $table = $_POST['table'] ?? '';
    if (!in_array($table, $componentTables)) {
        $error = 'Invalid table for update.';
    } else {
        $cols = get_table_columns($conn, $table);
        $mapping = $tableFieldMap[$table] ?? [];

        $sets = [];
        $values = [];
        $types = '';
        foreach ($mapping as $field) {
            if (in_array($field, $cols)) {
                $sets[] = "`$field`=?";
                $val = $_POST[$field] ?? '';
                if (in_array($field, $numericFields)) {
                    $val = $val === '' ? 0 : (float) $val;
                    $types .= 'd';
                } else {
                    $val = $conn->real_escape_string($val);
                    $types .= 's';
                }
                $values[] = $val;
            }
        }

        if (empty($sets)) {
            $error = 'No suitable columns found in target table to update.';
        } else {
            $sql = "UPDATE `" . $conn->real_escape_string($table) . "` SET " . implode(',', $sets) . " WHERE id=?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // bind params + id
                $values[] = $product_id;
                $types .= 'i';
                $bindParams = [];
                $bindParams[] = $types;
                for ($i = 0; $i < count($values); $i++) { $bindParams[] = &$values[$i]; }
                call_user_func_array([$stmt, 'bind_param'], $bindParams);
                if ($stmt->execute()) {
                    $success = 'Product updated successfully!';
                } else {
                    $error = 'Update failed: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Update prepare failed: ' . $conn->error;
            }
        }
    }
}

// Handle delete product. Expect table and id via GET: ?delete=ID&table=table_name
if (isset($_GET['delete']) && isset($_GET['table'])) {
    $product_id = (int) $_GET['delete'];
    $table = $_GET['table'];
    if (in_array($table, $componentTables)) {
        $stmt = $conn->prepare("DELETE FROM `" . $conn->real_escape_string($table) . "` WHERE id=?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: products.php');
    exit;
}

// Fetch all products from component tables and normalize fields
$products = [];
foreach ($componentTables as $table) {
    // ensure table exists by attempting to SHOW TABLES LIKE
    $safe = $conn->real_escape_string($table);
    $check = $conn->query("SHOW TABLES LIKE '" . $safe . "'");
    if (!$check || $check->num_rows === 0) continue;

    $cols = get_table_columns($conn, $table);
    $nameCol = pick_col($cols, ['name','Name','model','Model','title','Title']);
    $priceCol = pick_col($cols, ['price','Price','cost','Cost','MRP','mrp']);
    $descCol = pick_col($cols, ['description','Description','details','Details','specs','Specs']);

    $res = $conn->query("SELECT * FROM `" . $safe . "`");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $prod = [];
            $prod['id'] = $row['id'] ?? ($row['ID'] ?? null);
            $prod['table'] = $table;
            $prod['raw'] = $row;
            // normalized fields
            if ($nameCol && isset($row[$nameCol])) $prod['name'] = $row[$nameCol];
            else {
                // fallback: pick first non-id column
                foreach ($row as $k => $v) { if (strtolower($k) !== 'id') { $prod['name'] = $v; break; } }
            }
            $prod['price'] = $priceCol && isset($row[$priceCol]) ? (float)$row[$priceCol] : 0.0;
            $prod['description'] = $descCol && isset($row[$descCol]) ? $row[$descCol] : '';
            $products[] = $prod;
        }
    }
}

// Helper to render dynamic fields server-side for initial load (used when editing)
function render_fields_html($table, $values = []) {
    global $tableFieldMap;
    $html = '';
    $fields = $tableFieldMap[$table] ?? [];
    foreach ($fields as $f) {
        $val = $values[$f] ?? '';
        $label = ucfirst(str_replace('_',' ',$f));
        // determine input type for numeric fields
        global $numericFields;
        $isNumeric = in_array($f, $numericFields);
        $type = $isNumeric ? 'number' : 'text';
        $step = $isNumeric ? ' step="any"' : '';
        $min = $isNumeric ? ' min="0"' : '';
        $html .= "<div class=\"form-group dynamic-field\" data-field=\"" . htmlspecialchars($f) . "\">";
        $html .= "<label for=\"" . htmlspecialchars($f) . "\">" . htmlspecialchars($label) . "</label>";
        $html .= "<input type=\"" . $type . "\" id=\"" . htmlspecialchars($f) . "\" name=\"" . htmlspecialchars($f) . "\" value=\"" . htmlspecialchars($val) . "\"" . $step . $min . ">";
        $html .= "</div>";
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products -Pc Modification</title>
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

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="product-actions">
                <button class="btn" id="add-product-btn"><i class="fas fa-plus"></i> Add Product</button>
            </div><br>

            <!-- Add/Edit Product Form (hidden by default) -->
            <div class="product-form-container" id="product-form-container" style="display: <?= $edit_product ? 'block' : 'none' ?>;">
                <form method="POST" class="product-form" id="product-form">
                    <input type="hidden" name="product_id" id="product_id"
                        value="<?= $edit_product ? htmlspecialchars($edit_product['id']) : '' ?>">
                    <h2 id="form-title"><?= $edit_product ? 'Edit Product' : 'Add New Product' ?></h2>
                            <div class="form-row">
                                <div class="form-group" style="flex:1">
                                    <label for="category">Category</label>
                                    <select id="table" name="table" required>
                                        <option value="" <?= $edit_product ? '' : 'selected' ?>>-- Select category --</option>
                                        <?php foreach ($componentTables as $t): ?>
                                            <option value="<?= htmlspecialchars($t) ?>" <?= $edit_product && isset($edit_product['table']) && $edit_product['table'] == $t ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst(str_replace('_',' ',$t))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div id="dynamic-fields">
                                <?php
                                    // If editing, render the fields for the selected table with values
                                    $initialTable = $edit_product['table'] ?? null;
                                    $initialValues = [];
                                    if ($edit_product && !empty($initialTable)) {
                                        // Pull raw values if available
                                        if (!empty($edit_product['id']) && !empty($edit_product['table'])) {
                                            $id = (int)$edit_product['id'];
                                            $t = $edit_product['table'];
                                            $res = $conn->query("SELECT * FROM `" . $conn->real_escape_string($t) . "` WHERE id=" . $id . " LIMIT 1");
                                            if ($res && $row = $res->fetch_assoc()) { $initialValues = $row; }
                                        } else {
                                            // fallback to posted values
                                            $initialValues = [
                                                'name' => $edit_product['name'] ?? '',
                                                'price' => $edit_product['price'] ?? '',
                                                'description' => $edit_product['description'] ?? ''
                                            ];
                                        }
                                        // render server-side when editing
                                        echo render_fields_html($initialTable, $initialValues);
                                    }
                                ?>
                            </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancel-form">Cancel</button>
                        <button type="submit" class="btn" name="<?= $edit_product ? 'update_product' : 'add_product' ?>"
                            id="submit-btn"><?= $edit_product ? 'Update Product' : 'Add Product' ?></button>
                    </div>
                </form>
            </div>

            <div class="products-table">
                <div class="category-filters">
                    <button class="cat-btn active" data-table="all">All</button>
                    <?php foreach ($componentTables as $t): ?>
                        <button class="cat-btn" data-table="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$t))) ?></button>
                    <?php endforeach; ?>
                </div>
                <br>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr data-table="<?= htmlspecialchars($product['table']) ?>">
                                        <td><?= htmlspecialchars($product['id']) ?></td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= htmlspecialchars(ucfirst($product['table'])) ?></td>
                                        <td>₹<?= number_format($product['price'], 2) ?></td>
                                        <td class="actions">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                                <input type="hidden" name="table" value="<?= htmlspecialchars($product['table']) ?>">
                                                <?php
                                                    // output hidden inputs for each mapped field if present in raw row
                                                    $map = $tableFieldMap[$product['table']] ?? [];
                                                    foreach ($map as $f) {
                                                        $v = $product['raw'][$f] ?? '';
                                                        echo '<input type="hidden" name="' . htmlspecialchars($f) . '" value="' . htmlspecialchars($v) . '">';
                                                    }
                                                ?>
                                                <button type="submit" class="btn-edit" name="edit_product">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </form>
                                            <a href="products.php?delete=<?= $product['id'] ?>&table=<?= urlencode($product['table']) ?>" class="btn-delete"
                                                onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script>
        // Inject mapping for dynamic fields
        window.__tableFieldMap = <?= json_encode($tableFieldMap) ?>;
        window.__numericFields = <?= json_encode($numericFields) ?>;
        // If initial table exists, render fields (this runs after script.js loads)
        document.addEventListener('DOMContentLoaded', function() {
            var initTable = '<?= isset($initialTable) ? htmlspecialchars($initialTable, ENT_QUOTES) : $componentTables[0] ?>';
            var initValues = <?= json_encode($initialValues) ?>;
            try { renderDynamicFields(initTable, initValues); } catch(e){}
        });
    </script>
</body>

</html>