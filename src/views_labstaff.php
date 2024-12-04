<?php
// Start the session
session_start();

// Include the database connection file
require_once "db.php";

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'labStaff') {
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
    'view_orders'   => ['title' => 'Test Orders',
                        'heading' => 'Test Orders',
                        'createButton' => 'Create Order',
                        'create_url' => 'create_order.php',
                        'updateButton' => 'Update Order',
                        'update_url' => 'update_order.php'],
    'view_results'  => ['title' => 'View Results',
                        'heading' => 'Test Results',
                        'createButton' => 'Create Result',
                        'create_url' => 'create_result.php',
                        'updateButton' => 'Update Result',
                        'update_url' => 'update_result.php']
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
    $createButton = $allowed_tasks[$task]['createButton'];
    $create_url = $allowed_tasks[$task]['create_url'];
    $updateButton = $allowed_tasks[$task]['updateButton'];
    $update_url = $allowed_tasks[$task]['update_url'];

    // Prepare the SQL query based on the task
    $sql = "";
    $params = [];
    $types = '';

    // Get the patient's email from the session
    $userID = $_SESSION['userID'];

    if ($task === "view_orders") {
        $sql = "SELECT lso.orderID, lso.testName, lso.patientFirstName, lso.patientLastName, lso.orderDate, lso.orderStatus
                FROM labStaffOrders lso
                WHERE lso.labStaffID = ?";
        $params = [$userID];
        $types = 'i';
    } elseif ($task === "view_results") {
        $sql = "SELECT lsr.orderID, lsr.testName, lsr.patientFirstName, lsr.patientLastName,
                       lsr.labStaffOrderFirstName, lsr.labStaffOrderLastName,
                       lsr.labStaffResultFirstName, lsr.labStaffResultLastName,
                       lsr.reportURL, lsr.interpretation
                FROM labStaffResults lsr
                WHERE lsr.labStaffOrderID = ? OR lsr.labStaffResultID = ?";
        $params = [$userID, $userID];
        $types = 'ii';
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
                            <th>Patient Name Surname</th>
                            <th>Order Date</th>
                            <th>Oder Status</th>
                        </tr>
                    </thead>
                    <tbody>";
                while ($row = $result->fetch_assoc()) {
                    $tableOutput .= "<tr>
                        <td>" . htmlspecialchars($row["orderID"]) . "</td>
                        <td>" . htmlspecialchars($row["testName"]) . "</td>
                        <td>" . htmlspecialchars($row["patientFirstName"]) . " " . htmlspecialchars($row["patientLastName"]) . "</td>
                        <td>" . htmlspecialchars($row["orderDate"]) . "</td>
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
                            <th>Patient Name Surname</th>
                            <th>Physician Name Surname</th>
                            <th>Pathologist Name Surname</th>
                            <th>Report</th>
                            <th>Interpretation</th>
                        </tr>
                    </thead>
                    <tbody>";
                while ($row = $result->fetch_assoc()) {
                    $tableOutput .= "<tr>
                        <td>" . htmlspecialchars($row["orderID"]) . "</td>
                        <td>" . htmlspecialchars($row["testName"]) . "</td>
                        <td>" . htmlspecialchars($row["patientFirstName"]) . " " . htmlspecialchars($row["patientLastName"]) . "</td>
                        <td>" . htmlspecialchars($row["labStaffOrderFirstName"]) . " " . htmlspecialchars($row["labStaffOrderLastName"]) . "</td>
                        <td>" . htmlspecialchars($row["labStaffResultFirstName"]) . " " . htmlspecialchars($row["labStaffResultLastName"]) . "</td>
                        <td><a href='" . htmlspecialchars($row["reportURL"]) . "' target='_blank'>View Report</a></td>
                        <td>" . htmlspecialchars($row["interpretation"]) . "</td>
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
    <title>Lab Staff Dashboard - <?php echo htmlspecialchars($pageTitle); ?></title>
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
            <a href="welcome_labstaff.php" class="btn btn-secondary mt-3">Back</a>
            <a href="<?php echo $create_url; ?>" class="btn btn-primary mt-3"><?php echo $createButton; ?></a>
            <a href="<?php echo $update_url; ?>" class="btn btn-warning mt-3"><?php echo $updateButton; ?></a>
            <a href="logout.php" class="btn btn-danger mt-3 logout-link">Logout</a>
        <?php endif; ?>
    </div>
</body>
</html>