<?php
$host = "localhost";
$dbname = "mpesa_db";
$username = "root"; // Default XAMPP MySQL user
$password = ""; // Default XAMPP MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
