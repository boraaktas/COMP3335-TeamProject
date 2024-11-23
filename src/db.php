<?php
// Database connection parameters
$host = "percona";
$dbname = "comp3335_database";
$dbuser = "root";
$dbpass = "mypassword";

// Function to establish a database connection
function getConnection() {
    $conn = new mysqli($GLOBALS['host'], $GLOBALS['dbuser'], $GLOBALS['dbpass'], $GLOBALS['dbname']);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to authenticate user
function authenticateUser($email, $password) {
    $conn = getConnection();

    // Query to fetch user credentials
    $sql = "SELECT u.userID, u.password, r.roleName 
            FROM users u 
            JOIN user_roles ur ON u.userID = ur.userID 
            JOIN roles r ON ur.roleID = r.roleID 
            WHERE u.email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            return [
                'status' => true,
                'userID' => $user['userID'],
                'role' => $user['roleName']
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Invalid password.'
            ];
        }
    } else {
        return [
            'status' => false,
            'message' => 'User not found.'
        ];
    }
}
?>