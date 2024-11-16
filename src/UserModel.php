<?php


class UserModel{

    private function connectDB() {
        require "db.php"; 
        return $conn;
    }

    private function getAccTablename($accLevel){
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
        return $table;  
    }

    public function getUserByUserName($username, $accLevel){
        
        $table = $this->getAccTablename($accLevel);

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

    // Has no purpose yet. Idea behind it is, if a staff or secretary is logged in he/she can add patients
    public function addPatient($name, $surname, $phone, $birthdate ,$mail ,$password, $accLevel){


        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID); //Argon2i is very safe

        $db = $this->connectDB();

        $stmt = $db->prepare("INSERT INTO `patients` (name, surname, phoneNo, birthdate, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === false) {
            die("Error preparing statement: " . $db->error);
        }

        $stmt->bind_param("ssssss", $name, $surname, $phone, $birthdate, $mail, $hashedPassword, $accLevel);
        $stmt->execute();

    }


    public function authenticate($username, $password, $accLevel){
        $user = $this-> getUserByUserName($username, $accLevel);
        if($user && password_verify($password,  $user['password'])){ // Checks if the Input password is the same as the hash in the DB
            return true;    
        }else{
            return false;
        }
    }
}