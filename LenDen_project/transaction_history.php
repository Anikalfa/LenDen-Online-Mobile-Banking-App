<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: Please log in to view your transaction history.");
}

$user_id = $_SESSION['user_id'];

// Retrieve transactions for the logged-in user
$sql = "SELECT id, type, COALESCE(description, 'N/A') AS description, amount, date 
        FROM transactions 
        WHERE sender_id = ? OR receiver_id = ? 
        ORDER BY date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Transaction History</title>
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
  .transaction-history-container {
    padding: 30px 20px 20px 20px;
    max-width: 900px;
    margin: 0 auto;
    text-align: center;
  }
  .transaction-history-container h2 {
    font-size: 36px;
    color: #001c64;
    margin-bottom: 30px;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 16px;
  }
  th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
  }
  th {
    background-color: #f4f4f4;
    color: #333;
  }
  tr:nth-child(even) {
    background-color: #f9f9f9;
  }
  .transaction-history-image {
    background-image: url('images/transaction.jpg');
    background-size: cover;
    background-position: center;
    height: 400px;
    width: 100%;
    margin-top: 50px;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }

  /* Updated Go to Dashboard link styling like Cash Out page */
  .go-dashboard-link {
    display: inline-block;
    margin-top: 20px;
    color: #001c64;
    font-weight: 700;
    text-decoration: none;
    width: 280px;
    background-color: #f7faff;
    border-radius: 14px;
    padding: 14px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    transition: background-color 0.3s ease;
  }
  .go-dashboard-link:hover {
    background-color: #d9e7ff;
    text-decoration: underline;
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
    .transaction-history-image {
      height: 200px;
    }
    table {
      font-size: 14px;
    }
    .navbar nav a {
      font-size: 12px;
    }
    .go-dashboard-link {
      width: 100%;
      text-align: center;
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

<div class="transaction-history-image"></div>

<div class="transaction-history-container">
  <h2>Transaction History</h2>
  <table>
    <thead>
      <tr>
        <th>Transaction ID</th>
        <th>Type</th>
        <th>Description</th>
        <th>Amount (৳)</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<tr>
                      <td>{$row['id']}</td>
                      <td>{$row['type']}</td>
                      <td>{$row['description']}</td>
                      <td>৳" . number_format($row['amount'], 2) . "</td>
                      <td>{$row['date']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='5'>No transactions found.</td></tr>";
      }
      ?>
    </tbody>
  </table>

  <a href="dashboard.php" class="go-dashboard-link">Go to Dashboard</a>
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
