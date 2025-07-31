<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTR Slips Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        button {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
        }
        .list-btn {
            background: blue;
            color: white;
        }
        .update-btn {
            background: green;
            color: white;
        }
    </style>
</head>
<body>

    <h2>UTR Slips Records</h2>
    <p>Date: <input type="text" id="currentDate" readonly></p>
    
    <button class="list-btn" onclick="fetchStudents()">List Students</button>
    
    <div id="studentTable">
        <!-- Student records will be displayed here -->
    </div>

    <script>
        document.getElementById("currentDate").value = new Date().toISOString().split('T')[0];

        function fetchStudents() {
            fetch('fetch_students.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('studentTable').innerHTML = data;
                })
                .catch(error => console.error('Error fetching students:', error));
        }

        function updateStudent(reg_number) {
            let utr = document.getElementById(`utr_${reg_number}`).value;
            let amount = document.getElementById(`amount_${reg_number}`).value;

            let formData = new URLSearchParams();
            formData.append("reg_number", reg_number);
            formData.append("utr_number", utr);
            formData.append("amount", amount);

            fetch('update_student.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                fetchStudents();
            })
            .catch(error => console.error('Error updating student:', error));
        }
    </script>

</body>
</html>
