<?php
$conn = new mysqli("localhost", "root", "", "pc-builder");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM components ORDER BY category";
$result = $conn->query($sql);

$currentCategory = "";

echo "<h2>PC Components List</h2>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row["category"] != $currentCategory) {
            if ($currentCategory != "") echo "<hr>";
            $currentCategory = $row["category"];
            echo "<h3>" . ucfirst($currentCategory) . "</h3>";
        }
        echo "<b>" . $row["name"] . "</b> - ₹" . $row["price"] . "<br>";
        echo "<small>" . $row["description"] . "</small><br><br>";
    }
} else {
    echo "No components found.";
}

$conn->close();
?>
