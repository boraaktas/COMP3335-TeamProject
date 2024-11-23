<?php
/*
require_once "UserModel.php";


session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    $email = $_POST['email'];
    $password = $_POST['password'];
    $accLevel = $_POST['access_level'];

    $userModel = new UserModel();

    $userModel->connectDB('root');
    if ($userModel->authenticate($email, $password, $accLevel)) {
        // Store session information
        $_SESSION['email'] = $email;
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['access_level'] = $accLevel;

        // Reconnect as the specific role user
        $userModel->connectDB($accLevel);

        // Redirect to the appropriate dashboard
        if ($accLevel == "lab_staff") {
            header("Location: dashboard_labstaff.php");
        } elseif ($accLevel == "patient") {
            header("Location: dashboard_patient.php");
        } elseif ($accLevel == "secretary") {
            header("Location: dashboard_secretary.php");
        }
        exit;
    }else{
        header("Location: index.html?error=Invalid%20username%20or%20password");
        exit;
    }
}else{
    include "index.html";
}
    */


    require_once "db.php";
    session_start();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $accLevel = $_POST['access_level'];
    
        try {
            // Authenticate the user
            $authResponse = authenticateUser($username, $password);
    
            if ($authResponse['status']) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
    
                // Store session details
                $_SESSION['username'] = $username;
                $_SESSION['isLoggedIn'] = true;
                $_SESSION['access_level'] = $accLevel;
                $_SESSION['userID'] = $authResponse['userID'];
    
                // Map access levels to dashboards
                $dashboards = [
                    'lab_staff' => 'dashboard_labstaff.php',
                    'patient' => 'dashboard_patient.php',
                    'secretary' => 'dashboard_secretary.php',
                ];
    
                // Redirect based on access level
                if (array_key_exists($accLevel, $dashboards)) {
                    header("Location: " . $dashboards[$accLevel]);
                    exit;
                } else {
                    // Handle unexpected access levels
                    $_SESSION['error'] = 'Invalid access level.';
                    header("Location: index.html");
                    exit;
                }
            } else {
                // Invalid credentials
                $_SESSION['error'] = $authResponse['message'];
                header("Location: index.html");
                exit;
            }
        } catch (Exception $e) {
            // Handle database or connection errors
            $_SESSION['error'] = 'An error occurred. Please try again later.';
            header("Location: index.html");
            exit;
        }
    } else {
        // If not a POST request, load the login page
        include "index.html";
    }
    