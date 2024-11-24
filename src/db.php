<?php

function getDatabaseCredentials($role) {
    $credentials = [
        'root' => [
            'host' => 'percona',
            'username' => 'root',
            'password' => 'mypassword',
            'dbname' => 'comp3335_database'
        ],
        'lab_staff' => [
            'host' => 'percona',
            'username' => 'root',
            'password' => 'mypassword',
            'dbname' => 'comp3335_database'
        ],
        'patient' => [
            'host' => 'percona',
            'username' => 'root',
            'password' => 'mypassword',
            'dbname' => 'comp3335_database'
        ],
        'secretary' => [
            'host' => 'percona',
            'username' => 'root',
            'password' => 'mypassword',
            'dbname' => 'comp3335_database'
        ]
    ];

    return $credentials[$role] ?? $credentials['root'];
}
    /*

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
        throw new Exception("Connection to Database failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to authenticate and assign role
function authenticateUser($email, $password) {
    $conn = getConnection();

    // Prepare the SQL statement to fetch user credentials
    $sql = "SELECT userID, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            // Call stored procedure to assign the user role dynamically
            $assignRoleSql = "CALL AssignUserRole(?)";
            $assignStmt = $conn->prepare($assignRoleSql);
            $assignStmt->bind_param("s", $email);
            if ($assignStmt->execute()) {
                return [
                    'status' => true,
                    'message' => 'Login successful. Role assigned dynamically.',
                    'userID' => $user['userID']
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Failed to assign role.'
                ];
            }
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

*/