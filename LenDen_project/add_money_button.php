<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please log in to add money.";
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

// Fetch banks for dropdown
$sql_banks = "SELECT * FROM banks";
$result_banks = $conn->query($sql_banks);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $transaction_id = uniqid("txn_");

    if ($payment_method == "bank") {
        $bank_account = $_POST['bank_account'];
        $bank_pin = $_POST['bank_pin'];
        $bank_select = $_POST['bank_select'];

        if (empty($bank_account) || empty($bank_pin) || empty($bank_select)) {
            echo "<p style='color:red; text-align:center;'>Bank account, PIN, and bank selection are required!</p>";
            exit();
        }
    } elseif ($payment_method == "card") {
        $card_number = $_POST['card_number'];
        $cvv = $_POST['cvv'];

        if (empty($card_number) || empty($cvv)) {
            echo "<p style='color:red; text-align:center;'>Card number and CVV are required!</p>";
            exit();
        }
    }

    $sql = "INSERT INTO add_money (user_id, amount, payment_method, transaction_id, status) 
            VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $user_id, $amount, $payment_method, $transaction_id);

    if ($stmt->execute()) {
        $conn->query("UPDATE users SET balance = balance + $amount WHERE id = $user_id");

        $balance_result = $conn->query("SELECT balance FROM users WHERE id = $user_id");
        $row = $balance_result->fetch_assoc();
        $new_balance = $row['balance'];

        $receiver_id = NULL;
        $transaction_type = "add_money";
        $description = "Added money via $payment_method";

        $transaction_sql = "INSERT INTO transactions (sender_id, receiver_id, amount, type, description, transaction_id) 
                            VALUES (?, ?, ?, ?, ?, ?)";
        $transaction_stmt = $conn->prepare($transaction_sql);
        $transaction_stmt->bind_param("iidsss", $user_id, $receiver_id, $amount, $transaction_type, $description, $transaction_id);

        if ($transaction_stmt->execute()) {
            echo "<p style='color:green; text-align:center; font-weight:bold; font-size:18px;'>Money added successfully! Your new balance is: ৳" . number_format($new_balance, 2) . "</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error inserting transaction record: " . $transaction_stmt->error . "</p>";
        }
    } else {
        echo "<p style='color:red; text-align:center;'>Error: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add Money</title>
<style>
  body {
    margin: 0; padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #e0f0ff, #ffffff);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  /* Navbar Styles */
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
    cursor: pointer;
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

  /* Container below navbar */
  #add-money-container {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
  }
  #add-money-container h2 {
    font-size: 36px;
    color: #001c64;
    margin-bottom: 30px;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
  }

  /* Balance box */
  #balance-info {
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

  /* Form styles */
  form {
    background: rgba(255, 255, 255, 0.8);
    padding: 45px 50px;
    border-radius: 20px;
    border: 1px solid #cce0ff;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    text-align: left;
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
    transition: border-color 0.3s ease;
    font-weight: 500;
  }
  form input:focus,
  form select:focus {
    outline: none;
    border-color: #0077ff;
    box-shadow: 0 0 10px rgba(0, 119, 255, 0.4);
  }

  /* Center & enlarge Add Money button */
  form button[type="submit"] {
    width: 50%;
    display: block;
    margin: 20px auto;
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

  /* Go back box */
  #go-back-box {
    width: 280px;
    margin: 35px auto 0 auto;
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
  }

  /* Current offer */
  #current-offer {
    font-size: 30px;
    font-weight: 900;
    color: #001c64;
    margin-top: 40px;
    margin-bottom: 50px;
    position: relative;
    text-align: center;
  }
  #current-offer::after {
    content: '';
    width: 130px;
    height: 5px;
    background-color: #ffdd00;
    display: block;
    margin: 8px auto 0 auto;
    border-radius: 3px;
    box-shadow: 0 0 10px #ffdd00;
  }

  /* Footer */
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

  /* Video container */
  #video-container {
    display: flex;
    justify-content: center;
    margin-top: 40px;
    gap: 20px;
    flex-wrap: wrap;
  }
  #video-container iframe {
    width: 48%;
    min-width: 320px;
    height: 315px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
  }

  /* Responsive */
  @media (max-width: 700px) {
    #video-container iframe {
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
    #add-money-container h2 {
      font-size: 28px;
    }
    #balance-info {
      font-size: 16px;
      padding: 14px 28px;
    }
  }
</style>
</head>
<body>

<header class="navbar">
  <div class="logo">LenDen</div>
  <nav>
    <a href="dashboard.php" id="services-link">Services</a>
    <a href="business.php" id="business-link">Business</a>
    <a href="help.php" id="help-link">Help</a>
    <a href="about.php" id="about-link">About</a>
  </nav>
</header>

<div id="add-money-image"></div>

<div id="add-money-container">
  <h2>Add Money</h2>
  <div id="balance-info">Your current balance: ৳<?php echo $current_balance; ?></div>

  <form method="post">
    <label for="amount">Amount to Add:</label>
    <input type="number" name="amount" id="amount" required />

    <label for="payment_method">Payment Method:</label>
    <select name="payment_method" id="payment_method" required onchange="togglePaymentFields()">
      <option value="bank">Bank</option>
      <option value="card">Card</option>
    </select>

    <div id="bank_fields">
      <label for="bank_account">Bank Account Number:</label>
      <input type="text" name="bank_account" id="bank_account" />

      <label for="bank_pin">Bank PIN:</label>
      <input type="password" name="bank_pin" id="bank_pin" />

      <label for="bank_select">Select Bank:</label>
      <select name="bank_select" id="bank_select" required>
        <?php
        if ($result_banks->num_rows > 0) {
            while($row = $result_banks->fetch_assoc()) {
                echo "<option value='".$row['id']."'>".$row['bank_name']."</option>";
            }
        }
        ?>
      </select>
    </div>

    <div id="card_fields" style="display:none;">
      <label for="card_number">Card Number:</label>
      <input type="text" name="card_number" id="card_number" />

      <label for="cvv">CVV:</label>
      <input type="password" name="cvv" id="cvv" />
    </div>

    <button type="submit">Add Money</button>
  </form>

  <div id="go-back-box">
    <a href="dashboard.php">Go Back to Dashboard</a>
  </div>

  <div id="current-offer">Current Offer</div>
</div>

<div id="video-container">
  <iframe src="https://www.youtube.com/embed/rVeZTR7v9rs?list=PLipzXrhhkPjmG9FWb5edXkAmAc4WQIDCz" frameborder="0" allowfullscreen></iframe>
  <iframe src="https://www.youtube.com/embed/pcC7P2HKTaI?list=PLipzXrhhkPjmG9FWb5edXkAmAc4WQIDCz&index=2" frameborder="0" allowfullscreen></iframe>
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

<script>
  function togglePaymentFields() {
    var method = document.getElementById("payment_method").value;
    document.getElementById("bank_fields").style.display = (method === "bank") ? "block" : "none";
    document.getElementById("card_fields").style.display = (method === "card") ? "block" : "none";
  }
</script>

</body>
</html>