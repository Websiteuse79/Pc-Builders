<?php
 $conn = new mysqli("localhost", "root", "", "pc-builder");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
    'Connection successful';
}
?>