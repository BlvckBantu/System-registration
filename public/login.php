<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="form-container">
        <img src="../assets/logo.jpg" alt="Company Logo" class="logo">
        <h2>Login</h2>

        <?php if (isset($_SESSION['message'])) { echo "<p>{$_SESSION['message']}</p>"; unset($_SESSION['message']); } ?>
        
        <form action="process_login.php" method="post">
            <label>Phone Number:</label>
            <input type="text" name="phone" required><br>

            <label>Password:</label>
            <input type="password" name="password" required><br>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
