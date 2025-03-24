<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION["user"]["username"]; ?>!</h2>
    <p>Your phone number: <?php echo $_SESSION["user"]["phone"]; ?></p>
    <p>Status: <?php echo $_SESSION["user"]["status"]; ?></p>

    <a href="logout.php" class="logout-btn">Logout</a>
</body>
</html>
