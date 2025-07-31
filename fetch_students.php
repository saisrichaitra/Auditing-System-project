<?php
$conn = new mysqli("localhost", "root", "", "image_upload_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT reg_number, branch, utr_number, amount FROM utr_slips";
$result = $conn->query($sql);

if (!$result) {
    die("Query Failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Registration Number</th>
                <th>Branch</th>
                <th>UTR Number</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['reg_number']}</td>
                <td>{$row['branch']}</td>
                <td><input type='text' value='{$row['utr_number']}' id='utr_{$row['reg_number']}'></td>
                <td><input type='text' value='{$row['amount']}' id='amount_{$row['reg_number']}'></td>
                <td><button class='update-btn' onclick='updateStudent(\"{$row['reg_number']}\")'>Update</button></td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "No records found.";
}

$conn->close();
?>
