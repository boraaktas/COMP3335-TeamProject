<?php
// Start the session
session_start();

// Include the database connection file
require_once "db.php";

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'secretary') {
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
    'view_appointments' => ['title' => 'Appointments',
                            'heading' => 'Appointments',
                            'createButton' => 'Create Appointment',
                            'create_url' => 'create_appointment.php',
                            'updateButton' => 'Update Appointment',
                            'update_url' => 'update_appointment.php'],
    'view_billings'     => ['title' => 'Billings',
                            'heading' => 'Billings',
                            'updateButton' => 'Update Billing',
                            'update_url' => 'update_billing.php'],
    'view_results'      => ['title' => 'Results',
                            'heading' => 'Test Results'],
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
    $createButton = $allowed_tasks[$task]['createButton'] ?? '';
    $create_url = $allowed_tasks[$task]['create_url'] ?? '';
    $updateButton = $allowed_tasks[$task]['updateButton'] ?? '';
    $update_url = $allowed_tasks[$task]['update_url'] ?? '';

    // Prepare the SQL query based on the task
    $sql = "";
    $params = [];
    $types = '';

    // Get the patient's email from the session
    $userID = $_SESSION['userID'];

    if ($task === "view_appointments") {
        $sql = "SELECT sa.orderID, sa.patientSSN, sa.patientFirstName, sa.patientLastName, sa.appointmentDateTime
                FROM secretaryAppointments sa
                WHERE sa.secretaryID = ?";
        $params = [$userID];
        $types = 'i';
    } elseif ($task === "view_billings") {
        $sql = "SELECT sb.orderID, sb.patientSSN, sb.patientFirstName, sb.patientLastName, sb.billedAmount, sb.insuranceClaimStatus, sb.paymentStatus
                FROM secretaryBillings sb
                WHERE sb.secretaryID = ?";
        $params = [$userID];
        $types = 'i';
    } elseif ($task === "view_results") {
        $sql = "SELECT sr.orderID, sr.patientSSN, sr.patientFirstName, sr.patientLastName, sr.reportURL
                FROM secretaryResults sr
                WHERE sr.secretaryID = ?";
        $params = [$userID];
        $types = 'i';
    }

    // Query the database
    if ($sql) {
        // Use prepared statements to prevent SQL injection
        $result = queryDatabase($_SESSION['role'], $sql, ['types' => $types, 'values' => $params]);

        if ($result && $result->num_rows > 0) {

            // Generate the table output based on the task
            if ($task === "view_appointments") {
                $tableOutput .= "
                <table class='table table-striped table-bordered'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>Order</th>
                            <th>Patient SSN</th>
                            <th>Patient Name Surname</th>
                            <th>Appointment Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>";
                while ($row = $result->fetch_assoc()) {
                    $tableOutput .= "<tr>
                        <td>" . htmlspecialchars($row["orderID"]) . "</td>
                        <td>" . htmlspecialchars($row["patientSSN"]) . "</td>
                        <td>" . htmlspecialchars($row["patientFirstName"]) . " " . htmlspecialchars($row["patientLastName"]) . "</td>
                        <td>" . htmlspecialchars($row["appointmentDateTime"] ?? "N/A") . "</td>
                    </tr>";
                }
                $tableOutput .= "</tbody></table>";

            } elseif ($task === "view_billings") {
                $tableOutput .= "
                <table class='table table-striped table-bordered'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>Order</th>
                            <th>Patient SSN</th>
                            <th>Patient Name Surname</th>
                            <th>Billed Amount</th>
                            <th>Insurance Claim Status</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>";
                while ($row = $result->fetch_assoc()) {
                    $tableOutput .= "<tr>
                        <td>" . htmlspecialchars($row["orderID"]) . "</td>
                        <td>" . htmlspecialchars($row["patientSSN"]) . "</td>
                        <td>" . htmlspecialchars($row["patientFirstName"]) . " " . htmlspecialchars($row["patientLastName"]) . "</td>
                        <td>" . htmlspecialchars($row["billedAmount"]) . "</td>
                        <td>" . htmlspecialchars(1 === $row["insuranceClaimStatus"] ? "Approved" : "Rejected") . "</td>
                        <td>" . htmlspecialchars(1 === $row["paymentStatus"] ? "Paid" : "Pending") . "</td>
                    </tr>";
                }
                $tableOutput .= "</tbody></table>";
            } elseif ($task === "view_results") {
                $tableOutput .= "
                <table class='table table-striped table-bordered'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>Order</th>
                            <th>Patient SSN</th>
                            <th>Patient Name Surname</th>
                            <th>Report</th>
                        </tr>
                    </thead>
                    <tbody>";
                while ($row = $result->fetch_assoc()) {
                    $tableOutput .= "<tr>
                        <td>" . htmlspecialchars($row["orderID"]) . "</td>
                        <td>" . htmlspecialchars($row["patientSSN"]) . "</td>
                        <td>" . htmlspecialchars($row["patientFirstName"]) . " " . htmlspecialchars($row["patientLastName"]) . "</td>
                        <td><a href='" . htmlspecialchars($row["reportURL"]) . "' target='_blank'>View Report</a></td>
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
    <title>Secretary Dashboard - <?php echo htmlspecialchars($pageTitle); ?></title>
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
            <a href="welcome_secretary.php" class="btn btn-secondary mt-3">Back</a>
            <?php if (!empty($createButton)): ?>
                <a href="<?php echo $create_url; ?>" class="btn btn-primary mt-3"><?php echo $createButton; ?></a>
            <?php endif; ?>
            <?php if (!empty($updateButton)): ?>
                <a href="<?php echo $update_url; ?>" class="btn btn-warning mt-3"><?php echo $updateButton; ?></a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger mt-3 logout-link">Logout</a>
        <?php endif; ?>
    </div>
</body>
</html>