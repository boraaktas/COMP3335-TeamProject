<?php

require_once "db.php";

session_start();

// Get user-specific database credentials based on the logged-in user's role
$credentials = getDatabaseCredentials($_SESSION['access_level'] ?? 'root');
$conn = new mysqli(
    $credentials['host'],
    $credentials['username'],
    $credentials['password'],
    $credentials['dbname']
);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Initialize a variable to store table output
$tableOutput = "";

if ($_POST['task'] == "test_orders") {
    $passwordHash = password_hash($_POST['password'], PASSWORD_ARGON2ID);
    $stmt = $conn->prepare("SELECT * FROM `orders` WHERE PatientID = (SELECT DISTINCT PatientID FROM `patients` WHERE email = ?)");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    // The visualization of the results is just basic right now can be done more beautiful if necessary
    if ($result->num_rows > 0) {
        $tableOutput.= "<table border='1'>
            <tr>
            <th>Order ID</th>
            <th>Patient ID</th>
            <th>Test Code</th>
            <th>Order Date</th>
            <th>Order Status</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $field1name = $row["orderID"];
            $field2name = $row["patientID"];
            $field3name = $row["testCode"];
            $field4name = $row["orderDate"];
            $field5name = $row["orderStatus"]; 
    
            $tableOutput.= '<tr> 
                      <td>'.$field1name.'</td> 
                      <td>'.$field2name.'</td> 
                      <td>'.$field3name.'</td> 
                      <td>'.$field4name.'</td> 
                      <td>'.$field5name.'</td> 
                  </tr>';
        }
        $tableOutput.="</table>";
        $result->free();
    }else {
        $tableOutput. "<p>No test orders found.</p>";
    }


}elseif ($_POST['task'] == "view_results") {
    $passwordHash = password_hash($_POST['password'], PASSWORD_ARGON2ID);
    $stmt = $conn->prepare("SELECT * FROM `testResults` WHERE 
                            OrderID = (SELECT OrderID FROM `orders` o
                            JOIN `patients` p
                            ON o.PatientID = p.PatientID
                            WHERE email = ?)");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    // The visualization of the results is just basic right now can be done more beautiful if necessary
    if ($result->num_rows > 0) {
        $tableOutput.="<table border='1'>
            <tr>
            <th>Result ID</th>
            <th>Order ID</th>
            <th>Report URL</th>
            <th>Interpretation</th>
            <th>Lab staff ID</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $field1name = $row["resultID"];
            $field2name = $row["orderID"];
            $field3name = $row["reportURL"];
            $field4name = $row["interpretation"];
            $field5name = $row["labStaffID"]; 
    
            $tableOutput.= '<tr> 
                      <td>'.$field1name.'</td> 
                      <td>'.$field2name.'</td> 
                      <td>'.$field3name.'</td> 
                      <td>'.$field4name.'</td> 
                      <td>'.$field5name.'</td> 
                  </tr>';
        }
        $tableOutput.= "</table>";
        $result->free();
    }else {
        $tableOutput. "<p>No results found.</p>";
    }
}elseif ($_POST['task'] == "bills"){
    $passwordHash = password_hash($_POST['password'], PASSWORD_ARGON2ID);
    $stmt = $conn->prepare("SELECT * FROM `billing` WHERE 
                            OrderID = (SELECT OrderID FROM `orders` o
                            JOIN `patients` p
                            ON o.PatientID = p.PatientID
                            WHERE email = ?)");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    // The visualization of the results is just basic right now can be done more beautiful if necessary
    if ($result->num_rows > 0) {
        $tableOutput.= "<table border='1'>
            <tr>
            <th>Billing ID</th>
            <th>Order ID</th>
            <th>Amount</th>
            <th>Payment status</th>
            <th>Insurance claim status</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $field1name = $row["billingID"];
            $field2name = $row["orderID"];
            $field3name = $row["billedAmount"];
            $field4name = $row["paymentStatus"];
            $field5name = $row["insuranceClaimStatus"]; 
    
            $tableOutput.= '<tr> 
                      <td>'.$field1name.'</td> 
                      <td>'.$field2name.'</td> 
                      <td>'.$field3name.'</td> 
                      <td>'.$field4name.'</td> 
                      <td>'.$field5name.'</td> 
                  </tr>';
        }
        $tableOutput.= "</table>";
        $result->free();
    }else {
        $tableOutput. "<p>No bills found.</p>";
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Output</title>
</head>
<body>
    <?php
    // Output the table or message
    echo $tableOutput;
    ?>
    <br>
    <div>
        <a href="logout.php">
            <button type="button">Logout</button>
        </a>
        <button type="button" onclick="history.back();">Back</button>
        <!-- OR use a predefined URL for the back button -->
        <!-- <a href="task_selection.php">
            <button type="button">Back</button>
        </a> -->
    </div>
</body>
</html>