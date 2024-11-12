<?php


class UserModel{

    private $db;


    public function __construct() {
        require_once "db.php"; 
        $this->db = $db;
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
                return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM `$table` WHERE surname=?");

        if ($stmt === false) {
            die("Error preparing statement: " . $this->db->error);
        }

        $stmt->bind_param("s", $this->$username);
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
        if($user && password_verify($password, $user['password'])){
            return true;    
        }else{
            return false;
        }

    }
}

?>