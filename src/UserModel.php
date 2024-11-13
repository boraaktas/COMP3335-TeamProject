<?php


class UserModel{


    public function connectDB(){
        require "db.php"; 
        return $conn;
    }

    public function getUserByUserName($username, $accLevel){
        $table = '';

        switch ($accLevel) {
            case "Patient":
                $table = "patients";
                break;
            case "Lab Staff":
                $table = "staffs";
                break;
            case "Secretary":
                $table = "staffs";
            default:
                $table = "patients";
        }

        $db = $this->connectDb();
        $stmt = $db->prepare("SELECT * FROM `$table` WHERE surname=?");

        if ($stmt === false) {
            die("Error preparing statement: " . $db->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); 
        } else {
            return null;
        }
    }


    public function authenticate($username, $password, $accLevel){
        $user = $this-> getUserByUserName($username, $accLevel);
        if($user && ($password == $user['password'])){ // Will be exchanged with the passowrd_verify function but that needs some preprocessing
            return true;    
        }else{
            return false;
        }

    }
}

?>