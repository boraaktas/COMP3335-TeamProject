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
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?> to the lab staff dashboard from MedTestLab.</h1>
    <label for="task">Task:</label>
            <select id="task" name="task" required>
                <option value="result_reporting" selected>Result reporting</option>
                <option value="tests">Tests</option>
            </select><br><br>

            <button type="submit">Execute</button>
            <br><br>
    <a href="logout.php">Logout</a>
</body>
</html>

<?php
// Include footer if needed
include('footer.php');
?>

