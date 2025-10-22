<?php
$host = 'localhost'; // or your server's IP address
$dbname = 'bkash_project'; // your database name
$username = 'root'; // your MySQL username
$password = ''; // your MySQL password (empty for XAMPP default)

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
