<?php
session_start();

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'secretary') {
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
    <title>Secretary Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?> to the Secretary Dashboard</h1>
    <form action="secretary_functions.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required><br><br>

        <label for="task">Task:</label>
        <select id="task" name="task" required>
            <option value="result_printing" selected>Result Printing</option>
            <option value="appointment">Appointment</option>
            <option value="billing">Billing</option>
        </select><br><br>

        <label for="email">Patient Email:</label>
        <input type="text" id="email" name="email" placeholder="Enter email" required><br><br>

        <button type="submit">Execute</button>
    </form>
    <a href="logout.php">Logout</a>
</body>
</html>