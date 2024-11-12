<?php

require_once "UserModel.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $userModel = new UserModel();
    $user = $userModel->getUserByUserName($username);
    
    if ($user && password_verify($password, $user['password'])){

        $_SESSION['username'] = $user['username'];
        $_SESSION['isLoggedIn'] = true;

        header('Location: dashboard.php');
        exit;

    }else{
        header("Location: index.html?error=Invalid%20username%20or%20password");
        exit;
    }
}else{
    include "index.html";
}


?>