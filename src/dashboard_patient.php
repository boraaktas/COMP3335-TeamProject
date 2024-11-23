<?php
session_start();

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'patient') {
    header("Location: unauthorized.php");
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?> to the MedTestLab Patient Dashboard.</h1>
    <form action="patient_functions.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required><br><br>

        <label for="task">Task:</label>
        <select id="task" name="task" required>
            <option value="test_orders" selected>Test Orders</option>
            <option value="view_results">View Results</option>
            <option value="bills">Bills</option>
        </select><br><br>

        <button type="submit">Execute</button>
    </form>
    <a href="logout.php">Logout</a>
</body>
</html>