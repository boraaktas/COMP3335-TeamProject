<?php
session_start();

// If not logged in, redirect to login page
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
    header("Location: login.php");
    exit;
}

// Include common header part
include('header.php');

// Display the actual dashboard content
?>

<!--html output-->
<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?> to the patient dashboard from MedTestLab.</h1>
    <form action="patient_functions.php" method="post">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required><br><br>

        <label for="task">Task:</label>
                <select id="task" name="task" required>
                    <option value="test_orders" selected>Test orders</option>
                    <option value="view_results">View results</option>
                    <option value="bills">Bills</option>
                </select><br><br>

                <button type="submit">Execute</button>
                <br><br>
    </form>
    <a href="logout.php">Logout</a>
</html>

<?php
// Include footer if needed
include('footer.php');
?>

