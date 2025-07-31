<?php
$conn = new mysqli("localhost", "root", "", "image_upload_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_number = $_POST['reg_number'];
    $utr_number = $_POST['utr_number'];
    $amount = $_POST['amount'];

    $stmt = $conn->prepare("UPDATE utr_slips SET utr_number = ?, amount = ? WHERE reg_number = ?");
    $stmt->bind_param("sds", $utr_number, $amount, $reg_number);

    if ($stmt->execute()) {
        echo "Record updated successfully!";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
