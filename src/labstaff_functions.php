<?php

require_once "db.php";

session_start();

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'labStaff') {
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
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if ($task === "result_reporting" && !empty($email)) {
    $stmt = $conn->prepare("
        SELECT * FROM `testResults` 
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
} elseif ($task === "tests") {
    $stmt = $conn->prepare("SELECT * FROM `testCatalogs`");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tableOutput .= "<table border='1'>
            <tr>
                <th>Test Code</th>
                <th>Test Name</th>
                <th>Cost</th>
                <th>Test Description</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $tableOutput .= "<tr>
                <td>" . htmlspecialchars($row["testCode"]) . "</td>
                <td>" . htmlspecialchars($row["testName"]) . "</td>
                <td>" . htmlspecialchars($row["cost"]) . "</td>
                <td>" . htmlspecialchars($row["testDescription"]) . "</td>
            </tr>";
        }
        $tableOutput .= "</table>";
    } else {
        $tableOutput .= "<p>No tests found.</p>";
    }
    $stmt->close();
} else {
    $tableOutput .= "<p>Invalid task or missing input.</p>";
}

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
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <?php echo $tableOutput; ?>
    <a href="logout.php">Logout</a>
</body>
</html>