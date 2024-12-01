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

$pageTitle = 'Update Order';
$heading = 'Update an Existing Order';
$error = '';
$message = '';

try {
    // Fetch orders assigned to this lab staff
    $sql = "SELECT orderID, patientID, testID, orderDate FROM orders WHERE labStaffOrderID = ?";
    $params = ['types' => 'i', 'values' => [$staffID]];
    $ordersResult = queryDatabase($role, $sql, $params);

    $orders = [];
    while ($row = $ordersResult->fetch_assoc()) {
        $orders[] = $row;
    }

    // Fetch patients
    $sql = "SELECT patientID, firstName, lastName FROM patients";
    $params = ['types' => '', 'values' => []];
    $patientsResult = queryDatabase($role, $sql, $params);

    $patients = [];
    while ($row = $patientsResult->fetch_assoc()) {
        $patients[] = $row;
    }

    // Fetch tests
    $sql = "SELECT testID, testName FROM testCatalogs";
    $testsResult = queryDatabase($role, $sql, $params);

    $tests = [];
    while ($row = $testsResult->fetch_assoc()) {
        $tests[] = $row;
    }

    // Handle form submissions
    if (isset($_POST['selectOrder'])) {
        // Fetch selected order details
        $orderID = $_POST['orderID'];
        $sql = "SELECT * FROM orders WHERE orderID = ?";
        $params = ['types' => 'i', 'values' => [$orderID]];
        $orderResult = queryDatabase($role, $sql, $params);
        $order = $orderResult->fetch_assoc();

    } elseif (isset($_POST['updateOrder'])) {
        // Update the order
        $orderID = $_POST['orderID'];
        $patientID = $_POST['patientID'];
        $testID = $_POST['testID'];
        $orderDate = $_POST['orderDate'];

        $sql = "UPDATE orders SET patientID = ?, testID = ?, orderDate = ? WHERE orderID = ?";
        $params = [
            'types' => 'iisi',
            'values' => [$patientID, $testID, $orderDate, $orderID]
        ];
        queryDatabase($role, $sql, $params);

        $message = "Order updated successfully.";
        unset($order); // Clear the selected order

    } elseif (isset($_POST['deleteOrder'])) {
        // Delete the order
        $orderID = $_POST['orderID'];

        $sql = "DELETE FROM orders WHERE orderID = ?";
        $params = ['types' => 'i', 'values' => [$orderID]];
        queryDatabase($role, $sql, $params);

        $message = "Order deleted successfully.";
        unset($order); // Clear the selected order
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
        /* Same styles as in create_order.php */
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

        <?php if (!isset($order)): ?>
            <div class="form-container mx-auto" style="max-width: 600px;">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="orderID">Select Order:</label>
                        <select name="orderID" id="orderID" class="form-control" required>
                            <?php foreach ($orders as $orderItem): ?>
                                <option value="<?php echo htmlspecialchars($orderItem['orderID']); ?>">
                                    Order ID: <?php echo htmlspecialchars($orderItem['orderID']); ?>,
                                    Patient ID: <?php echo htmlspecialchars($orderItem['patientID']); ?>,
                                    Test ID: <?php echo htmlspecialchars($orderItem['testID']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="selectOrder" class="btn btn-primary btn-block">Select Order</button>
                </form>
                <div class="back-link">
                    <a href="welcome_labstaff.php" class="btn btn-secondary mt-3">Back</a>
                </div>
                <div class="logout-link">
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <div class="form-container mx-auto" style="max-width: 600px;">
                <form method="post" action="">
                    <input type="hidden" name="orderID" value="<?php echo htmlspecialchars($order['orderID']); ?>">

                    <div class="form-group">
                        <label for="patientID">Select Patient:</label>
                        <select name="patientID" id="patientID" class="form-control" required>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo htmlspecialchars($patient['patientID']); ?>"
                                    <?php if ($patient['patientID'] == $order['patientID']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($patient['firstName'] . ' ' . $patient['lastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="testID">Select Test:</label>
                        <select name="testID" id="testID" class="form-control" required>
                            <?php foreach ($tests as $test): ?>
                                <option value="<?php echo htmlspecialchars($test['testID']); ?>"
                                    <?php if ($test['testID'] == $order['testID']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($test['testName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="orderDate">Order Date:</label>
                        <input type="date" name="orderDate" id="orderDate" class="form-control" value="<?php echo htmlspecialchars($order['orderDate']); ?>" required>
                    </div>

                    <button type="submit" name="updateOrder" class="btn btn-primary btn-block">Update Order</button>
                    <button type="submit" name="deleteOrder" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to delete this order?');">Delete Order</button>
                </form>
                <div class="back-link">
                    <a href="update_order.php" class="btn btn-secondary mt-3">Back</a>
                </div>
                <div class="logout-link">
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>