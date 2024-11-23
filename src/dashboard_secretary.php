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
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>to the secretary dashboard from MedTestLab</h1>
    <form action="secretary_functions.php" method="post">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required><br><br>

    <label for="task">Task:</label>
            <select id="task" name="task" required>
                <option value="result_printing" selected>Result printing</option>
                <option value="appointment">Appointment</option>
                <option value="billing">Billing</option>
            </select><br><br>
        <label for="email">Patient email: </label>
        <input type="text" id="email" name="email" placeholder="Enter mail" required><br><br>

        <button type="submit">Execute</button>
        <br><br>
    </form>
    <a href="logout.php">Logout</a>
</body>
</html>

<?php
// Include footer if needed
include('footer.php');
?>

