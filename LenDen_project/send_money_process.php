<?php
session_start();
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender = $_SESSION['user_id'];  // Sender's ID
    $receiver_id = $_POST['receiver'];  // Receiver's ID
    $amount = floatval($_POST['amount']);  // Amount to send
    $bkash_pin = $_POST['pin'];  // PIN for authentication (renamed from password to pin)
    $reference = $_POST['reference'] ?? null;  // Optional reference

    // Fetch sender's balance and stored PIN (password)
    $senderQuery = $conn->prepare("SELECT balance, password FROM users WHERE id = ?");
    $senderQuery->bind_param("i", $sender);
    $senderQuery->execute();
    $senderResult = $senderQuery->get_result();

    if ($senderResult->num_rows === 0) {
        echo "Error: Sender not found!";
        exit();
    }

    $senderData = $senderResult->fetch_assoc();
    $sender_balance = $senderData['balance'];
    $stored_pin = $senderData['password'];  // Assuming password is stored as PIN

    // Verify Bkash PIN (using password_verify instead of direct comparison)
    if (!password_verify($bkash_pin, $stored_pin)) {
        echo "Error: Incorrect Bkash PIN!";
        exit();
    }

    // Get the receiver's phone number and balance
    $receiverQuery = $conn->prepare("SELECT phone, balance FROM users WHERE id = ?");
    $receiverQuery->bind_param("i", $receiver_id);
    $receiverQuery->execute();
    $receiverResult = $receiverQuery->get_result();

    if ($receiverResult->num_rows === 0) {
        echo "Error: Receiver not found!";
        exit();
    }

    $receiverData = $receiverResult->fetch_assoc();
    $receiver_phone = $receiverData['phone'];

    // Check if the sender has enough balance
    if ($sender_balance < $amount) {
        echo "Error: Insufficient balance!";
        exit();
    }

    // Generate a unique transaction ID
    $transaction_id = uniqid("txn_");

    // Start transaction
    $conn->begin_transaction();

    try {
        // Deduct from sender
        $updateSender = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $updateSender->bind_param("di", $amount, $sender);
        $updateSender->execute();

        // Add to receiver
        $updateReceiver = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $updateReceiver->bind_param("di", $amount, $receiver_id);
        $updateReceiver->execute();

        // Insert transaction record with reference
        $insertTransaction = $conn->prepare("INSERT INTO transactions (transaction_id, sender_id, receiver_id, amount, type, reference, date) 
                                             VALUES (?, ?, ?, ?, 'send_money', ?, NOW())");
        $insertTransaction->bind_param("siids", $transaction_id, $sender, $receiver_id, $amount, $reference);
        $insertTransaction->execute();

        // Commit transaction
        $conn->commit();

        // Update session balance
        $_SESSION['balance'] -= $amount;

        // Redirect to the dashboard with a success message
        $_SESSION['message'] = "Money sent successfully to " . $receiver_phone;
        header("Location: dashboard.php"); // Redirect to dashboard
        exit();

    } catch (Exception $e) {
        $conn->rollback(); // Rollback if any issue occurs
        echo "Transaction failed: " . $e->getMessage();
    }
}
?>



