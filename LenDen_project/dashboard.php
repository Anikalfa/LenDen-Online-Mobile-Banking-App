<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT name, balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_name = htmlspecialchars($row['name']);
    $balance = number_format($row['balance'], 2);
} else {
    echo "Error: User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard</title>
<style>
  /* Reset & base */
  * {
    box-sizing: border-box;
  }
  body {
    margin: 0; 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fff;
    color: #222;
    line-height: 1.5;
  }

  /* Navbar */
  .navbar {
    background-color: #001c64;
    color: #fff;
    padding: 10px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    width: 100%;
    top: 0; left: 0; right: 0;
    box-shadow: 0 2px 8px rgb(0 0 0 / 0.2);
    z-index: 1000;
    border-radius: 25px 25px;
  }
  .navbar .logo {
    font-weight: 700;
    font-size: 1.4rem;
    letter-spacing: 2px;
  }
  .navbar nav a {
    color: #fff;
    margin: 0 16px;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
  }
  .navbar nav a:hover {
    color: #f0a500;
  }
  .navbar-actions {
    display: flex;
    align-items: center;
    gap: 15px;
  }
  .user-name {
    font-weight: 600;
    font-size: 0.9rem;
  }
  .logout-btn {
    padding: 6px 16px;
    background-color: transparent;
    border: 2px solid #fff;
    border-radius: 6px;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none; /* Remove underline */
  }
  .logout-btn:hover {
    background-color: #f0a500;
    border-color: #f0a500;
    color: #001c64;
  }

  /* Carousel */
  .carousel-container {
    position: relative;
    max-width: 1200px;
    height: 420px;
    margin: 90px auto 30px;
    overflow: hidden;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgb(0 0 0 / 0.15);
  }
  .carousel-slide {
    display: none;
    width: 100%;
    height: 100%;
  }
  .carousel-slide.active {
    display: block;
    animation: fadeIn 1s ease;
  }
  .carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 15px;
  }
  @keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1;}
  }
  .carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0,0,0,0.45);
    border: none;
    color: white;
    font-size: 24px;
    padding: 12px 16px;
    border-radius: 50%;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.3s ease;
    z-index: 10;
  }
  .carousel-btn:hover {
    background-color: rgba(0,0,0,0.7);
  }
  .carousel-btn.prev {
    left: 20px;
  }
  .carousel-btn.next {
    right: 20px;
  }

  /* Section title */
  .service-title {
    max-width: 1200px;
    margin: 0 auto 25px;
    font-size: 2rem;
    font-weight: 700;
    color: #001c64;
    text-align: center;
    letter-spacing: 1px;
  }

  /* Shortcuts grid */
  .shortcuts-grid {
    max-width: 1200px;
    margin: 0 auto 50px;
    padding: 0 15px;
    display: flex;
    overflow-x: auto;
    gap: 20px;
    scrollbar-width: thin;
  }
  .shortcuts-grid::-webkit-scrollbar {
    height: 8px;
  }
  .shortcuts-grid::-webkit-scrollbar-thumb {
    background-color: #001c64;
    border-radius: 10px;
  }
  .shortcut-item {
    min-width: 120px;
    background: #f9f9f9;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgb(0 0 0 / 0.08);
    text-align: center;
    padding: 20px 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    flex-shrink: 0;
    text-decoration: none; /* Remove underline */
    color: #222;
  }
  .shortcut-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgb(0 0 0 / 0.15);
  }
  .shortcut-item img {
    width: 48px;
    height: 48px;
  }
  .shortcut-item p {
    margin-top: 12px;
    font-weight: 600;
    color: #222;
  }

  /* Security Info */
  .security-info {
    max-width: 1200px;
    margin: 0 auto 50px;
    padding: 30px 15px;
    background-color: #e8f0fe;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 6px 18px rgb(0 0 0 / 0.1);
  }
  .security-info h3 {
    font-size: 2.2rem;
    color: #001c64;
    margin-bottom: 12px;
    font-weight: 700;
  }
  .security-info p {
    color: #444;
    font-size: 1rem;
    max-width: 600px;
    margin: 0 auto 20px;
  }
  .more-info-btn {
    padding: 12px 26px;
    background-color: #001c64;
    border: none;
    color: white;
    font-size: 1rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
    text-decoration: none; /* Remove underline */
  }
  .more-info-btn:hover {
    background-color: #0040ff;
  }
  .security-info img {
    margin-top: 25px;
    max-width: 100%;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgb(0 0 0 / 0.12);
  }

  /* Download App Section */
  .download-app-section {
    max-width: 1200px;
    margin: 0 auto 80px;
    padding: 0 15px;
    text-align: center;
  }
  .download-app-section h1 {
    font-size: 2.8rem;
    font-weight: 800;
    color: #001c64;
    line-height: 1.1;
    margin: 10px 0;
  }
  .download-app-section p {
    font-size: 1.1rem;
    color: #555;
    margin-top: 15px;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
  }
  .download-app-section button {
    margin-top: 28px;
    padding: 16px 42px;
    font-size: 1.2rem;
    font-weight: 700;
    background-color: #001c64;
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    box-shadow: 0 6px 15px rgb(0 28 100 / 0.5);
    text-decoration: none; /* Remove underline */
  }
  .download-app-section button:hover {
    background-color: #0040ff;
    box-shadow: 0 8px 20px rgb(0 28 100 / 0.7);
  }

  /* Enhanced Footer */
  .footer {
    background: linear-gradient(135deg, #001435, #002a7f);
    padding: 50px 40px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    gap: 60px;
    border-radius: 20px 20px 0 0;
    box-shadow: 0 -4px 15px rgb(0 0 0 / 0.3);
    flex-wrap: nowrap;
  }
  .footer-links-container {
    display: flex;
    gap: 60px;
    flex: 1;
  }
  .footer-links {
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-width: 140px;
    flex: 1;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 1.05rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    transition: color 0.3s ease;
  }
  .footer-links.left {
    text-align: left;
    align-items: flex-start;
  }
  .footer-links.center {
    text-align: center;
    align-items: center;
  }
  .footer-links.right {
    text-align: right;
    align-items: flex-end;
  }
  .footer-links a {
    color: #d0d0d0;
    text-decoration: none;
    transition: color 0.25s ease;
    padding: 6px 0;
    border-radius: 6px;
  }
  .footer-links a:hover {
    color: #f0a500;
    background-color: rgba(240, 165, 0, 0.15);
    padding-left: 8px;
    box-shadow: inset 3px 0 0 0 #f0a500;
  }
  .footer-bottom {
    background: linear-gradient(135deg, #001435, #002a7f);
    color: #bbb;
    text-align: center;
    padding: 18px 40px;
    font-size: 0.95rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    border-radius: 0 0 20px 20px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.1);
    user-select: none;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .navbar nav {
      display: none;
    }
    .shortcuts-grid {
      justify-content: flex-start;
    }
    .footer {
      flex-direction: column;
      align-items: center;
      gap: 30px;
      padding: 40px 20px 30px;
    }
    .footer-links-container {
      flex-direction: column;
      gap: 30px;
      width: 100%;
      align-items: center;
      justify-content: center;
    }
    .footer-links {
      min-width: auto;
      flex: unset;
      text-align: center !important;
      align-items: center !important;
    }
    .footer-bottom {
      font-size: 0.9rem;
      padding: 15px 20px;
    }
  }
</style>
</head>
<body>

<header class="navbar">
  <div class="logo" id="logo">LenDen</div>
  <nav>
    <a href="#" id="services-link">Services</a>
    <a href="#" id="business-link">Business</a>
    <a href="#" id="help-link">Help</a>
    <a href="#" id="about-link">About</a>
  </nav>
  <div class="navbar-actions">
    <div class="user-name"><?php echo $user_name; ?></div>
    <form action="logout.php" method="POST" style="display:inline;">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </div>
</header>

<div class="carousel-container" aria-label="Image slideshow">
  <div class="carousel-slide active">
    <img src="images/pic1.jpg" alt="LenDen service preview slide 1" />
  </div>
  <div class="carousel-slide">
    <img src="images/pic4.jpg" alt="LenDen service preview slide 2" />
  </div>
  <div class="carousel-slide">
    <img src="images/pic5.jpg" alt="LenDen service preview slide 3" />
  </div>
  <button class="carousel-btn prev" onclick="changeSlide(-1)" aria-label="Previous Slide">&#10094;</button>
  <button class="carousel-btn next" onclick="changeSlide(1)" aria-label="Next Slide">&#10095;</button>
</div>

<div class="service-title">Select a Service</div>

<div class="shortcuts-grid" role="list">
  <a href="send_money.php" class="shortcut-item" role="listitem" aria-label="Send Money">
    <img src="icons/ii1.webp" alt="" />
    <p>Send Money</p>
  </a>

  <a href="mobile_recharge.php" class="shortcut-item" role="listitem" aria-label="Mobile Recharge">
    <img src="icons/ii2.webp" alt="" />
    <p>Mobile Recharge</p>
  </a>

  <a href="add_money_button.php" class="shortcut-item" role="listitem" aria-label="Add Money">
    <img src="icons/ii3.webp" alt="" />
    <p>Add Money</p>
  </a>

  <a href="payment.php" class="shortcut-item" role="listitem" aria-label="Payment">
    <img src="icons/ii4.webp" alt="" />
    <p>Payment</p>
  </a>

  <a href="pay_bill.php" class="shortcut-item" role="listitem" aria-label="Pay Bill">
    <img src="icons/ii5.webp" alt="" />
    <p>Pay Bill</p>
  </a>

  <a href="bkash_to_bank.php" class="shortcut-item" role="listitem" aria-label="LenDen to Bank">
    <img src="icons/ii6.webp" alt="" />
    <p>LenDen to Bank</p>
  </a>

  <a href="cash_out.php" class="shortcut-item" role="listitem" aria-label="Cash Out">
    <img src="icons/ii7.webp" alt="" />
    <p>Cash Out</p>
  </a>

  <a href="transaction_history.php" class="shortcut-item" role="listitem" aria-label="Transactions">
    <img src="icons/ii8.webp" alt="" />
    <p>Transactions</p>
  </a>
</div>

<div class="security-info">
  <h3>Safe, Private, Secure</h3>
  <p>Your data is encrypted, keeping your sensitive financial info safe.</p>
  <button class="more-info-btn" onclick="alert('More security info coming soon!')">More About Security</button>
  <img src="images/payban.webp" alt="Security encryption illustration" />
</div>

<div class="download-app-section">
  <h1>Join the millions around</h1>
  <h1>the world who love LenDen</h1>
  <p>Download the app on your phone or sign up for free online.</p>
  <button onclick="alert('Redirecting to app download page...')">Download App</button>
</div>

<footer class="footer" role="contentinfo">
  <div class="footer-links-container">
    <div class="footer-links left" aria-label="Service links">
      <a href="#">Service</a>
      <a href="#">Campaigns</a>
      <a href="#">Company</a>
      <a href="#">About</a>
    </div>
    <div class="footer-links center" aria-label="Contact links">
      <a href="#">Contact Us</a>
      <a href="#">Career</a>
      <a href="#">Business</a>
      <a href="#">Digital Payroll</a>
    </div>
    <div class="footer-links right" aria-label="Other links">
      <a href="#">Others</a>
      <a href="#">Terms</a>
      <a href="#">FAQ</a>
      <a href="#">Security Tips</a>
    </div>
  </div>
</footer>

<div class="footer-bottom">
  &copy; 2025 Sakil. All Rights Reserved. &nbsp;&nbsp;&nbsp; Powered by Sakil
</div>

<script>
  let slideIndex = 0;
  const slides = document.querySelectorAll('.carousel-slide');
  const totalSlides = slides.length;

  function showSlides() {
    slides.forEach(s => s.classList.remove('active'));
    slideIndex++;
    if (slideIndex > totalSlides) slideIndex = 1;
    slides[slideIndex - 1].classList.add('active');
    setTimeout(showSlides, 3000);
  }

  function changeSlide(direction) {
    slideIndex += direction;
    if (slideIndex > totalSlides) slideIndex = 1;
    if (slideIndex < 1) slideIndex = totalSlides;
    slides.forEach(s => s.classList.remove('active'));
    slides[slideIndex - 1].classList.add('active');
  }

  showSlides();
</script>

</body>
</html>
