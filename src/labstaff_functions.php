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

if ($_POST['task'] == "result_reporting") {
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
        $tableOutput. "<p>No test results found.</p>";
    }
}elseif ($_POST['task'] == "tests") {
    $passwordHash = password_hash($_POST['password'], PASSWORD_ARGON2ID);
    $stmt = $conn->prepare("SELECT * FROM `testCatalogs`");
    $stmt->execute();
    $result = $stmt->get_result();
    // The visualization of the results is just basic right now can be done more beautiful if necessary
    if ($result->num_rows > 0) {
        $tableOutput.="<table border='1'>
            <tr>
            <th>Test Code</th>
            <th>Test name</th>
            <th>Cost</th>
            <th>Test description</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $field1name = $row["testCode"];
            $field2name = $row["testName"];
            $field3name = $row["cost"];
            $field4name = $row["testDescription"];
            
            $tableOutput.= '<tr> 
                      <td>'.$field1name.'</td> 
                      <td>'.$field2name.'</td> 
                      <td>'.$field3name.'</td> 
                      <td>'.$field4name.'</td>
                  </tr>';
        }
        $tableOutput.= "</table>";
        $result->free();
    }else {
        $tableOutput. "<p>No tests found.</p>";
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