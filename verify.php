<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "image_upload_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database connection failed.']));
}

$reg_number = $_GET['reg_number'] ?? '';

if (empty($reg_number)) {
    die(json_encode(['error' => 'Missing Registration Number']));
}

$stmt = $pdo->prepare("SELECT PaidAmount, due_amount, uploaded_at FROM utr_slips WHERE reg_number = ?");
$stmt->execute([$reg_number]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo json_encode(['error' => 'No records found.']);
} else {
    echo json_encode($data);
}
?>
