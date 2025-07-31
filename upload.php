<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "image_upload_db";
$logFile = __DIR__ . '/error.log';

header('Content-Type: application/json');

// Database Connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage() . "\n", 3, $logFile);
    die(json_encode(['error' => 'Database connection failed.']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_number = $_POST['reg_number'] ?? '';
    $PaidAmount = $_POST['PaidAmount'] ?? '';
    $utr_numbers = $_POST['utr'] ?? [];
    $images = $_FILES['images'] ?? [];

    if (empty($reg_number) || empty($PaidAmount) || empty($utr_numbers) || empty($images['tmp_name'][0])) {
        echo json_encode(['error' => 'Missing required fields.']);
        exit;
    }

    $pdo->beginTransaction();
    try {
        foreach ($utr_numbers as $i => $utr_number) {
            if (!isset($images['tmp_name'][$i]) || empty($images['tmp_name'][$i])) {
                continue;
            }

            $imageData = file_get_contents($images['tmp_name'][$i]);
            $imageType = $images['type'][$i];

            // Fetch phone number from database
            $stmtPhone = $pdo->prepare("SELECT phone FROM utr_slips WHERE reg_number = ?");
            $stmtPhone->execute([$reg_number]);
            $phoneResult = $stmtPhone->fetch(PDO::FETCH_ASSOC);

            if (!$phoneResult || empty($phoneResult['phone'])) {
                throw new Exception("Phone number not found for this registration number.");
            }
            $phone_number = $phoneResult['phone']; // Get the phone number

            // Check if record exists
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM utr_slips WHERE reg_number = ?");
            $stmtCheck->execute([$reg_number]);
            $exists = $stmtCheck->fetchColumn();

            if ($exists) {
                $stmt = $pdo->prepare("UPDATE utr_slips 
                                       SET utr_number = ?, image = ?, image_type = ?, PaidAmount = ?, uploaded_at = NOW() 
                                       WHERE reg_number = ?");
                $stmt->execute([$utr_number, $imageData, $imageType, $PaidAmount, $reg_number]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO utr_slips 
                                       (reg_number, utr_number, image, image_type, PaidAmount, uploaded_at) 
                                       VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$reg_number, $utr_number, $imageData, $imageType, $PaidAmount]);
            }
        }
        $pdo->commit();

// Fetch total amount from the database (assuming it is stored in `utr_slips`)
$stmt = $pdo->prepare("SELECT amount, phone FROM utr_slips WHERE reg_number = ?");
$stmt->execute([$reg_number]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $totalAmount = $result['amount']; // The total amount from the database
    $phone = $result['phone']; // Fetch phone number
    $dueAmount = $totalAmount - $PaidAmount; // Calculate due amount

    // Prepare SMS message
    $message = "The amount was received from the student $reg_number, and your due amount is $dueAmount. Please clear the fee early.";

    // Send SMS using Fast2SMS API
    $apiKey = "YOUR_FAST2SMS_API_KEY"; // Replace with your Fast2SMS API key
    $senderId = "FSTSMS"; // Default Fast2SMS sender ID
    $route = "p"; // "p" for promotional, "t" for transactional
    $url = "https://www.fast2sms.com/dev/bulkV2";

    $postData = [
        "authorization" => $apiKey,
        "message" => $message,
        "sender_id" => $senderId,
        "route" => $route,
        "numbers" => $phone,
    ];

    $headers = [
        "Content-Type: application/json",
        "Authorization: $apiKey"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    // Log SMS response for debugging
    error_log("SMS Response: " . $response . "\n", 3, $logFile);
}

echo json_encode(['success' => 'Data uploaded successfully!']);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Upload error: " . $e->getMessage() . "\n", 3, $logFile);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to send SMS using Fast2SMS
function sendSMS($phone, $reg_number, $PaidAmount) {
    $apiKey = "C9KZBMr8SeSK5obfHUeqmCkKPfsODtEcAV5TUPrTzfl7Z7ltPpxpDGlyhuLC";  // Replace with your Fast2SMS API key
    $message = "Payment received: Reg No: $reg_number, Amount: $PaidAmount. Thank you!";
    $senderId = "FSTSMS"; // Default Fast2SMS sender ID

    $data = [
        "route" => "q",
        "message" => $message,
        "language" => "english",
        "flash" => 0,
        "numbers" => $phone
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "authorization: $apiKey",
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}
?>
