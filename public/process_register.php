<?php
session_start();
require __DIR__ . '/../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validate phone number (ensure it's numeric and correct length)
    if (!preg_match('/^254[7][0-9]{8}$/', $phone)) {
        $_SESSION['message'] = "Invalid phone number! Must start with 2547 and be 12 digits.";
        header("Location: register.php");
        exit();
    }

    // Validate passwords
    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if phone number already exists
        $check_stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $check_stmt->execute([$phone]);

        if ($check_stmt->rowCount() > 0) {
            $_SESSION['message'] = "Phone number already registered!";
            header("Location: register.php");
            exit();
        }

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, phone, password, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$username, $phone, $hashed_password]);

        // Store phone number in session for payment tracking
        $_SESSION['phone'] = $phone;
        $_SESSION['message'] = "Registration successful! Please complete payment.";
        
        header("Location: mpesa.php"); // Redirect to payment page
        exit();
    } catch (PDOException $e) {
        $_SESSION['message'] = "Registration failed: " . $e->getMessage();
        header("Location: register.php");
        exit();
    }
}
?>
