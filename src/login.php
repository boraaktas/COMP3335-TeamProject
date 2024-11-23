<?php

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