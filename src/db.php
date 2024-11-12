<?php

$host = "percona";
$dbname = "comp3335_database";
$dbuser = "root";
$dbpass = "mypassword";


$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if(!$conn->connect_error) {
    die("Connection to Database failed!\n". $conn->connect_error);
}

?>