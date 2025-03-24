<?php
session_start();
require __DIR__ . '/../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST["phone"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        if ($user['status'] === 'active') {
            $_SESSION["user"] = $user;
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['message'] = "Please complete the M-Pesa payment.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Invalid credentials!";
        header("Location: login.php");
        exit();
    }
}
?>
