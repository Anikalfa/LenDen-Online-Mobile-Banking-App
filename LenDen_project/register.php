<?php
include 'config/db.php'; // Ensure this file connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $nid = $_POST['nid']; // Added NID
    $dob = $_POST['dob']; // Added Date of Birth
    $address = $_POST['address']; // Added Address
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password for storage

    // Handle file upload (profile picture)
    $profile_picture = '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];

        // Check if the file is of the allowed type
        if (in_array($file_type, $allowed_types)) {
            // Check file size (max 2MB)
            if ($file_size <= 2 * 1024 * 1024) {
                // Generate a unique filename for the image
                $file_name = uniqid('profile_') . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                $target_dir = 'uploads/';  // Ensure the 'uploads' folder exists
                $target_file = $target_dir . $file_name;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                    $profile_picture = $file_name; // Save the filename in the database
                } else {
                    echo "Error: Failed to upload image.";
                    exit();
                }
            } else {
                echo "Error: File size exceeds the limit of 2MB.";
                exit();
            }
        } else {
            echo "Error: Only JPG and PNG files are allowed.";
            exit();
        }
    }

    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = ? OR phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<p style='color:red;'>Email or Phone already registered!</p>";
    } else {
        // Proceed with registration
        if (empty($profile_picture)) {
            $profile_picture = NULL; // Set photo as NULL if no image uploaded
        }

        $sql = "INSERT INTO users (name, email, phone, nid, dob, address, password, photo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $name, $email, $phone, $nid, $dob, $address, $password, $profile_picture);
        
        if ($stmt->execute()) {
            // Redirect to login page after successful registration
            header("Location: login.php");
            exit(); // Make sure to call exit() after the header redirection
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            width: 500px;  /* Set the width to 800px */
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1); /* Restoring the previous box shadow */
            text-align: center;
        }

        .auth-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
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

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .input-group textarea {
            resize: vertical;
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

        .forgot-password-link a {
            color: #4f6d7a;
            font-size: 14px;
            text-decoration: none;
        }

        .forgot-password-link a:hover {
            text-decoration: underline;
        }

        p {
            margin-top: 15px;
        }

        p a {
            color: #4f6d7a;
            font-weight: bold;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <h2><i class="fas fa-user-plus"></i> Register</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="post" enctype="multipart/form-data">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-group">
                <i class="fas fa-phone"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fas fa-id-card"></i>
                <input type="text" name="nid" placeholder="NID Number" required>
            </div> 
            <div class="input-group">
                <i class="fas fa-calendar"></i>
                <input type="date" name="dob" required>
            </div> 
            <div class="input-group">
                <i class="fas fa-map-marker"></i>
                <textarea name="address" placeholder="Enter your address" required></textarea>
            </div>
            <div class="input-group">
                <i class="fas fa-image"></i>
                <input type="file" name="profile_picture" accept="image/jpeg, image/png" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

</body>
</html>
