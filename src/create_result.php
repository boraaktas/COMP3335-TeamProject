<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'labStaff') {
    header("Location: unauthorized.php");
    exit;
}

$role = $_SESSION['role'];
$staffID = $_SESSION['userID'];
$userName = $_SESSION['userName'];

$pageTitle = 'Create Result';
$heading = 'Create a New Result';
$error = '';
$message = '';

try {
    // Fetch orders with 'Pending Result'
    $sql = "SELECT orders.orderID
            FROM orders
            WHERE orders.orderStatus = 'Pending Result'
           ";
    $params = ['types' => '', 'values' => []];
    $ordersResult = queryDatabase($role, $sql, $params);

    $orders = [];
    while ($row = $ordersResult->fetch_assoc()) {
        $orders[] = $row;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $orderID = $_POST['orderID'];
        $interpretation = $_POST['interpretation'];
        $reportURL = 'https://www.example.com/report1'; // Fixed URL

        // Insert the new result
        $sql = "INSERT INTO results (orderID, labStaffResultID, interpretation, reportURL) VALUES (?, ?, ?, ?)";
        $params = [
            'types' => 'iiss',
            'values' => [$orderID, $staffID, $interpretation, $reportURL]
        ];
        queryDatabase($role, $sql, $params);

        $message = "Result created successfully.";

        // Refresh orders list
        $orders = array_filter($orders, function($order) use ($orderID) {
            return $order['orderID'] != $orderID;
        });
    }

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
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
        /* Same styles as before */
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .logout-link {
            margin-top: 20px;
            text-align: center;
        }
        .logout-link a {
            color: #dc3545;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .back-link {
            margin-top: 20px;
            text-align: center;
        }
        .back-link a {
            margin: 0 10px;
        }
        .alert {
            margin-top: 20px;
        }
        textarea {
            resize: vertical;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center"><?php echo htmlspecialchars($heading); ?></h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <div class="form-container mx-auto" style="max-width: 600px;">
            <form method="post" action="">
                <div class="form-group">
                    <label for="orderID">Select Order:</label>
                    <select name="orderID" id="orderID" class="form-control" required>
                        <?php foreach ($orders as $order): ?>
                            <option value="<?php echo htmlspecialchars($order['orderID']); ?>">
                                Order ID: <?php echo htmlspecialchars($order['orderID']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reportURL">Report URL:</label>
                    <input type="text" name="reportURL" id="reportURL" class="form-control" value="https://www.example.com/report1" readonly>
                </div>

                <div class="form-group">
                    <label for="interpretation">Interpretation:</label>
                    <textarea name="interpretation" id="interpretation" class="form-control" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Result</button>
            </form>
            <div class="back-link">
                <a href="welcome_labstaff.php" class="btn btn-secondary mt-3">Back</a>
            </div>
            <div class="logout-link">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>