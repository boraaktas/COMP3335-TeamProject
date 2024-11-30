<?php
require_once "db.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    // Validate input fields
    if (!$email || !$password) {
        header("Location: index.html?error=All%20fields%20are%20required.");
        exit;
    }

    try {
        // Authenticate the user
        $authResponse = authenticateUser($email, $password);

        if ($authResponse['status']) {
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Store session details
            $_SESSION['userID'] = $authResponse['userID'];
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $authResponse['role'];
            $_SESSION['userName'] = $authResponse['userName'];
            $_SESSION['isLoggedIn'] = true;

            // Map roles to welcomes
            $pages = [
                'patient' => 'welcome_patient.php',
                'labStaff' => 'welcome_labstaff.php',
                'secretary' => 'welcome_secretary.php',
            ];

            // Redirect to the appropriate page
            if (array_key_exists($authResponse['role'], $pages)) {
                header("Location: " . $pages[$authResponse['role']]);
                exit;

            } else {
                header("Location: index.html?error=Unauthorized%20role.");
                exit;
            }
            
        } else {
            // Invalid credentials
            header("Location: index.html?error=" . urlencode($authResponse['message']));
            exit;
        }

    } catch (Exception $e) {
        // Handle database or server errors
        header("Location: index.html?error=An%20unexpected%20error%20occurred.");
        exit;
    }

} else {
    // If not a POST request, redirect to login page
    header("Location: index.html");
    exit;
}
?>