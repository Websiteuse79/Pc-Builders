<?php
header('Content-Type: application/json');
require_once 'db.php'; // assumes db.php sets up $conn (mysqli)

$categories = [
    'CPU' => 'cpu',
    'GPU' => 'gpu',
    'Motherboard' => 'motherboards',
    'Power_Supply' => 'power_supplies',
    'RAM' => 'ram',
    'Storage' => 'storage',
    'Cabinet' => 'cabinets'
];

$result = [];
foreach ($categories as $key => $table) {
    $query = "SELECT * FROM `$table`";
    $res = $conn->query($query);
    $items = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }
    }
    $result[$key] = $items;
}

echo json_encode($result);
