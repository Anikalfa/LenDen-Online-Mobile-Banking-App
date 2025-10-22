<!-- reset_password.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="reset-password-container">
    <h2>Reset Password</h2>
    <form method="POST" action="reset_password_action.php">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <button type="submit">Reset Password</button>
    </form>
    <a href="login.php">Go back to Login</a>
</div>

</body>
</html>
