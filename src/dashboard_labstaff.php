<?php
session_start();

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'labStaff') {
    header("Location: unauthorized.php");
    exit;
}

// Generate CSRF token for form submission
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Staff Dashboard</title>
    <script>
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
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?> to the Lab Staff Dashboard</h1>
    <form action="labstaff_functions.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required><br><br>

        <label for="task">Task:</label>
        <select id="task" name="task" onchange="updateInputField()" required>
            <option value="result_reporting" selected>Result Reporting</option>
            <option value="tests">Tests</option>
        </select><br><br>

        <label id="input-label" for="dynamic-input">Patient Email:</label>
        <input type="text" id="dynamic-input" name="email" placeholder="Enter email" required><br><br>

        <button type="submit">Execute</button>
    </form>

    <a href="logout.php">Logout</a>
</body>
</html>