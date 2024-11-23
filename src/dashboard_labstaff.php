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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script>
        // JavaScript function to update the input field dynamically
        function updateInputField() {
            const task = document.getElementById('task').value;
            const inputLabel = document.getElementById('input-label');
            const inputField = document.getElementById('dynamic-input');

            if (task === "result_reporting") {
                inputLabel.textContent = "Patient Email:";
                inputField.name = "email";
                inputField.placeholder = "Enter email";
            } else if (task === "tests") {
                inputLabel.textContent = "Test Code:";
                inputField.name = "testCode";
                inputField.placeholder = "Enter test code";
            }
        }
    </script>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?> to the lab staff dashboard from MedTestLab.</h1>
    <form action="labstaff_functions.php" method="post">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required><br><br>

        <label for="task">Task:</label>
        <select id="task" name="task" onchange="updateInputField()" required>
            <option value="result_reporting" selected>Result reporting</option>
            <option value="tests">Tests</option>
        </select><br><br>

        <label id="input-label" for="dynamic-input">Patient Email:</label>
        <input type="text" id="dynamic-input" name="email" placeholder="Enter email" required><br><br>

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
