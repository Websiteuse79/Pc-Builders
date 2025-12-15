<?php
function getData() {
    
    $data_file = __DIR__ . '../data.json';
    if (!file_exists($data_file)) {
        return ['products' => [], 'orders' => []];
    }
    return json_decode(file_get_contents($data_file), true);
}

function saveData($data) {
    $data_file = __DIR__ . '../data.json';
    file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
}

// Sample initial data if file doesn't exist
if (!file_exists(__DIR__ . '../data.json')) {
    $initial_data = [
        'products' => [
            [
                'id' => 'prod1',
                'name' => 'Gaming Pro',
                'description' => 'High-performance gaming PC for competitive players',
                'price' => 1999.99,
                'category' => 'prebuild',
                'image' => 'assets/images/gaming-pc.jpg',
                'specs' => ['Intel i7-12700K', 'RTX 3080', '32GB DDR5', '1TB NVMe SSD']
            ],
            [
                'id' => 'prod2',
                'name' => 'Intel Core i9-13900K',
                'description' => '24 cores (8P + 16E), up to 5.8GHz',
                'price' => 589.99,
                'category' => 'processor',
                'image' => 'assets/images/default-component.jpg',
                'specs' => ['24 cores', '5.8GHz boost', 'LGA1700 socket']
            ]
        ],
        'orders' => [
            [
                'id' => 'ord1',
                'customer_name' => 'John Doe',
                'date' => '2023-06-15',
                'items' => [
                    [
                        'name' => 'Gaming Pro',
                        'price' => 1999.99,
                        'quantity' => 1
                    ]
                ],
                'total' => 1999.99,
                'status' => 'Processing'
            ]
        ]
    ];
    
    saveData($initial_data);
}
?>