<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'add':
        // expects: name, price, quantity, image
        $item = [
            'name' => $_POST['name'],
            'price' => floatval($_POST['price']),
            'quantity' => intval($_POST['quantity']),
            'image' => isset($_POST['image']) ? $_POST['image'] : ''
        ];
        // If item exists, increase quantity
        $found = false;
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['name'] === $item['name']) {
                $cartItem['quantity'] += $item['quantity'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = $item;
        }
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        break;
    case 'update':
        // expects: name, quantity
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['name'] === $_POST['name']) {
                $cartItem['quantity'] = intval($_POST['quantity']);
                break;
            }
        }
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        break;
    case 'remove':
        // expects: name
        $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) {
            return $item['name'] !== $_POST['name'];
        }));
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        break;
    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true, 'cart' => []]);
        break;
    case 'get':
    default:
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        break;
}
