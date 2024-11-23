<?php

require_once "db.php";

session_start();

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'secretary') {
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

// Sanitize and validate inputs
$task = filter_input(INPUT_POST, 'task', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if ($task === "result_printing" && $email) {
    $stmt = $conn->prepare("
        SELECT * FROM `orders`
        WHERE patientID = (
            SELECT DISTINCT patientID FROM `patients` WHERE email = ?
        )
    ");
    $stmt->bind_param("s", $email);
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
        $tableOutput .= "<p>No test results found.</p>";
    }
    $stmt->close();

} elseif ($task === "appointment" && $email) {
    $stmt = $conn->prepare("
        SELECT * FROM `appointments`
        WHERE patientID = (
            SELECT DISTINCT patientID FROM `patients` WHERE email = ?
        ) AND secretaryID = (
            SELECT staffID FROM `staffs` WHERE email = ?
        )
    ");
    $stmt->bind_param("ss", $email, $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tableOutput .= "<table border='1'>
            <tr>
                <th>Appointment ID</th>
                <th>Patient ID</th>
                <th>Order ID</th>
                <th>Secretary ID</th>
                <th>Sampling Type</th>
                <th>Appointment Date</th>
                <th>Appointment Time</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $tableOutput .= "<tr>
                <td>" . htmlspecialchars($row["appointmentID"]) . "</td>
                <td>" . htmlspecialchars($row["patientID"]) . "</td>
                <td>" . htmlspecialchars($row["orderID"]) . "</td>
                <td>" . htmlspecialchars($row["secretaryID"]) . "</td>
                <td>" . htmlspecialchars($row["samplingType"]) . "</td>
                <td>" . htmlspecialchars($row["appointmentDate"]) . "</td>
                <td>" . htmlspecialchars($row["appointmentTime"]) . "</td>
            </tr>";
        }
        $tableOutput .= "</table>";
    } else {
        $tableOutput .= "<p>No appointments found.</p>";
    }
    $stmt->close();

} elseif ($task === "billing" && $email) {
    $stmt = $conn->prepare("
        SELECT * FROM `billing`
        WHERE orderID = (
            SELECT orderID FROM `orders` o
            JOIN `patients` p ON o.patientID = p.patientID
            WHERE p.email = ?
        )
    ");
    $stmt->bind_param("s", $email);
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
</head>
<body>
    <?php echo $tableOutput; ?>
    <a href="logout.php">Logout</a>
</body>
</html>