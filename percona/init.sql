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


INSERT INTO patients (name, surname, birthdate, phoneNo, email, password, insuranceType) 
VALUES ('Alice', 'Chan', '1990-01-01', '12345678', 'alice@gmail.com', '123456', 'AIA'),
       ('Bob', 'Lee', '1991-02-02', '87654321', 'bob@gmail.com', '123456', 'AXA'),
       ('Charlie', 'Wong', '1992-03-03', '12345678', 'charlie@gmail.com', '123456', 'Prudential');

INSERT INTO staffs (name, surname, phoneNo, email, password, role)
VALUES ('David', 'Chan', '12345678', 'david@gmail.com', '123456', 'labStaff'),
       ('Eva', 'Lee', '87654321', 'eva@gmail.com', '123456', 'secretary'),
       ('Frank', 'Wong', '12345678', 'frank@gmail.com', '123456', 'labStaff');

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
