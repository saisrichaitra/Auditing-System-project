<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_number = $_POST['reg_number'];
    $paidAmount = $_POST['PaidAmount'];
    $utrNumbers = isset($_POST['utr']) ? implode(", ", $_POST['utr']) : "";

    // Fetch student phone number
    $stmt = $conn->prepare("SELECT phone FROM students WHERE reg_number = ?");
    $stmt->bind_param("s", $reg_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        die("Student not found.");
    }

    $phone = $student['phone'];
    $message = "Your payment of ₹$paidAmount has been received. UTR: $utrNumbers.";

    // Send SMS via Fast2SMS
    $apiKey = "YOUR_FAST2SMS_API_KEY";  // Replace with your API key
    $url = "https://www.fast2sms.com/dev/bulkV2";
    $data = [
        "route" => "v3",
        "sender_id" => "TXTIND",
        "message" => $message,
        "language" => "english",
        "flash" => 0,
        "numbers" => $phone,
    ];

    $headers = [
        "authorization: $apiKey",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    echo "SMS sent successfully!";
}
?>
