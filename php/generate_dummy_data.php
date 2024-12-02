<?php
// generate_dummy_data.php

ini_set(option: 'display_errors', value: 1);
ini_set(option: 'display_startup_errors', value: 1);
error_reporting(error_level: E_ALL);

// Function to generate hashed password and IV
function generate_b_crypted_password($password): string {

    $b_crypted_password = password_hash(password: $password, algo: PASSWORD_BCRYPT);

    return $b_crypted_password;
}

function find_user_id_by_email($conn, $email): mixed {
    $stmt = $conn->prepare("SELECT userID FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 1) {
        return $result->fetch_assoc()['userID'];
    } else {
        echo "User not found with email: $email\n";
        echo "Aborting script.\n";
        exit(1);
    }
}

function find_testID_by_testCode($conn, $testCode): mixed {
    $stmt = $conn->prepare("SELECT testID FROM testCatalogs WHERE testCode = ?");
    $stmt->bind_param("i", $testCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    switch ($result->num_rows) {
        case 1:
            return $result->fetch_assoc()['testID'];
        default:
            echo "Test not found with testCode: $testCode\n";
            echo "Aborting script.\n";
            exit(1);
    }
}

function find_orderID_by_patientEmail_and_testCode($conn, $patientEmail, $testCode): mixed {
    $stmt = $conn->prepare("SELECT orders.orderID FROM orders
        JOIN users ON orders.patientID = users.userID
        JOIN testCatalogs ON orders.testID = testCatalogs.testID
        WHERE users.email = ? AND testCatalogs.testCode = ?");
    $stmt->bind_param("si", $patientEmail, $testCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    switch ($result->num_rows) {
        case 1:
            return $result->fetch_assoc()['orderID'];
        default:
            echo "Order not found with patientEmail: $patientEmail and testCode: $testCode\n";
            echo "Aborting script.\n";
            exit(1);
    }

}

function generate_users_data($conn): void {

    // Prepare users data
    $users = [
        [
            'role' => 'patient',
            'email' => 'patient_1@gmail.com',
            'password' => '123456',
            'patientSSN' => '123-45-6789',
            'firstName' => 'patient_1_firstName',
            'lastName' => 'patient_1_lastName',
            'birthDate' => '1990-01-11',
            'phoneNo' => '555-1234',
            'insuranceType' => 'Standard Insurance'
        ],

        [
            'role' => 'patient',
            'email' => 'patient_2@gmail.com',
            'password' => '123456',
            'patientSSN' => '987-65-4321',
            'firstName' => 'patient_2_firstName',
            'lastName' => 'patient_2_lastName',
            'birthDate' => '1995-02-22',
            'phoneNo' => '555-5678',
            'insuranceType' => 'No Insurance'
        ],

        [
            'role' => 'patient',
            'email' => 'patient_3@gmail.com',
            'password' => '123456',
            'patientSSN' => '543-21-9876',
            'firstName' => 'patient_3_firstName',
            'lastName' => 'patient_3_lastName',
            'birthDate' => '2000-03-01',
            'phoneNo' => '555-9101',
            'insuranceType' => 'Gold Insurance'
        ],

        [
            'role' => 'labStaff',
            'email' => 'labStaff_1@gmail.com',
            'password' => '123456',
            'firstName' => 'LabStaff_1_firstName',
            'lastName' => 'LabStaff_1_lastName',
            'phoneNo' => '555-5678',
        ],

        [
            'role' => 'labStaff',
            'email' => 'labStaff_2@gmail.com',
            'password' => '123456',
            'firstName' => 'LabStaff_2_firstName',
            'lastName' => 'LabStaff_2_lastName',
            'phoneNo' => '555-5678',
        ],

        [
            'role' => 'secretary',
            'email' => 'secretary_1@gmail.com',
            'password' => '123456',
            'firstName' => 'Secretary_1_firstName',
            'lastName' => 'Secretary_1_lastName',
            'phoneNo' => '555-9101',
        ],

        [
            'role' => 'secretary',
            'email' => 'secretary_2@gmail.com',
            'password' => '123456',
            'firstName' => 'Secretary_2_firstName',
            'lastName' => 'Secretary_2_lastName',
            'phoneNo' => '555-9101',
        ],
    ];

    // Insert users using stored procedures
    foreach ($users as $userData) {
        try {
            $b_crypted_password = generate_b_crypted_password(password: $userData['password']);

            if ($userData['role'] === 'patient') {
                $stmt = $conn->prepare("CALL insertPatient(?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $userData['email'],
                    $b_crypted_password,
                    $userData['patientSSN'],
                    $userData['firstName'],
                    $userData['lastName'],
                    $userData['birthDate'],
                    $userData['phoneNo'],
                    $userData['insuranceType'],
                    $userData['role'],
                ]);
                $stmt->close();
            } 
            // if the user role is labStaff or secretary
            elseif (($userData['role'] === 'labStaff') || ($userData['role'] === 'secretary')) {
                $stmt = $conn->prepare("CALL insertStaff(?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $userData['email'],
                    $b_crypted_password,
                    $userData['firstName'],
                    $userData['lastName'],
                    $userData['phoneNo'],
                    $userData['role'],
                ]);
                $stmt->close();
            } 
            else {
                throw new Exception(message: "Invalid user role.");
            }
            echo "User inserted successfully.\n";

        } catch (Exception $e) {
            // raise an error and stop the script
            echo "\n\n\n!!! Error inserting user: " . $e->getMessage() . "\n";
            echo "!!!!!!!! Aborting script. !!!!!!!!\n\n\n";
            exit(1);
        }
    }
    echo "All users inserted successfully.\n";
}

function generate_testCatalogs_data($conn): void {

    // Prepare test catalog data
    $testCatalog = [
        [
            'testCode' => 9001,
            'testName' => 'Blood Test',
            'testCost' => 100.00,
            'testDescription' => 'A test to check the blood',
        ],

        [
            'testCode' => 9002,
            'testName' => 'Urine Test',
            'testCost' => 50.00,
            'testDescription' => 'A test to check the urine',
        ],

        [
            'testCode' => 9003,
            'testName' => 'X-Ray',
            'testCost' => 200.00,
            'testDescription' => 'A test to check the bones',
        ],

        [
            'testCode' => 9004,
            'testName' => 'MRI',
            'testCost' => 500.00,
            'testDescription' => 'A test to check the brain',
        ],

        [
            'testCode' => 9005,
            'testName' => 'CT Scan',
            'testCost' => 400.00,
            'testDescription' => 'A test to check the body',
        ],

        [
            'testCode' => 9006,
            'testName' => 'Ultrasound',
            'testCost' => 300.00,
            'testDescription' => 'A test to check the baby',
        ],

        [
            'testCode' => 9007,
            'testName' => 'ECG',
            'testCost' => 150.00,
            'testDescription' => 'A test to check the heart',
        ],

        [
            'testCode' => 9008,
            'testName' => 'Endoscopy',
            'testCost' => 250.00,
            'testDescription' => 'A test to check the stomach',
        ]
    ];

    // Insert test catalog data
    foreach ($testCatalog as $testData) {
        try {
            $stmt = $conn->prepare("INSERT INTO testCatalogs (testCode, testName, testCost, testDescription) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $testData['testCode'],
                $testData['testName'],
                $testData['testCost'],
                $testData['testDescription'],
            ]);
            $stmt->close();
            echo "Test inserted successfully.\n";

        } catch (Exception $e) {
            // raise an error and stop the script
            echo "\n\n\n!!! Error inserting test: " . $e->getMessage() . "\n";
            echo "!!!!!!!! Aborting script. !!!!!!!!\n\n\n";
            exit(1);
        }
    }
    echo "All tests inserted successfully.\n";
}

function generate_acceptedInsurances_data($conn): void {

    // Prepare accepted insurance data
    $acceptedInsurance = [
        [
            'insuranceType' => 'Standard Insurance',
            'discountRate' => 0.10,
        ],

        [
            'insuranceType' => 'Premium Insurance',
            'discountRate' => 0.20,
        ],

        [
            'insuranceType' => 'Gold Insurance',
            'discountRate' => 1.00,
        ],
    ];

    // Insert accepted insurance data
    foreach ($acceptedInsurance as $insuranceData) {
        try {
            $stmt = $conn->prepare("INSERT INTO acceptedInsurances (insuranceType, discountRate) VALUES (?, ?)");
            $stmt->execute([
                $insuranceData['insuranceType'],
                $insuranceData['discountRate'],
            ]);
            $stmt->close();
            echo "Insurance inserted successfully.\n";

        } catch (Exception $e) {
            // raise an error and stop the script
            echo "\n\n\n!!! Error inserting insurance: " . $e->getMessage() . "\n";
            echo "!!!!!!!! Aborting script. !!!!!!!!\n\n\n";
            exit(1);
        }
    }
    echo "All insurances inserted successfully.\n";
}

function generate_orders_data($conn): void {

    // Prepare test orders data
    $testOrders = [
      
        [
            'patientID' => find_user_id_by_email($conn, 'patient_1@gmail.com'),
            'labStaffOrderID' => find_user_id_by_email($conn, 'labStaff_1@gmail.com'),
            'testID' => find_testID_by_testCode($conn, 9001),
            'orderDate' => '2020-11-01'
        ],

        [
            'patientID' => find_user_id_by_email($conn, 'patient_1@gmail.com'),
            'labStaffOrderID' => find_user_id_by_email($conn, 'labStaff_2@gmail.com'),
            'testID' => find_testID_by_testCode($conn, 9002),
            'orderDate' => '2020-11-02'
        ],

        [
            'patientID' => find_user_id_by_email($conn, 'patient_2@gmail.com'),
            'labStaffOrderID' => find_user_id_by_email($conn, 'labStaff_1@gmail.com'),
            'testID' => find_testID_by_testCode($conn, 9003),
            'orderDate' => '2020-11-03'
        ],

        [
            'patientID' => find_user_id_by_email($conn, 'patient_3@gmail.com'),
            'labStaffOrderID' => find_user_id_by_email($conn, 'labStaff_1@gmail.com'),
            'testID' => find_testID_by_testCode($conn, 9004),
            'orderDate' => '2020-11-04'
        ]
    ];

    // Insert test orders data
    foreach ($testOrders as $testOrderData) {
        try {
            $stmt = $conn->prepare("INSERT INTO orders (patientID, labStaffOrderID, testID, orderDate) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $testOrderData['patientID'],
                $testOrderData['labStaffOrderID'],
                $testOrderData['testID'],
                $testOrderData['orderDate'],
            ]);
            $stmt->close();
            echo "Test order inserted successfully.\n";

        } catch (Exception $e) {
            // raise an error and stop the script
            echo "\n\n\n!!! Error inserting test order: " . $e->getMessage() . "\n";
            echo "!!!!!!!! Aborting script. !!!!!!!!\n\n\n";
            exit(1);
        }
    }
    echo "All test orders inserted successfully.\n";
}

function generate_appointments_data($conn): void { 

    // Prepare appointment data
    $appointments = [
        [
            'orderID' => find_orderID_by_patientEmail_and_testCode($conn, 'patient_1@gmail.com', 9001),
            'secretaryID' => find_user_id_by_email($conn, 'secretary_1@gmail.com'),
            'appointmentDateTime' => '2020-11-10 10:00:00',
            'status' => 'Pending Result',
        ],
    ];

    // Insert appointment data
    foreach ($appointments as $appointmentData) {
        try {
            $stmt = $conn->prepare("INSERT INTO appointments (orderID, secretaryID, appointmentDateTime) VALUES (?, ?, ?)");
            $stmt->execute([
                $appointmentData['orderID'],
                $appointmentData['secretaryID'],
                $appointmentData['appointmentDateTime'],
            ]);
            $stmt->close();
            echo "Appointment inserted successfully.\n";

            if ($appointmentData['status'] === 'Pending Result') {
                $stmt = $conn->prepare("UPDATE orders SET orderStatus = 'Pending Result' WHERE orderID = ?");
                $stmt->execute([$appointmentData['orderID']]);
                $stmt->close();
                echo "Order status updated successfully, status: Pending Result for orderID: " . $appointmentData['orderID'] . "\n";
            }

        } catch (Exception $e) {
            // raise an error and stop the script
            echo "\n\n\n!!! Error inserting appointment: " . $e->getMessage() . "\n";
            echo "!!!!!!!! Aborting script. !!!!!!!!\n\n\n";
            exit(1);
        }
    }
    echo "All appointments inserted successfully.\n";
}

function generate_results_data($conn): void {

    // Prepare results data
    $results = [
        [
            'orderID' => find_orderID_by_patientEmail_and_testCode($conn, 'patient_1@gmail.com', 9001),
            'labStaffResultID' => find_user_id_by_email($conn, 'labStaff_2@gmail.com'),
            'interpretation' => 'Normal',
            'reportURL' => 'https://www.example.com/report1',
        ],
    ];

    // Insert results data
    foreach ($results as $resultData) {
        try {
            $stmt = $conn->prepare("INSERT INTO results (orderID, labStaffResultID, interpretation, reportURL) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $resultData['orderID'],
                $resultData['labStaffResultID'],
                $resultData['interpretation'],
                $resultData['reportURL'],
            ]);
            $stmt->close();
            echo "Result inserted successfully.\n";

        } catch (Exception $e) {
            // raise an error and stop the script
            echo "\n\n\n!!! Error inserting result: " . $e->getMessage() . "\n";
            echo "!!!!!!!! Aborting script. !!!!!!!!\n\n\n";
            exit(1);
        }
    }
    echo "All results inserted successfully.\n";
}

function main(): void {
    // get this from the environment variable
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $dbname = getenv('DB_NAME');
    $user = getenv('DB_ADMIN');
    $pass = getenv('DB_ADMIN_PASSWORD');

    $encryption_key = getenv('ENCRYPTION_KEY');
    $cypher_method = getenv('CYPHER_METHOD');

    // Retry mechanism
    $maxRetries = 20;
    $attempt = 0;

    while ($attempt < $maxRetries) {
        try {
            $conn = new mysqli($host, $user, $pass, $dbname);
            echo "Connected to the database successfully.\n";
            break;
        } catch (Exception $e) {
            $attempt++;
            echo "Connection failed: " . $e->getMessage() . "\n";
            sleep(2); // Wait before retrying
        }
    }

    if ($attempt == $maxRetries) {
        echo "Could not connect to the database after $maxRetries attempts.\n";
        exit(1);
    }

    // If there is a data in the database, do no generate dummy data
    $result = $conn->query("SELECT * FROM users");
    if ($result->num_rows > 0) {
        echo "Data already exists in the database. Skipping dummy data generation.\n";
    }

    else {
        // Generate test catalog data
        generate_testCatalogs_data($conn);
        echo "Test catalog inserted successfully.\n";

        // Generate accepted insurances data
        generate_acceptedInsurances_data($conn);
        echo "Accepted insurances inserted successfully.\n";

        // Generate users data
        generate_users_data($conn);
        echo "Users inserted successfully.\n";

        // Generate test orders data
        generate_orders_data($conn);
        echo "Test orders inserted successfully.\n";

        // Generate appointments data
        generate_appointments_data($conn);
        echo "Appointments inserted successfully.\n";

        // Generate results data
        generate_results_data($conn);
        echo "Results inserted successfully.\n";

        // Close the database connection
        $conn->close();

        echo "All dummy data generated successfully.\n";
    }
}

main();