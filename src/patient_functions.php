<?php

require "db.php";

session_start();


if ($_POST['task'] == "test_orders") {
    $passwordHash = password_hash($_POST['password'], PASSWORD_ARGON2ID);
    $stmt = $conn->prepare("SELECT * FROM `orders` WHERE PatientID = (SELECT PatientID FROM `patients` WHERE surname = ?)");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    // The visualization of the results is just basic right now can be done more beautiful if necessary
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $field1name = $row["orderID"];
            $field2name = $row["patientID"];
            $field3name = $row["testCode"];
            $field4name = $row["orderDate"];
            $field5name = $row["orderStatus"]; 
    
            echo '<tr> 
                      <td>'.$field1name.'</td> 
                      <td>'.$field2name.'</td> 
                      <td>'.$field3name.'</td> 
                      <td>'.$field4name.'</td> 
                      <td>'.$field5name.'</td> 
                  </tr>';
        }
        $result->free();
    }

}elseif ($_POST['task'] == "view_results") {
    //Not finished yet needs to be extended
}