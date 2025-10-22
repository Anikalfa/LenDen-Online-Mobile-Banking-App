<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // If logged in, redirect to dashboard
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>bKash Clone - Home</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Ensure this path is correct -->
    <style>
        body {
            background-image: url('images/cover.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            flex-direction: column; /* Align container to the bottom */
            justify-content: flex-end; /* Push content to the bottom */
            align-items: center; /* Center content horizontally */
            height: 100vh; /* Full height of the viewport */
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 800px;  /* Increased width to 800px */
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 4px 15px rgba(255, 255, 255, 0.8); /* White box shadow */
            margin-bottom: 30px; /* Space from the bottom */
        }

        .container h1 {
            color: #da2c67; /* Color changed to #da2c67 */
            margin-bottom: 20px;
        }

        .container p {
            color: #da2c67; /* Color changed to #da2c67 */
            margin-bottom: 20px;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .btn {
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-btn {
            background-color: #4f6d7a;
            color: white;
        }

        .register-btn {
            background-color: #da68a0;
            color: white;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Welcome to LenDen</h1>
        <p>A financial transaction system.</p>
        <div class="buttons">
            <a href="login.php" class="btn login-btn">Login</a>
            <a href="register.php" class="btn register-btn">Register</a>
        </div>
    </div>

</body>
</html>
