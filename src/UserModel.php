<?php


class UserModel{


    private $db;
    public function connectDB($role = 'root') {
        require_once "db.php"; 
        $credentials = getDatabaseCredentials($role);

        $this->db = new mysqli(
            $credentials['host'],
            $credentials['username'],
            $credentials['password'],
            $credentials['dbname']
        );

        if ($this->db->connect_error) {
            die("Connection to Database failed: " . $this->db->connect_error);
        }

        return $this->db;
    }

    private function getAccTablename($accLevel){
        $table = "";
        switch ($accLevel) {
            case "patient":
                $table = "patients";
                break;
            case "lab_staff":
                $table = "staffs";
                break;
            case "secretary":
                $table = "staffs";
                break;
        }
        return $table;  
    }

    public function getUserByUserName($email, $accLevel){

        $table = $this->getAccTablename($accLevel);

        $stmt = $this->db->prepare("SELECT * FROM `$table` WHERE email=?");

        if ($stmt === false) {
            die("Error preparing statement: " . $this->db->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); 
        } else {
            return null;
        }
    }

    // Has no purpose yet. Idea behind it is, if a staff or secretary is logged in he/she can add patients
    public function addPatient($name, $surname, $phone, $birthdate ,$email ,$password, $accLevel){


        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID); //Argon2i is very safe

        $db = $this->connectDB();

        $stmt = $db->prepare("INSERT INTO `patients` (name, surname, phoneNo, birthdate, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === false) {
            die("Error preparing statement: " . $db->error);
        }

        $stmt->bind_param("ssssss", $name, $surname, $phone, $birthdate, $email, $hashedPassword, $accLevel);
        $stmt->execute();

    }


    public function authenticate($email, $password, $accLevel){
        $user = $this-> getUserByUserName($email, $accLevel);
        if($user && password_verify($password,  $user['password'])){ // Checks if the Input password is the same as the hash in the DB
            return true;    
        }else{
            return false;
        }
    }
}