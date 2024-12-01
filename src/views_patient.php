<?php
// Start the session
session_start();

// Include the database connection file
require_once "db.php";

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'patient') {
    header("Location: unauthorized.php");
    exit;
}

// Initialize variables
$tableOutput = "";
$error = "";
$pageTitle = "";
$heading = "";

// Define allowed tasks with corresponding titles and headings
$allowed_tasks = [
    'view_orders'   => ['title' => 'Test Orders', 'heading' => 'Your Test Orders'],
    'view_results'  => ['title' => 'View Results', 'heading' => 'Your Test Results'],
    'view_bills'    => ['title' => 'Billings', 'heading' => 'Your Bills'],
];

// Get the task input
$task = $_POST['task'] ?? '';

// Validate the task input
if (!array_key_exists($task, $allowed_tasks)) {
    $error = "Invalid task.";
} else {
    // Set the page title and heading based on the task
    $pageTitle = $allowed_tasks[$task]['title'];
    $heading = $allowed_tasks[$task]['heading'];

    // Prepare the SQL query based on the task
    $sql = "";
    $params = [];
    $types = '';

    // Get the patient's email from the session
    $userID = $_SESSION['userID'];

    if ($task === "view_orders") {
        $sql = "SELECT po.orderID, po.testName, po.labStaffFirstName, po.labStaffLastName, po.orderDate, po.appointmentDateTime, po.orderStatus
                FROM patientOrders po
                WHERE po.patientID = ?";
        $params = [$userID];
        $types = 'i';
    } elseif ($task === "view_results") {
        $sql = "SELECT pr.orderID, pr.testName, pr.labStaffFirstName, pr.labStaffLastName, pr.reportURL, pr.interpretation
                FROM patientResults pr
                WHERE pr.patientID = ?";
        $params = [$userID];
        $types = 'i';
    } elseif ($task === "view_bills") {
        $sql = "SELECT pb.orderID, pb.testName, pb.billedAmount, pb.insuranceClaimStatus, pb.paymentStatus
                FROM patientBillings pb
                WHERE pb.patientID = ?";
        $params = [$userID];
        $types = 'i';
    }

    // Query the database
    if ($sql) {
        // Use prepared statements to prevent SQL injection
        $result = queryDatabase($_SESSION['role'], $sql, ['types' => $types, 'values' => $params]);

        if ($result && $result->num_rows > 0) {

            // Generate the table output based on the task
            if ($task === "view_orders") {
                $tableOutput .= "
                <table class='table table-striped table-bordered'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>Order</th>
                            <th>Test Name</th>
                            <th>Physician</th>
                            <th>Order Date</th>
                            <th>Appointment Date & Time</th>
                            <th>Order Status</th>
                        </tr>
                    </thead>
                    <tbody>";
                while ($row = $result->fetch_assoc()) {
                    $tableOutput .= "<tr>
                        <td>" . htmlspecialchars($row["orderID"]) . "</td>
                        <td>" . htmlspecialchars($row["testName"]) . "</td>
                        <td>" . htmlspecialchars($row["labStaffFirstName"]) . " " . htmlspecialchars($row["labStaffLastName"]) . "</td>
                        <td>" . htmlspecialchars($row["orderDate"]) . "</td>
                        <td>" . htmlspecialchars($row["appointmentDateTime"] ?? "N/A") . "</td>
                        <td>" . htmlspecialchars($row["orderStatus"]) . "</td>
                    </tr>";
                }
                $tableOutput .= "</tbody></table>";

            } elseif ($task === "view_results") {
                $tableOutput .= "
                <table class='table table-striped table-bordered'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>Order</th>
                            <th>Test Name</th>
                            <th>Physician</th>
                            <th>Report</th>
                            <th>Interpretation</th>
                        </tr>
                    </thead>
                    <tbody>";
                while ($row = $result->fetch_assoc()) {
                    $tableOutput .= "<tr>
                        <td>" . htmlspecialchars($row["orderID"]) . "</td>
                        <td>" . htmlspecialchars($row["testName"]) . "</td>
                        <td>" . htmlspecialchars($row["labStaffFirstName"]) . " " . htmlspecialchars($row["labStaffLastName"]) . "</td>
                        <td><a href='" . htmlspecialchars($row["reportURL"]) . "' target='_blank'>View Report</a></td>
                        <td>" . htmlspecialchars($row["interpretation"]) . "</td>
                    </tr>";
                }
                $tableOutput .= "</tbody></table>";
                
            } elseif ($task === "view_bills") {
                $tableOutput .= "
                <table class='table table-striped table-bordered'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>Order</th>
                            <th>Test Name</th>
                            <th>Billed Amount</th>
                            <th>Insurance Claim Status</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>";
                while ($row = $result->fetch_assoc()) {
                    $tableOutput .= "<tr>
                        <td>" . htmlspecialchars($row["orderID"]) . "</td>
                        <td>" . htmlspecialchars($row["testName"]) . "</td>
                        <td>" . htmlspecialchars($row["billedAmount"]) . "</td>
                        <td>" . htmlspecialchars(1 === $row["insuranceClaimStatus"] ? 'Claimed' : 'Not Claimed') . "</td>
                        <td>" . htmlspecialchars(1 === $row["paymentStatus"] ? 'Paid' : 'Not Paid') . "</td>
                    </tr>";
                }
                $tableOutput .= "</tbody></table>";
            }

        } else {
            $tableOutput .= "<p class='text-warning'>No records found.</p>";
        }
        
    } else {
        $error = "An error occurred while processing your request.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard - <?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .logout-link {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <h2 class="mb-4"><?php echo htmlspecialchars($heading); ?></h2>
            <?php echo $tableOutput; ?>
            <a href="welcome_patient.php" class="btn btn-secondary mt-3">Back</a>
            <a href="logout.php" class="btn btn-danger mt-3 logout-link">Logout</a>
        <?php endif; ?>
    </div>
</body>
</html>