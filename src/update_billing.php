<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'secretary') {
    header("Location: unauthorized.php");
    exit;
}

$role = $_SESSION['role'];
$secretaryID = $_SESSION['userID'];
$userName = $_SESSION['userName'];

$pageTitle = 'Change Payment Status';
$heading = 'Change Payment Status of an Order';
$error = '';
$message = '';

try {
    // Fetch orders where the appointment belongs to this secretary
    $sql = "SELECT orders.orderID, billings.paymentStatus
            FROM orders
            JOIN appointments ON orders.orderID = appointments.orderID
            JOIN billings ON orders.orderID = billings.orderID
            WHERE appointments.secretaryID = ?";
    $params = ['types' => 'i', 'values' => [$secretaryID]];
    $ordersResult = queryDatabase($role, $sql, $params);

    $orders = [];
    while ($row = $ordersResult->fetch_assoc()) {
        $orders[] = $row;
    }

    // Handle form submission
    if (isset($_POST['changePaymentStatus'])) {
        $orderID = $_POST['orderID'];
        $paymentStatus = $_POST['paymentStatus'];

        // CHANGE current payment status
        $sql = "UPDATE billings
                SET paymentStatus = ?
                WHERE orderID = ?";
        $params = [
            'types' => 'ii',
            'values' => [$paymentStatus, $orderID]
        ];
        queryDatabase($role, $sql, $params);

        $message = "Payment status changed successfully.";

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
    <title>Secretary Dashboard - <?php echo htmlspecialchars($pageTitle); ?></title>
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
        .table-responsive {
            margin-bottom: 20px;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .no-orders {
            text-align: center;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 1rem;
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

        <?php if (count($orders) > 0): ?>
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
                        <label for="paymentStatus">Payment Status:</label>
                        <select name="paymentStatus" id="paymentStatus" class="form-control" required>
                            <option value=1>Paid</option>
                            <option value=0>Unpaid</option>
                        </select>
                    </div>

                    <button type="submit" name="changePaymentStatus" class="btn btn-primary btn-block">Change Payment Status</button>
                </form>
                <div class="back-link">
                    <a href="welcome_secretary.php" class="btn btn-secondary mt-3">Back to Welcome Page</a>
                </div>
            </div>
        <?php else: ?>
            <div class="no-orders">
                <p>No orders available to change payment status.</p>
                <div class="back-link">
                    <a href="welcome_secretary.php" class="btn btn-secondary mt-3">Back to Welcome Page</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>