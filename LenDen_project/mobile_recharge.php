<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Error: Unauthorized access!";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the current user's balance
$sql_balance = "SELECT balance FROM users WHERE id = ?";
$stmt_balance = $conn->prepare($sql_balance);
$stmt_balance->bind_param("i", $user_id);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();
$current_balance = 0;

if ($result_balance->num_rows > 0) {
    $row_balance = $result_balance->fetch_assoc();
    $current_balance = number_format($row_balance['balance'], 2);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_number = $_POST['phone_number'];
    $amount = floatval($_POST['amount']);
    $operator = $_POST['operator'];

    // Check if the user has enough balance
    $userQuery = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows === 0) {
        echo "Error: User not found!";
        exit();
    }

    $userData = $userResult->fetch_assoc();
    $user_balance = $userData['balance'];

    if ($user_balance < $amount) {
        echo "Error: Insufficient balance!";
        exit();
    }

    // Deduct amount and update balance
    $new_balance = $user_balance - $amount;
    $updateBalanceQuery = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $updateBalanceQuery->bind_param("di", $new_balance, $user_id);
    $updateBalanceQuery->execute();

    // Insert recharge record
    $insertRechargeQuery = $conn->prepare("INSERT INTO mobile_recharge (user_id, phone_number, amount, operator) VALUES (?, ?, ?, ?)");
    $insertRechargeQuery->bind_param("isss", $user_id, $phone_number, $amount, $operator);
    $insertRechargeQuery->execute();

    // Transaction record
    $transaction_id = uniqid("txn_");
    $insertTransactionQuery = $conn->prepare("INSERT INTO transactions (transaction_id, sender_id, receiver_id, amount, type, description) VALUES (?, ?, ?, ?, 'recharge', ?)");
    $insertTransactionQuery->bind_param("siids", $transaction_id, $user_id, $user_id, $amount, $operator);
    $insertTransactionQuery->execute();

    echo "Mobile recharge successful! Your new balance is: ৳" . number_format($new_balance, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mobile Recharge</title>
<style>
  body {
    margin: 0; padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #e0f0ff, #ffffff);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
  .navbar {
    background-color: #001c64;
    color: white;
    padding: 5px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 25px;
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
  }
  .navbar .logo {
    font-size: 18px;
    font-weight: bold;
    user-select: none;
  }
  .navbar nav a {
    color: white;
    margin: 0 8px;
    font-size: 13px;
    text-decoration: none;
    transition: color 0.3s ease;
  }
  .navbar nav a:hover {
    color: #ffdd00;
  }
  .recharge-container {
    padding: 60px 20px 20px 20px; /* Reduced top padding from 100px to 60px */
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
  }
  .recharge-container h2 {
    font-size: 36px;
    color: #001c64;
    margin-bottom: 30px;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
  }
  .balance-info {
    font-size: 20px;
    color: #222;
    margin-bottom: 30px;
    background-color: #f9f9f9;
    border-radius: 60px;
    padding: 18px 40px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    display: inline-block;
    font-weight: 600;
  }
  form {
    background: rgba(255, 255, 255, 0.8);
    padding: 45px 50px;
    border-radius: 20px;
    /* border removed */
    box-shadow: inset 0 0 15px rgba(255,255,255,0.6), 0 15px 35px rgba(0,0,0,0.1);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    text-align: left;
    max-width: 500px;
    margin: 0 auto;
  }
  form label {
    font-weight: 600;
    color: #001c64;
    display: block;
    margin-bottom: 8px;
    font-size: 16px;
  }
  form input,
  form select {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 12px;
    border: 1.8px solid #c1d4ff;
    font-size: 15px;
    font-weight: 500;
    transition: border-color 0.3s ease;
  }
  form input:focus,
  form select:focus {
    outline: none;
    border-color: #0077ff;
    box-shadow: 0 0 10px rgba(0, 119, 255, 0.4);
  }
  form button[type="submit"] {
    width: 50%;
    display: block;
    margin: 20px auto 0 auto;
    padding: 15px;
    font-size: 20px;
    background-color: #001c64;
    border: 1.5px solid white;
    color: white;
    cursor: pointer;
    border-radius: 12px;
    transition: background-color 0.3s ease, color 0.3s ease;
  }
  form button[type="submit"]:hover {
    background-color: #ffdd00;
    color: #001c64;
    border-color: #ffdd00;
  }
  #go-back-box {
    width: 280px;
    margin: 20px auto 0 auto;
    background-color: #f7faff;
    border-radius: 14px;
    padding: 14px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    text-align: center;
    transition: background-color 0.3s ease;
  }
  #go-back-box:hover {
    background-color: #d9e7ff;
  }
  #go-back-box a {
    text-decoration: none;
    color: #001c64;
    font-weight: 700;
    font-size: 17px;
    letter-spacing: 0.04em;
    display: inline-block;
  }
  #go-back-box a:hover {
    text-decoration: underline;
  }
  .recharge-image {
    background-image: url('images/mobilere.jpg');
    background-size: cover;
    background-position: center;
    height: 400px;
    width: 100%;
    margin-top: 50px;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }
  .current-offer {
    font-size: 30px;
    font-weight: 900;
    color: #001c64;
    margin-top: 40px;
    margin-bottom: 50px;
    text-align: center;
    position: relative;
  }
  .current-offer::after {
    content: '';
    width: 130px;
    height: 5px;
    background-color: #ffdd00;
    display: block;
    margin: 8px auto 0 auto;
    border-radius: 3px;
    box-shadow: 0 0 10px #ffdd00;
  }
  .video-container {
    display: flex;
    justify-content: center;
    margin-top: 40px;
    gap: 20px;
    flex-wrap: wrap;
  }
  .video-container iframe {
    width: 48%;
    min-width: 320px;
    height: 315px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
  }
  footer {
    background-color: #001435;
    padding: 40px 20px;
    margin-top: 60px;
    color: white;
    width: 100%;
    box-sizing: border-box;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    border-radius: 15px 15px 0 0;
  }
  #footer-links-container {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    width: 100%;
    gap: 30px;
  }
  .footer-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 30%;
  }
  .footer-links a {
    color: white;
    text-decoration: none;
    font-size: 17px;
    font-weight: 500;
    transition: color 0.3s ease;
  }
  .footer-links a:hover {
    color: #ffdd00;
    text-decoration: underline;
  }
  #copyright {
    width: 100%;
    text-align: center;
    margin-top: 25px;
    font-size: 14px;
    letter-spacing: 0.04em;
    color: #cccccc;
  }
  @media (max-width: 700px) {
    .video-container iframe {
      width: 100%;
      height: 230px;
    }
    .footer-links {
      width: 100%;
      text-align: center;
    }
    form {
      padding: 30px 25px;
    }
  }
</style>
</head>
<body>
<header class="navbar">
  <div class="logo" id="logo">LenDen</div>
  <nav>
    <a href="dashboard.php" id="services-link">Services</a>
    <a href="business.php" id="business-link">Business</a>
    <a href="help.php" id="help-link">Help</a>
    <a href="about.php" id="about-link">About</a>
  </nav>
</header>

<div class="recharge-image"></div>

<div class="recharge-container">
  <h2>Mobile Recharge</h2>

  <div class="balance-info">
    Your current balance: ৳<?php echo $current_balance; ?>
  </div>

  <form method="post">
    <label for="phone_number">Recipient Phone Number:</label>
    <input type="text" name="phone_number" required>

    <label for="amount">Amount:</label>
    <input type="number" name="amount" required>

    <label for="operator">Select Operator:</label>
    <select name="operator" required>
      <option value="Airtel">Airtel</option>
      <option value="Robi">Robi</option>
      <option value="Grameenphone">Grameenphone</option>
      <option value="Banglalink">Banglalink</option>
    </select>

    <label for="pin">Enter Your PIN:</label>
    <input type="password" id="pin" name="pin" required>

    <button type="submit">Recharge</button>
  </form>

  <div id="go-back-box">
    <a href="dashboard.php">Go Back to Dashboard</a>
  </div>

  <div class="current-offer">Current Offer</div>
</div>

<div class="video-container">
  <iframe src="https://www.youtube.com/embed/kguEg9zKv0Y?list=PLipzXrhhkPjlqMqpKPmlsd4gHFGMm2LRb&index=2" frameborder="0" allowfullscreen></iframe>
  <iframe src="https://www.youtube.com/embed/kXEY9nV-fSk?list=PLipzXrhhkPjlqMqpKPmlsd4gHFGMm2LRb&index=4" frameborder="0" allowfullscreen></iframe>
</div>

<footer>
  <div id="footer-links-container">
    <div class="footer-links">
      <a href="#">Service</a>
      <a href="#">Campaigns</a>
      <a href="#">Company</a>
      <a href="#">About</a>
    </div>
    <div class="footer-links">
      <a href="#">Contact Us</a>
      <a href="#">Career</a>
      <a href="#">Business</a>
      <a href="#">Digital Payroll</a>
    </div>
    <div class="footer-links">
      <a href="#">Others</a>
      <a href="#">Terms</a>
      <a href="#">FAQ</a>
      <a href="#">Security Tips</a>
    </div>
  </div>
  <div id="copyright">&copy; 2025 Sakil. All Rights Reserved.</div>
</footer>
</body>
</html>
