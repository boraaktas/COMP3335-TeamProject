<?php

require_once "UserModel.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    $username = $_POST['username'];
    $password = $_POST['password'];
    $accLvevel = $_POST['access_level'];

    $userModel = new UserModel();

    if ($userModel->authenticate($username, $password, $accessLevel)) {
        $_SESSION['username'] = $username;
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['accessLevel'] = $accessLevel;

        header("Location: dashboard.php");
        exit;

    }else{
        header("Location: index.html?error=Invalid%20username%20or%20password");
        exit;
    }
}else{
    include "index.html";
}


?>