CREATE DATABASE IF NOT EXISTS comp3335_database;
USE comp3335_database;

-- TABLES:
-- Users table
CREATE TABLE IF NOT EXISTS users (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS roles (
    roleID INT AUTO_INCREMENT PRIMARY KEY,
    roleName VARCHAR(255) NOT NULL UNIQUE
);

-- Insert roles into the roles table
INSERT INTO roles (roleName)
VALUES ('labStaff'),
       ('secretary'),
       ('patient');

CREATE TABLE IF NOT EXISTS userRoles (
    userID INT PRIMARY KEY, -- Each user can have only one role
    roleID INT NOT NULL,
    FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE,
    FOREIGN KEY (roleID) REFERENCES roles(roleID) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS patients (
    patientID INT NOT NULL,
    patientSSN VARCHAR(255) NOT NULL UNIQUE,
    firstName VARCHAR(255) NOT NULL,
    lastName VARCHAR(255) NOT NULL,
    birthDate DATE NOT NULL,
    phoneNo VARCHAR(255),
    insuranceType VARCHAR(255) NOT NULL,
    FOREIGN KEY (patientID) REFERENCES users(userID) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS staffs (
    staffID INT NOT NULL,
    firstName VARCHAR(255) NOT NULL,
    lastName VARCHAR(255) NOT NULL,
    phoneNo VARCHAR(255),
    staffRole ENUM('labStaff', 'secretary') NOT NULL,
    FOREIGN KEY (staffID) REFERENCES users(userID) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS testCatalogs (
    testID INT AUTO_INCREMENT PRIMARY KEY,
    testCode INT NOT NULL UNIQUE,
    testName VARCHAR(255) NOT NULL UNIQUE,
    testCost INT NOT NULL,
    testDescription TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS orders (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    patientID INT NOT NULL,
    labStaffOrderID INT NOT NULL,
    testID INT NOT NULL,
    orderDate DATE NOT NULL,  -- Date when the order was placed
    -- Order status can be one of the following:
    -- Pending Appointment: Order is placed but no appointment is scheduled
    -- Pending Result: Sampling is done but no result is available
    -- Completed: Result is available
    orderStatus ENUM('Pending Appointment', 'Pending Result', 'Completed') NOT NULL DEFAULT 'Pending Appointment',
    FOREIGN KEY (patientID) REFERENCES patients(patientID) ON DELETE CASCADE,
    FOREIGN KEY (labStaffOrderID) REFERENCES staffs(staffID) ON DELETE CASCADE,
    FOREIGN KEY (testID) REFERENCES testCatalogs(testID) ON DELETE CASCADE
);

-- Appoinments are created, updated, and deleted by the secretaries
-- When an appointment is created, change the orderStatus to 'Pending Result'. And if it is deleted, change the orderStatus to 'Pending Appointment'
-- When an appointment is done, the secretary should update the orderStatus to 'Pending Result'
CREATE TABLE IF NOT EXISTS appointments (
    appointmentID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT NOT NULL,
    secretaryID INT NOT NULL,
    appointmentDateTime DATETIME NOT NULL,
    FOREIGN KEY (orderID) REFERENCES orders(orderID) ON DELETE CASCADE,
    FOREIGN KEY (secretaryID) REFERENCES staffs(staffID) ON DELETE CASCADE
);

-- When a test result is created, change the orderStatus to 'Completed', and if it is deleted, change the orderStatus to 'Pending Result'
-- If orderSatus of the order is not 'Pending Result', then the test result cannot be created
CREATE TABLE IF NOT EXISTS results (
    resultID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT NOT NULL,
    labStaffResultID INT,
    interpretation TEXT NOT NULL,
    reportURL VARCHAR(255) NOT NULL,
    FOREIGN KEY (orderID) REFERENCES orders(orderID) ON DELETE CASCADE,
    FOREIGN KEY (labStaffResultID) REFERENCES staffs(staffID) ON DELETE CASCADE
);

-- When an order is created, insert a row into the billings table with paymentStatus as 'Unpaid',
-- and set insuranceClaimStatus based on the insuranceType of the patient
-- if the insuranceType is in the acceptedInsurance table, then insuranceClaimStatus should be True, else False
-- If the insuranceClaimStatus is True, then the billedAmount should be the cost of the test multiplied by the discountRate
-- paymentStatus can be changed by the secretaries
CREATE TABLE IF NOT EXISTS billings (
    billingID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT NOT NULL UNIQUE,  -- Each order should have only one billing
    billedAmount INT NOT NULL,
    paymentStatus BOOLEAN NOT NULL,
    insuranceClaimStatus BOOLEAN NOT NULL,
    FOREIGN KEY (orderID) REFERENCES orders(orderID) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS acceptedInsurances (
    insuranceID INT AUTO_INCREMENT PRIMARY KEY,
    insuranceType VARCHAR(255) NOT NULL UNIQUE,
    discountRate FLOAT NOT NULL, 
    -- Discount rate for the insuranceType should be between 0 and 1
    CHECK (discountRate >= 0 AND discountRate <= 1)
);

-- VIEWS:
-- Create a view to display order of a patient
CREATE VIEW patientOrders AS
SELECT patients.patientID, orders.orderID,
       testCatalogs.testName,
       staffs.firstName AS labStaffFirstName, staffs.lastName AS labStaffLastName,
       orders.orderDate, appointments.appointmentDateTime, orders.orderStatus
FROM orders
JOIN patients ON orders.patientID = patients.patientID
JOIN testCatalogs ON orders.testID = testCatalogs.testID
JOIN staffs ON orders.labStaffOrderID = staffs.staffID
LEFT JOIN appointments ON orders.orderID = appointments.orderID;

-- Create a view to display the results of a patient
CREATE VIEW patientResults AS
SELECT patients.patientID, orders.orderID,
       testCatalogs.testName,
       results.reportURL, results.interpretation
FROM orders
JOIN patients ON orders.patientID = patients.patientID
JOIN testCatalogs ON orders.testID = testCatalogs.testID
JOIN results ON orders.orderID = results.orderID;

-- Create a view to display the billings of a patient
CREATE VIEW patientBillings AS
SELECT patients.patientID, orders.orderID, testCatalogs.testName,
       billings.billedAmount, billings.insuranceClaimStatus, billings.paymentStatus
FROM orders
JOIN testCatalogs ON orders.testID = testCatalogs.testID
JOIN billings ON orders.orderID = billings.orderID
JOIN patients ON orders.patientID = patients.patientID;

-- Create a view to display the orders of a lab staff
CREATE VIEW labStaffOrders AS
SELECT staffs.staffID AS labStaffID, orders.orderID, testCatalogs.testName,
       patients.patientSSN AS patientSSN,
       patients.firstName AS patientFirstName, patients.lastName AS patientLastName,
       orders.orderDate, orders.orderStatus
FROM orders
JOIN staffs ON orders.labStaffOrderID = staffs.staffID
JOIN testCatalogs ON orders.testID = testCatalogs.testID
JOIN patients ON orders.patientID = patients.patientID;

-- Create a view to display the results of a lab staff
CREATE VIEW labStaffResults AS
SELECT orders.orderID,
       orders.labStaffOrderID AS labStaffOrderID, results.labStaffResultID AS labStaffResultID,
       resultStaff.firstName AS labStaffResultFirstName, resultStaff.lastName AS labStaffResultLastName,
       orderStaff.firstName AS labStaffOrderFirstName, orderStaff.lastName AS labStaffOrderLastName,
       patients.patientSSN AS patientSSN, patients.firstName AS patientFirstName, patients.lastName AS patientLastName,
       testCatalogs.testName, results.reportURL, results.interpretation
FROM orders
JOIN results ON orders.orderID = results.orderID
JOIN testCatalogs ON orders.testID = testCatalogs.testID
JOIN patients ON orders.patientID = patients.patientID
JOIN staffs AS resultStaff ON results.labStaffResultID = resultStaff.staffID
JOIN staffs AS orderStaff ON orders.labStaffOrderID = orderStaff.staffID;

-- Create a view to display the appointments of a secretary
CREATE VIEW secretaryAppointments AS
SELECT orders.orderID, staffs.staffID AS secretaryID,
       patients.patientSSN AS patientSSN,
       patients.firstName AS patientFirstName, patients.lastName AS patientLastName,
       staffs.firstName AS secretaryFirstName, staffs.lastName AS secretaryLastName,
       appointments.appointmentDateTime
FROM orders
JOIN patients ON orders.patientID = patients.patientID
JOIN appointments ON orders.orderID = appointments.orderID
JOIN staffs ON appointments.secretaryID = staffs.staffID;

-- Create a view to display the billings of a secretary
CREATE VIEW secretaryBillings AS
SELECT orders.orderID, staffs.staffID AS secretaryID,
       patients.patientSSN AS patientSSN,
       patients.firstName AS patientFirstName, patients.lastName AS patientLastName,
       billings.billedAmount, billings.insuranceClaimStatus, billings.paymentStatus
FROM orders
JOIN patients ON orders.patientID = patients.patientID
JOIN billings ON orders.orderID = billings.orderID
JOIN appointments ON orders.orderID = appointments.orderID
JOIN staffs ON appointments.secretaryID = staffs.staffID;

-- Create a view to display the results of a secretary
CREATE VIEW secretaryResults AS
SELECT orders.orderID, staffs.staffID AS secretaryID,
       patients.patientSSN AS patientSSN,
       patients.firstName AS patientFirstName, patients.lastName AS patientLastName,
       results.reportURL
FROM orders
JOIN patients ON orders.patientID = patients.patientID
JOIN results ON orders.orderID = results.orderID
JOIN appointments ON orders.orderID = appointments.orderID
JOIN staffs ON appointments.secretaryID = staffs.staffID;


 -- TRIGGERS:
-- Create a trigger to update the orderStatus of an order when an appointment is created, change the orderStatus to 'Pending Result'
DELIMITER $$
CREATE TRIGGER updateOrderStatusCreateAppointment
AFTER INSERT ON appointments
FOR EACH ROW
BEGIN
    UPDATE orders
    SET orderStatus = 'Pending Result'
    WHERE orderID = NEW.orderID;
END $$
DELIMITER ;

-- Create a trigger to update the orderStatus of an order when an appointment is deleted, change the orderStatus to 'Pending Appointment'
DELIMITER $$
CREATE TRIGGER updateOrderStatusDeleteAppointment
AFTER DELETE ON appointments
FOR EACH ROW
BEGIN
    UPDATE orders
    SET orderStatus = 'Pending Appointment'
    WHERE orderID = OLD.orderID;
END $$
DELIMITER ;

-- Create a trigger to update the orderStatus of an order when a test result is created, change the orderStatus to 'Completed'
DELIMITER $$
CREATE TRIGGER updateOrderStatusCreateResult
AFTER INSERT ON results
FOR EACH ROW
BEGIN
    UPDATE orders
    SET orderStatus = 'Completed'
    WHERE orderID = NEW.orderID;
END $$
DELIMITER ;

-- Create a trigger to update the orderStatus of an order when a test result is deleted, change the orderStatus to 'Pending Result'
DELIMITER $$
CREATE TRIGGER updateOrderStatusDeleteResult
AFTER DELETE ON results
FOR EACH ROW
BEGIN
    UPDATE orders
    SET orderStatus = 'Pending Result'
    WHERE orderID = OLD.orderID;
END $$
DELIMITER ;

-- Create a trigger to check the orderStatus before inserting a test result
DELIMITER $$
CREATE TRIGGER checkOrderStatusBeforeInsertTestResult
BEFORE INSERT ON results
FOR EACH ROW
BEGIN
    DECLARE currentStatus ENUM('Pending Appointment', 'Pending Result', 'Completed');

    -- Retrieve the current orderStatus
    SELECT orderStatus INTO currentStatus
    FROM orders
    WHERE orderID = NEW.orderID;

    -- Check if the orderStatus is 'Pending Result'
    IF currentStatus != 'Pending Result' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot insert test result: Order status is not "Pending Result".';
    END IF;
END $$
DELIMITER ;

-- Create a trigger to insert a row into the billings table when an order is created
DELIMITER $$
CREATE TRIGGER insertBillingCreateOrder
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    -- Determine discountRate and insuranceClaimStatus
    SET @discountRate = (
        SELECT discountRate
        FROM acceptedInsurances
        WHERE insuranceType = (SELECT insuranceType FROM patients WHERE patientID = NEW.patientID)
    );
    
    IF @discountRate IS NULL THEN
        SET @insuranceClaimStatus = FALSE;
        SET @discountRate = 0;
    ELSE
        SET @insuranceClaimStatus = TRUE;
    END IF;

    -- Calculate billedAmount
    SET @billedAmount = (
        SELECT testCost FROM testCatalogs WHERE testID = NEW.testID
    ) * (1 - @discountRate);

    -- If the @billedAmount is 0, then make paymentStatus as TRUE
    SET @paymentStatus = @billedAmount = 0;

    -- Insert into billings table
    INSERT INTO billings (orderID, billedAmount, paymentStatus, insuranceClaimStatus)
    VALUES (NEW.orderID, @billedAmount, @paymentStatus, @insuranceClaimStatus);
END $$
DELIMITER ;

-- PROCEDURES:
-- Create a procedure to insert a new patient 
DELIMITER $$
CREATE PROCEDURE insertPatient(
    IN email VARCHAR(255),
    IN b_crypted_password VARCHAR(255),
    IN patientSSN VARCHAR(255),
    IN firstName VARCHAR(255),
    IN lastName VARCHAR(255),
    IN birthDate DATE,
    IN phoneNo VARCHAR(255),
    IN insuranceType VARCHAR(255),
    IN roleName VARCHAR(255)
)
BEGIN    
    -- Get the roleID of the roleName
    SET @roleID = (SELECT roleID FROM roles WHERE roles.roleName = roleName);

    -- Insert into the users table
    INSERT INTO users (email, password)
    VALUES (email, b_crypted_password);

    -- Get the userID of the newly inserted user
    SET @newUserID = LAST_INSERT_ID();

    -- Map the user to the patient role
    INSERT INTO userRoles (userID, roleID)
    VALUES (@newUserID, @roleID);
    
    -- Insert into the patients table
    INSERT INTO patients (patientID, patientSSN, firstName, lastName, birthDate, phoneNo, insuranceType)
    VALUES (@newUserID, patientSSN, firstName, lastName, birthDate, phoneNo, insuranceType);
END $$
DELIMITER ;

-- Create a procedure to insert a new staff
DELIMITER $$
CREATE PROCEDURE insertStaff(
    IN email VARCHAR(255),
    IN b_crypted_password VARCHAR(255),
    IN firstName VARCHAR(255),
    IN lastName VARCHAR(255),
    IN phoneNo VARCHAR(255),
    IN roleName VARCHAR(255)
)
BEGIN
    -- Get the roleID of the roleName
    SET @roleID = (SELECT roleID FROM roles WHERE roles.roleName = roleName);

    -- Insert into the users table
    INSERT INTO users (email, password)
    VALUES (email, b_crypted_password);

    -- Get the userID of the newly inserted user
    SET @newUserID = LAST_INSERT_ID();

    -- Map the user to the staff role
    INSERT INTO userRoles (userID, roleID)
    VALUES (@newUserID, @roleID);

    -- Insert into the staffs table
    INSERT INTO staffs (staffID, firstName, lastName, phoneNo, staffRole)
    VALUES (@newUserID, firstName, lastName, phoneNo, roleName);
END $$
DELIMITER ;


-- ROLES:
-- Create roles at the database level
CREATE USER 'patient'@'%' IDENTIFIED BY '123456'; -- Patient
CREATE USER 'labStaff'@'%' IDENTIFIED BY '123456';     -- Lab Staff
CREATE USER 'secretary'@'%' IDENTIFIED BY '123456'; -- Secretary

-- Assign privileges to roles
-- patient
GRANT SELECT, UPDATE ON comp3335_database.patients TO patient;  -- if they can change their information
GRANT SELECT ON comp3335_database.patientOrders TO patient;
GRANT SELECT ON comp3335_database.patientResults TO patient;
GRANT SELECT ON comp3335_database.patientBillings TO patient;


-- labStaff
GRANT SELECT, UPDATE ON comp3335_database.orders TO labStaff;  -- if they can change their information
GRANT SELECT ON comp3335_database.labStaffOrders TO labStaff;
GRANT SELECT ON comp3335_database.labStaffResults TO labStaff;
GRANT SELECT ON comp3335_database.patients TO labStaff;  -- they should see patient information to create order
GRANT SELECT ON comp3335_database.testCatalogs TO labStaff;  -- they should see the test catalog to create order
GRANT SELECT, INSERT, UPDATE, DELETE ON comp3335_database.orders TO labStaff;
GRANT SELECT, INSERT, UPDATE, DELETE ON comp3335_database.results TO labStaff;


-- secretary
GRANT SELECT, UPDATE ON comp3335_database.orders TO secretary;  -- if they can change their information
GRANT SELECT ON comp3335_database.secretaryAppointments TO secretary;
GRANT SELECT ON comp3335_database.secretaryBillings TO secretary;
GRANT SELECT ON comp3335_database.secretaryResults TO secretary;
GRANT SELECT ON comp3335_database.orders TO secretary;  -- they should see order information to create appointment
GRANT SELECT, INSERT, UPDATE, DELETE ON comp3335_database.appointments TO secretary;
GRANT SELECT, UPDATE ON comp3335_database.billings TO secretary;

-- exporter user for MySQL metrics collection by mysqld-exporter
CREATE USER IF NOT EXISTS 'exporter'@'%' IDENTIFIED BY 'exporterpassword';
GRANT PROCESS, REPLICATION CLIENT ON *.* TO 'exporter'@'%';
FLUSH PRIVILEGES;