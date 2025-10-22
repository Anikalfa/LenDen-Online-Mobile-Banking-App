<?php
session_start();
include 'config/db.php';


if (!isset($_SESSION['user_id'])) {
    echo "Error: Unauthorized access!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receiver_id = $_SESSION['user_id'];  // Current logged-in user's ID
    $sender_phone = $_POST['sender'];     // Phone number of the sender
    $amount = floatval($_POST['amount']);  // Amount to be received

    // Check if sender exists and has enough balance
    $senderQuery = $conn->prepare("SELECT id, balance FROM users WHERE phone = ?");
    $senderQuery->bind_param("s", $sender_phone);
    $senderQuery->execute();
    $senderResult = $senderQuery->get_result();

    if ($senderResult->num_rows === 0) {
        echo "Error: Sender not found!";
        exit();
    }

    $senderData = $senderResult->fetch_assoc();
    $sender_id = $senderData['id'];
    $sender_balance = $senderData['balance'];

    if ($sender_balance < $amount) {
        echo "Error: Sender has insufficient balance!";
        exit();
    }

    // Deduct money from sender
    $new_sender_balance = $sender_balance - $amount;
    $updateSender = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $updateSender->bind_param("di", $new_sender_balance, $sender_id);
    $updateSender->execute();

    // Add money to receiver
    $receiverQuery = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $receiverQuery->bind_param("i", $receiver_id);
    $receiverQuery->execute();
    $receiverResult = $receiverQuery->get_result();
    $receiverData = $receiverResult->fetch_assoc();
    $new_receiver_balance = $receiverData['balance'] + $amount;

    $updateReceiver = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $updateReceiver->bind_param("di", $new_receiver_balance, $receiver_id);
    $updateReceiver->execute();

    // Insert transaction record
    $transactionQuery = $conn->prepare("INSERT INTO transactions (sender_id, receiver_id, amount, type) VALUES (?, ?, ?, 'receive_money')");
    $transactionQuery->bind_param("iid", $sender_id, $receiver_id, $amount);
    $transactionQuery->execute();

    echo "Success: Money received successfully!";
}
?>

<form method="post">
    <input type="text" name="sender" placeholder="Sender Phone Number" required><br><br>
    <input type="number" name="amount" placeholder="Amount" required><br><br>
    <button type="submit">Receive Money</button>
</form>
