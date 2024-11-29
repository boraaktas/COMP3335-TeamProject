CREATE DATABASE IF NOT EXISTS comp3335_database;
USE comp3335_database;
CREATE TABLE IF NOT EXISTS patients (
    patientID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    birthdate DATE NOT NULL,
    phoneNo VARCHAR(255),
    email VARCHAR(255) NOT NULL UNIQUE, 
    password VARCHAR(255) NOT NULL,
    insuranceType VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS staffs (
    staffID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    phoneNo VARCHAR(255),
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS testCatalogs (
    testCode INT AUTO_INCREMENT PRIMARY KEY,
    testName VARCHAR(255) NOT NULL,
    cost INT NOT NULL,
    testDescription TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS orders (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    patientID INT NOT NULL,
    testCode INT NOT NULL,
    orderDate DATE NOT NULL,
    orderStatus VARCHAR(255) NOT NULL,
    FOREIGN KEY (patientID) REFERENCES patients(patientID),
    FOREIGN KEY (testCode) REFERENCES testCatalogs(testCode)
);

CREATE TABLE IF NOT EXISTS appointments (
    appointmentID INT AUTO_INCREMENT PRIMARY KEY,
    patientID INT NOT NULL,
    orderID INT NOT NULL,
    secretaryID INT NOT NULL,
    samplingType VARCHAR(255),
    appointmentDate DATE NOT NULL,
    appointmentTime TIME NOT NULL,
    FOREIGN KEY (patientID) REFERENCES patients(patientID),
    FOREIGN KEY (orderID) REFERENCES orders(orderID),
    FOREIGN KEY (secretaryID) REFERENCES staffs(staffID)
);

CREATE TABLE IF NOT EXISTS testResults (
    resultID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT NOT NULL,
    reportURL VARCHAR(255) NOT NULL,
    interpretation TEXT NOT NULL,
    labStaffID INT NOT NULL,
    FOREIGN KEY (orderID) REFERENCES orders(orderID),
    FOREIGN KEY (labStaffID) REFERENCES staffs(staffID)
);

CREATE TABLE IF NOT EXISTS billing (
    billingID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT NOT NULL,
    billedAmount INT NOT NULL,
    paymentStatus VARCHAR(255) NOT NULL,
    insuranceClaimStatus VARCHAR(255) NOT NULL,
    FOREIGN KEY (orderID) REFERENCES orders(orderID)
);

-- Create the dynamic user-role mapping structure

-- Users table
CREATE TABLE IF NOT EXISTS users (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Roles table
CREATE TABLE IF NOT EXISTS roles (
    roleID INT AUTO_INCREMENT PRIMARY KEY,
    roleName VARCHAR(255) NOT NULL UNIQUE
);

-- User-Role mapping table
CREATE TABLE IF NOT EXISTS user_roles (
    userID INT NOT NULL,
    roleID INT NOT NULL,
    FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE,
    FOREIGN KEY (roleID) REFERENCES roles(roleID) ON DELETE CASCADE,
    PRIMARY KEY (userID, roleID)
);

-- Populate roles
INSERT INTO roles (roleName) VALUES ('labStaff'), ('secretary'), ('patient');

DELIMITER $$

CREATE TRIGGER before_insert_patients
BEFORE INSERT ON patients
FOR EACH ROW
BEGIN
    DECLARE user_id INT;

    -- Insert into the users table
    INSERT INTO users (email, password) 
    VALUES (NEW.email, NEW.password);

    -- Get the inserted userID
    SET user_id = LAST_INSERT_ID();

    -- Map the user to the patient role
    INSERT INTO user_roles (userID, roleID)
    VALUES (user_id, (SELECT roleID FROM roles WHERE roleName = 'patient'));

    -- If any step fails, rollback will automatically prevent adding the row to patients
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER before_insert_staffs_lab
BEFORE INSERT ON staffs
FOR EACH ROW
BEGIN
    DECLARE user_id INT;

    -- Insert into the users table
    INSERT INTO users (email, password) 
    VALUES (NEW.email, NEW.password);

    -- Get the inserted userID
    SET user_id = LAST_INSERT_ID();

    -- Assign role based on the staff role
    IF NEW.role = 'labStaff' THEN
        INSERT INTO user_roles (userID, roleID)
        VALUES (user_id, (SELECT roleID FROM roles WHERE roleName = 'labStaff'));
    ELSEIF NEW.role = 'secretary' THEN
        INSERT INTO user_roles (userID, roleID)
        VALUES (user_id, (SELECT roleID FROM roles WHERE roleName = 'secretary'));
    ELSE
        -- Raise an error for an invalid role
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid role provided for staff';
    END IF;

    -- If any step fails, rollback will automatically prevent adding the row to staffs
END$$

DELIMITER ;

-- Create roles at the database level
CREATE ROLE labStaff;
CREATE ROLE secretary;
CREATE ROLE patient;

-- Grant privileges to roles

-- labStaff can read/write to testResults table and read orders
GRANT SELECT, INSERT, UPDATE ON comp3335_database.testResults TO labStaff;
GRANT SELECT ON comp3335_database.orders TO labStaff;

-- secretary can read/write to appointments table, read orders, and read/write to billing table
GRANT SELECT, INSERT, UPDATE ON comp3335_database.appointments TO secretary;
GRANT SELECT ON comp3335_database.orders TO secretary;
GRANT SELECT, INSERT, UPDATE ON comp3335_database.billing TO secretary;

-- patient can read appointments, testResults, orders, and billing
GRANT SELECT ON comp3335_database.appointments TO patient;
GRANT SELECT ON comp3335_database.testResults TO patient;
GRANT SELECT ON comp3335_database.orders TO patient;
GRANT SELECT ON comp3335_database.billing TO patient;

-- Create stored procedure for assigning roles dynamically
DELIMITER $$

CREATE PROCEDURE AssignUserRole(IN username VARCHAR(255))
BEGIN
    DECLARE roleName VARCHAR(255);

    -- Get the roleName for the user
    SELECT r.roleName 
    INTO roleName
    FROM users u
    JOIN user_roles ur ON u.userID = ur.userID
    JOIN roles r ON ur.roleID = r.roleID
    WHERE u.username = username;

    -- Dynamically set privileges based on the roleName
    IF roleName = 'labStaff' THEN
        SET ROLE labStaff;
    ELSEIF roleName = 'secretary' THEN
        SET ROLE secretary;
    ELSEIF roleName = 'patient' THEN
        SET ROLE patient;
    END IF;
END$$

DELIMITER ;

INSERT INTO patients (name, surname, birthdate, phoneNo, email, password, insuranceType) 
VALUES ('Alice', 'Chan', '1990-01-01', '12345678', 'alice@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$alVCanFaZmFlcVpKdzZseA$VpUsbrMKTzTUJvsujzQzGEa0GkKz5SDrwJCmUmH9nUg', 'AIA'),
       ('Bob', 'Lee', '1991-02-02', '87654321', 'bob@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$alVCanFaZmFlcVpKdzZseA$VpUsbrMKTzTUJvsujzQzGEa0GkKz5SDrwJCmUmH9nUg', 'AXA'),
       ('Charlie', 'Wong', '1992-03-03', '12345678', 'charlie@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$alVCanFaZmFlcVpKdzZseA$VpUsbrMKTzTUJvsujzQzGEa0GkKz5SDrwJCmUmH9nUg', 'Prudential');

INSERT INTO staffs (name, surname, phoneNo, email, password, role)
VALUES ('David', 'Chan', '12345678', 'david@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$alVCanFaZmFlcVpKdzZseA$VpUsbrMKTzTUJvsujzQzGEa0GkKz5SDrwJCmUmH9nUg', 'labStaff'),
       ('Eva', 'Lee', '87654321', 'eva@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$alVCanFaZmFlcVpKdzZseA$VpUsbrMKTzTUJvsujzQzGEa0GkKz5SDrwJCmUmH9nUg', 'secretary'),
       ('Frank', 'Wong', '12345678', 'frank@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$alVCanFaZmFlcVpKdzZseA$VpUsbrMKTzTUJvsujzQzGEa0GkKz5SDrwJCmUmH9nUg', 'labStaff');

INSERT INTO testCatalogs (testName, cost, testDescription) 
VALUES ('Blood Test', 100, 'Test for blood'),
       ('Urine Test', 200, 'Test for urine'),
       ('X-ray', 300, 'X-ray test');

INSERT INTO orders (patientID, testCode, orderDate, orderStatus) 
VALUES (1, 1, '2021-01-01', 'Pending'),
       (2, 2, '2021-02-02', 'Completed'),
       (3, 3, '2021-03-03', 'Pending');

INSERT INTO appointments (patientID, orderID, secretaryID, appointmentDate, appointmentTime) 
VALUES (1, 1, 1, '2021-01-02', '10:00:00'),
       (2, 2, 2, '2021-02-03', '11:00:00'),
       (3, 3, 3, '2021-03-04', '12:00:00');

INSERT INTO testResults (orderID, reportURL, interpretation, labStaffID)
VALUES (1, 'http://example.com/report1', 'Normal', 1),
       (2, 'http://example.com/report2', 'Abnormal', 2),
       (3, 'http://example.com/report3', 'Normal', 3);

INSERT INTO billing (orderID, billedAmount, paymentStatus, insuranceClaimStatus)
VALUES (1, 100, 'Paid', 'Claimed'),
       (2, 200, 'Unpaid', 'Not Claimed'),
       (3, 300, 'Paid', 'Claimed');

-- exporter user for MySQL metrics collection by mysqld-exporter
CREATE USER IF NOT EXISTS 'exporter'@'%' IDENTIFIED BY 'exporterpassword';
GRANT PROCESS, REPLICATION CLIENT ON *.* TO 'exporter'@'%';
FLUSH PRIVILEGES;

