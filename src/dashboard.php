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
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>.</h1>
    <a href="logout.php">Logout</a>
</body>
</html>

<?php
// Include footer if needed
include('footer.php');
?>

