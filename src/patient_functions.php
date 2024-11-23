<?php

require_once "db.php";

session_start();

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'patient') {
    header("Location: unauthorized.php");
    exit;
}

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token validation failed.");
}

// Get the database connection
try {
    $conn = getConnection();
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Initialize a variable to store table output
$tableOutput = "";

// Validate and sanitize task input
$task = filter_input(INPUT_POST, 'task', FILTER_SANITIZE_SPECIAL_CHARS);

if ($task === "test_orders") {
    $stmt = $conn->prepare("
        SELECT * FROM `orders`
        WHERE patientID = (
            SELECT DISTINCT patientID FROM `patients` WHERE email = ?
        )
    ");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tableOutput .= "<table border='1'>
            <tr>
                <th>Order ID</th>
                <th>Patient ID</th>
                <th>Test Code</th>
                <th>Order Date</th>
                <th>Order Status</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $tableOutput .= "<tr>
                <td>" . htmlspecialchars($row["orderID"]) . "</td>
                <td>" . htmlspecialchars($row["patientID"]) . "</td>
                <td>" . htmlspecialchars($row["testCode"]) . "</td>
                <td>" . htmlspecialchars($row["orderDate"]) . "</td>
                <td>" . htmlspecialchars($row["orderStatus"]) . "</td>
            </tr>";
        }
        $tableOutput .= "</table>";
    } else {
        $tableOutput .= "<p>No test orders found.</p>";
    }
    $stmt->close();

} elseif ($task === "view_results") {
    $stmt = $conn->prepare("
        SELECT * FROM `testResults`
        WHERE orderID = (
            SELECT orderID FROM `orders` o
            JOIN `patients` p ON o.patientID = p.patientID
            WHERE p.email = ?
        )
    ");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tableOutput .= "<table border='1'>
            <tr>
                <th>Result ID</th>
                <th>Order ID</th>
                <th>Report URL</th>
                <th>Interpretation</th>
                <th>Lab Staff ID</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $tableOutput .= "<tr>
                <td>" . htmlspecialchars($row["resultID"]) . "</td>
                <td>" . htmlspecialchars($row["orderID"]) . "</td>
                <td><a href='" . htmlspecialchars($row["reportURL"]) . "' target='_blank'>View Report</a></td>
                <td>" . htmlspecialchars($row["interpretation"]) . "</td>
                <td>" . htmlspecialchars($row["labStaffID"]) . "</td>
            </tr>";
        }
        $tableOutput .= "</table>";
    } else {
        $tableOutput .= "<p>No test results found.</p>";
    }
    $stmt->close();

} elseif ($task === "bills") {
    $stmt = $conn->prepare("
        SELECT * FROM `billing`
        WHERE orderID = (
            SELECT orderID FROM `orders` o
            JOIN `patients` p ON o.patientID = p.patientID
            WHERE p.email = ?
        )
    ");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tableOutput .= "<table border='1'>
            <tr>
                <th>Billing ID</th>
                <th>Order ID</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Insurance Claim Status</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $tableOutput .= "<tr>
                <td>" . htmlspecialchars($row["billingID"]) . "</td>
                <td>" . htmlspecialchars($row["orderID"]) . "</td>
                <td>" . htmlspecialchars($row["billedAmount"]) . "</td>
                <td>" . htmlspecialchars($row["paymentStatus"]) . "</td>
                <td>" . htmlspecialchars($row["insuranceClaimStatus"]) . "</td>
            </tr>";
        }
        $tableOutput .= "</table>";
    } else {
        $tableOutput .= "<p>No bills found.</p>";
    }
    $stmt->close();

} else {
    $tableOutput .= "<p>Invalid task or missing input.</p>";
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Output</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <?php echo $tableOutput; ?>
    <a href="logout.php">Logout</a>
</body>
</html>