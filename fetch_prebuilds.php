<?php
// fetch_prebuilds.php
header('Content-Type: application/json');

// Database connection (update with your DB credentials)
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'pc-builder'; // Change to your actual DB name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Table name with space, so use backticks
$sql = "SELECT id, Name, CPU, GPU, RAM, Storage, Motherboard, Cooler, `Power Supply`, `Case`, Price, Image FROM `pre_build`";
$result = $conn->query($sql);


$prebuilds = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Combine all specs into a specs array for frontend use

        $row['specs'] = [
            'CPU: ' . $row['CPU'],
            'GPU: ' . $row['GPU'],
            'RAM: ' . $row['RAM'],
            'Storage: ' . $row['Storage'],
            'Motherboard: ' . $row['Motherboard'],
            'Cooler: ' . $row['Cooler'],
            'Power Supply: ' . $row['Power Supply'],
            'Case: ' . $row['Case']
        ];
        $row['name'] = $row['Name'];
      
        $row['price'] = $row['Price'];  $row['image'] = $row['Image'] ? 'assets/images/' . $row['Image'] : 'assets/images/gaming-pc.png';
        $prebuilds[] = $row;
    }
}

$conn->close();
echo json_encode($prebuilds);
