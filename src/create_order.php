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

$pageTitle = 'Create Order';
$heading = 'Create a New Order';
$error = '';
$message = '';

try {
    // Fetch patients
    $sql = "SELECT patientID, firstName, lastName FROM patients";
    $params = ['types' => '', 'values' => []];
    $patientsResult = queryDatabase($role, $sql, $params);

    $patients = [];
    while ($row = $patientsResult->fetch_assoc()) {
        $patients[] = $row;
    }

    // Fetch tests from the catalog
    $sql = "SELECT testCode, testName FROM testCatalogs";
    $testsResult = queryDatabase($role, $sql, $params);

    $tests = [];
    while ($row = $testsResult->fetch_assoc()) {
        $tests[] = $row;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $patientID = $_POST['patientID'];
        $testID = $_POST['testID'];
        $orderDate = $_POST['orderDate'];

        // Insert the new order into the database
        $sql = "INSERT INTO orders (patientID, labStaffOrderID, testID, orderDate) VALUES (?, ?, ?, ?)";
        $params = [
            'types' => 'iiis',
            'values' => [$patientID, $staffID, $testID, $orderDate]
        ];
        queryDatabase($role, $sql, $params);

        $message = "Order created successfully.";
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
        <div class="form-container mx-auto" style="max-width: 600px;">
            <form method="post" action="">
                <div class="form-group">
                    <label for="patientID">Select Patient:</label>
                    <select name="patientID" id="patientID" class="form-control" required>
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?php echo htmlspecialchars($patient['patientID']); ?>">
                                <?php echo htmlspecialchars($patient['firstName'] . ' ' . $patient['lastName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="testID">Select Test:</label>
                    <select name="testID" id="testID" class="form-control" required>
                        <?php foreach ($tests as $test): ?>
                            <option value="<?php echo htmlspecialchars($test['testID']); ?>">
                                <?php echo htmlspecialchars($test['testName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="orderDate">Order Date:</label>
                    <input type="date" name="orderDate" id="orderDate" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Order</button>
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