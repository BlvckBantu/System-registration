<?php
session_start();
require __DIR__ . '/../config/database.php';

$data = file_get_contents("php://input");
file_put_contents("mpesa_callback.log", $data . PHP_EOL, FILE_APPEND); // Log response for debugging

$mpesaResponse = json_decode($data, true);

if ($mpesaResponse && isset($mpesaResponse["Body"]["stkCallback"]["ResultCode"])) {
    $resultCode = $mpesaResponse["Body"]["stkCallback"]["ResultCode"];

    // Check if payment was successful (ResultCode 0 means success)
    if ($resultCode == 0) {
        $metadata = $mpesaResponse["Body"]["stkCallback"]["CallbackMetadata"]["Item"];
        
        // Extract payment details
        $amount = null;
        $mpesaReceiptNumber = null;
        $phone = null;

        foreach ($metadata as $item) {
            if ($item["Name"] == "Amount") {
                $amount = $item["Value"];
            }
            if ($item["Name"] == "MpesaReceiptNumber") {
                $mpesaReceiptNumber = $item["Value"];
            }
            if ($item["Name"] == "PhoneNumber") {
                $phone = $item["Value"];
            }
        }

        // Ensure correct amount and valid phone number
        if ($amount == 1500 && !empty($phone) && !empty($mpesaReceiptNumber)) {
            // Convert phone number to standard format (Safaricom returns 2547XXXXXXXX)
            if (substr($phone, 0, 3) == "254") {
                $phone = "0" . substr($phone, 3); // Convert to 07XXXXXXXX format
            }

            // Update user status to 'active' and store M-Pesa receipt number
            $stmt = $pdo->prepare("UPDATE users SET status = 'active', mpesa_receipt = ? WHERE phone = ?");
            $stmt->execute([$mpesaReceiptNumber, $phone]);

            $_SESSION['message'] = "Payment successful! You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['message'] = "Payment validation failed. Please contact support.";
            header("Location: register.php");
            exit();
        }
    } else {
        // Payment failed (log it for debugging)
        file_put_contents("mpesa_callback_error.log", json_encode($mpesaResponse, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);

        $_SESSION['message'] = "Payment was not completed. Please try again.";
        header("Location: register.php");
        exit();
    }
} else {
    $_SESSION['message'] = "Invalid M-Pesa response.";
    header("Location: register.php");
    exit();
}
?>
