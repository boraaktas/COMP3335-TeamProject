<?php

// Function to establish a database connection with the given role
function getConnection($role) {
    
    $dbCredentials = [
        'admin' => [
            'host' => getenv('DB_HOST'),
            'username' => getenv('DB_ADMIN'),
            'password' => getenv('DB_ADMIN_PASSWORD')
        ],
        'patient' => [
            'host' => getenv('DB_HOST'),
            'username' => getenv('DB_PATIENT'),
            'password' => getenv('DB_PATIENT_PASSWORD')
        ],
        'labStaff' => [
            'host' => getenv('DB_HOST'),
            'username' => getenv('DB_LABSTAFF'),
            'password' => getenv('DB_LABSTAFF_PASSWORD')
        ],
        'secretary' => [
            'host' => getenv('DB_HOST'),
            'username' => getenv('DB_SECRETARY'),
            'password' => getenv('DB_SECRETARY_PASSWORD')
        ]
    ];

    // Check if the role exists in the dictionary
    if (!array_key_exists($role, $dbCredentials)) {
        throw new Exception("Role not found.");
    }

    // Create a new connection
    $conn = new mysqli(
        $dbCredentials[$role]['host'],
        $dbCredentials[$role]['username'],
        $dbCredentials[$role]['password'],
        'comp3335_database'
    );

    return $conn;
}

function queryDatabase($role, $sql, $params) {

    // Get the database connection with the given role
    $conn = getConnection($role);

    $stmt = $conn->prepare($sql);
    // if there are parameters to bind length of params bigger than 0
    if (count($params['values']) > 0) {
        $stmt->bind_param($params['types'], ...$params['values']);
    }
    $stmt->execute();

    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();

    return $result;
}

function get_user_role_firstName_lastName($userID) {
    // Query to fetch user role
    $sql = "SELECT roles.roleName
            FROM userRoles JOIN roles ON userRoles.roleID = roles.roleID
            WHERE userRoles.userID = ?";

    $result = queryDatabase('admin', $sql, ['types' => 'i', 'values' => [$userID]]);

    if ($result->num_rows !== 1) {
        throw new Exception("User not found.");
    }

    $role = $result->fetch_assoc()['roleName'];

    $userTable = ($role === 'labStaff' || $role === 'secretary') ? 'staffs' : 'patients';
    $IDName = ($role === 'labStaff' || $role === 'secretary') ? 'staffID' : 'patientID';

    $sql = "SELECT firstName, lastName
            FROM $userTable
            WHERE $IDName = ?";
    $result = queryDatabase('admin', $sql, ['types' => 'i', 'values' => [$userID]]);

    if ($result->num_rows !== 1) {
        throw new Exception("User not found.");
    }

    $user = $result->fetch_assoc();

    return [
        'role' => $role,
        'firstName' => $user['firstName'],
        'lastName' => $user['lastName']
    ];

}

// Function to authenticate user
function authenticateUser($email, $password) {

    // Query to fetch user credentials
    $sql = "SELECT *
            FROM users
            WHERE email = ?";

    // Execute the query with the admin role
    // it will be the only time we use the admin role, after this we will use the role of the user
    $result = queryDatabase('admin', $sql, ['types' => 's', 'values' => [$email]]);

    // Check if the user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if the b_crypt password matches the given password
        if (password_verify($password, $user['password'])) {
            
            // Get the user's role, first name, and last name
            $user_info = get_user_role_firstName_lastName($user['userID']);
            $role = $user_info['role'];
            $firstName = $user_info['firstName'];
            $lastName = $user_info['lastName'];
            
            return [
                'status' => true,
                'userID' => $user['userID'],
                'role' => $role,
                'userName' => $firstName . ' ' . $lastName
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