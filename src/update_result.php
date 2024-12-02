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

$pageTitle = 'Update Result';
$heading = 'Update an Existing Result';
$error = '';
$message = '';

try {
    // Fetch results created by this lab staff
    $sql = "SELECT orders.orderID, results.resultID
            FROM results 
            JOIN orders ON results.orderID = orders.orderID
            WHERE labStaffResultID = ?";
    $params = ['types' => 'i', 'values' => [$staffID]];
    $resultsResult = queryDatabase($role, $sql, $params);

    $results = [];
    while ($row = $resultsResult->fetch_assoc()) {
        $results[] = $row;
    }

    // Handle form submissions
    if (isset($_POST['selectResult'])) {
        // Fetch selected result details
        $resultID = $_POST['resultID'];
        $sql = "SELECT * 
                FROM results 
                WHERE resultID = ?";
        $params = ['types' => 'i', 'values' => [$resultID]];
        $resultResult = queryDatabase($role, $sql, $params);
        $result = $resultResult->fetch_assoc();

    } elseif (isset($_POST['updateResult'])) {
        // Update the result
        $resultID = $_POST['resultID'];
        $interpretation = $_POST['interpretation'];
        $reportURL = $_POST['reportURL'];

        $sql = "UPDATE results 
                SET interpretation = ?, reportURL = ? 
                WHERE resultID = ?";
        $params = [
            'types' => 'ssi',
            'values' => [$interpretation, $reportURL, $resultID]
        ];
        queryDatabase($role, $sql, $params);

        $message = "Result updated successfully.";
        unset($result); // Clear the selected result

    } elseif (isset($_POST['deleteResult'])) {
        // Delete the result
        $resultID = $_POST['resultID'];

        $sql = "DELETE 
                FROM results 
                WHERE resultID = ?";
        $params = ['types' => 'i', 'values' => [$resultID]];
        queryDatabase($role, $sql, $params);

        $message = "Result deleted successfully.";
        unset($result); // Clear the selected result

        // Fetch updated results
        $sql = "SELECT orders.orderID, results.resultID
                FROM results 
                JOIN orders ON results.orderID = orders.orderID
                WHERE labStaffResultID = ?";
        $params = ['types' => 'i', 'values' => [$staffID]];
        $resultsResult = queryDatabase($role, $sql, $params);

        $results = [];
        while ($row = $resultsResult->fetch_assoc()) {
            $results[] = $row;
        }
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

        <?php if (!isset($result)): ?>
            <div class="form-container mx-auto" style="max-width: 600px;">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="resultID">Select Result:</label>
                        <select name="resultID" id="resultID" class="form-control" required>
                            <?php foreach ($results as $resultItem): ?>
                                <option value="<?php echo htmlspecialchars($resultItem['resultID']); ?>">
                                    Order ID: <?php echo htmlspecialchars($resultItem['orderID']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="selectResult" class="btn btn-primary btn-block">Select Result</button>
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
                    <input type="hidden" name="resultID" value="<?php echo htmlspecialchars($result['resultID']); ?>">

                    <div class="form-group">
                        <label for="orderID">Order ID: <?php echo htmlspecialchars($result['orderID']); ?></label>
                    </div>

                    <div class="form-group">
                        <label for="reportURL">Report URL:</label>
                        <input type="text" name="reportURL" id="reportURL" class="form-control" value="https://www.example.com/report1" readonly>
                    </div>

                    <div class="form-group">
                        <label for="interpretation">Interpretation:</label>
                        <textarea name="interpretation" id="interpretation" class="form-control" rows="5" required><?php echo htmlspecialchars($result['interpretation']); ?></textarea>
                    </div>

                    <button type="submit" name="updateResult" class="btn btn-primary btn-block">Update Result</button>
                    <button type="submit" name="deleteResult" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to delete this result?');">Delete Result</button>
                </form>
                <div class="back-link">
                    <a href="update_result.php" class="btn btn-secondary mt-3">Back</a>
                </div>
                <div class="logout-link">
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>