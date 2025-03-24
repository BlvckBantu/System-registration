<?php
session_start();
require __DIR__ . '/../config/database.php';

$data = file_get_contents("php://input");
file_put_contents("mpesa_callback.log", $data, FILE_APPEND); // Log the response for debugging

$mpesaResponse = json_decode($data, true);

if ($mpesaResponse && isset($mpesaResponse["Body"]["stkCallback"]["ResultCode"])) {
    $resultCode = $mpesaResponse["Body"]["stkCallback"]["ResultCode"];
    
    // Check if payment was successful (ResultCode 0 means success)
    if ($resultCode == 0) {
        $metadata = $mpesaResponse["Body"]["stkCallback"]["CallbackMetadata"]["Item"];
        
        // Extract payment details
        $amount = $metadata[0]["Value"] ?? null;
        $mpesaReceiptNumber = $metadata[1]["Value"] ?? null;
        $phone = $metadata[4]["Value"] ?? null;

        if ($amount == 1500 && !empty($phone) && !empty($mpesaReceiptNumber)) {
            // Update user status to 'active' and store M-Pesa receipt number
            $stmt = $conn->prepare("UPDATE users SET status = 'active', mpesa_receipt = ? WHERE phone = ?");
            $stmt->bind_param("ss", $mpesaReceiptNumber, $phone);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Payment successful! You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['message'] = "Payment validation failed. Contact support.";
            header("Location: register.php");
            exit();
        }
    } else {
        // Payment failed (store in logs)
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
