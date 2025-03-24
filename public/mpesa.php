<?php
session_start();
require __DIR__ . '/../config/mpesa_config.php';

$phone = $_SESSION['phone'] ?? ''; // Ensure the user’s phone is available
$amount = 1500; 
$timestamp = date("YmdHis");
$password = base64_encode(SHORTCODE . PASSKEY . $timestamp);

// Get Access Token
$tokenUrl = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
$credentials = base64_encode(CONSUMER_KEY . ":" . CONSUMER_SECRET);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $tokenUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($curl);
curl_close($curl);
$token = json_decode($result)->access_token ?? null;

// Ensure token is valid
if (!$token) {
    die("Failed to generate access token.");
}

// STK Push Request
$stkPushUrl = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
$headers = ["Authorization: Bearer $token", "Content-Type: application/json"];

$callbackUrl = "https://yourdomain.com/public/callback.php"; // Update with your actual callback URL

$payload = [
    "BusinessShortCode" => SHORTCODE,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => SHORTCODE,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callbackUrl,
    "AccountReference" => "Mpesa Registration",
    "TransactionDesc" => "Register"
];

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $stkPushUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

// Check M-Pesa response
if (isset($data['ResponseCode']) && $data['ResponseCode'] == "0") {
    $_SESSION['mpesa_request_id'] = $data['CheckoutRequestID']; // Store request ID
    $_SESSION['message'] = "Payment request sent to your phone. Please complete the payment.";
    
    header("Location: login.php"); 
    exit();
} else {
    $_SESSION['message'] = "M-Pesa request failed: " . ($data['errorMessage'] ?? "Unknown error");
    
    header("Location: register.php");
    exit();
}
?>
