<?php
session_start();
include 'config/db.php'; // Ensure this file correctly connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']); // Trim spaces
    $password = $_POST['password'];

    // Check if email exists in the database
    $sql = "SELECT id, name, password, balance FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Query failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // If user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password, $balance);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Store user info in session
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['balance'] = $balance; // ✅ Store balance

            header("Location: dashboard.php");
            exit();
        } else {
            echo "<p style='color:red;'>Invalid password!</p>";
        }
    } else {
        echo "<p style='color:red;'>Email not found!</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-image: url('images/cover.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .auth-container {
            width: 400px;  /* Adjust the width as needed */
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(255, 255, 255, 0.8); /* White box shadow */
            text-align: center;
        }

        .auth-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: black; /* Change the "Login" text color to black */
        }

        .input-group {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-group i {
            margin-right: 10px;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 20px;
            background-color: #4f6d7a;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #365e6c;
        }

        .forgot-password-link a,
        p a {
            color: black; /* Change all the link colors to black */
            font-size: 14px;
            text-decoration: none;
        }

        .forgot-password-link a:hover,
        p a:hover {
            text-decoration: underline;
        }

        p {
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="post">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <!-- Forgot/Reset Password Link -->
        <div class="forgot-password-link">
            <a href="reset_password.php">Forgot/Reset Password?</a>
        </div>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>

</body>
</html>
