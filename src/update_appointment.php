<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'secretary') {
    header("Location: unauthorized.php");
    exit;
}

$role = $_SESSION['role'];
$secretaryID = $_SESSION['userID'];
$userName = $_SESSION['userName'];

$pageTitle = 'Update Appointment';
$heading = 'Update an Existing Appointment';
$error = '';
$message = '';

try {
    // Fetch appointments created by this secretary
    $sql = "SELECT orders.orderID, appointments.appointmentDateTime, appointments.appointmentID
            FROM appointments
            JOIN orders ON appointments.orderID = orders.orderID
            WHERE appointments.secretaryID = ? AND orders.orderStatus != 'Completed'";
    $params = ['types' => 'i', 'values' => [$secretaryID]];
    $appointmentsResult = queryDatabase($role, $sql, $params);

    $appointments = [];
    while ($row = $appointmentsResult->fetch_assoc()) {
        $appointments[] = $row;
    }

    // Handle form submissions
    if (isset($_POST['selectAppointment'])) {
        // Fetch selected appointment details
        $appointmentID = $_POST['appointmentID'];
        $sql = "SELECT * 
                FROM appointments 
                WHERE appointmentID = ? AND secretaryID = ?";
        $params = ['types' => 'ii', 'values' => [$appointmentID, $secretaryID]];
        $appointmentResult = queryDatabase($role, $sql, $params);
        $appointment = $appointmentResult->fetch_assoc();

        if (!$appointment) {
            throw new Exception("Appointment not found or you do not have permission to edit it.");
        }

    } elseif (isset($_POST['updateAppointment'])) {
        // Update the appointment
        $appointmentID = $_POST['appointmentID'];
        $appointmentDateTime = $_POST['appointmentDateTime'];

        $sql = "UPDATE appointments
                SET appointmentDateTime = ?
                WHERE appointmentID = ? AND secretaryID = ?";
        $params = [
            'types' => 'sii',
            'values' => [$appointmentDateTime, $appointmentID, $secretaryID]
        ];
        queryDatabase($role, $sql, $params);

        $message = "Appointment updated successfully.";
        unset($appointment); // Clear the selected appointment

    } elseif (isset($_POST['deleteAppointment'])) {
        // Delete the appointment
        $appointmentID = $_POST['appointmentID'];

        $sql = "DELETE 
                FROM appointments
                WHERE appointmentID = ? AND secretaryID = ?";
        $params = ['types' => 'ii', 'values' => [$appointmentID, $secretaryID]];
        queryDatabase($role, $sql, $params);

        $message = "Appointment deleted successfully.";
        unset($appointment); // Clear the selected appointment

        // Refresh appointments list
        $sql = "SELECT orders.orderID, appointments.appointmentDateTime, appointments.appointmentID
                FROM appointments
                JOIN orders ON appointments.orderID = orders.orderID
                WHERE appointments.secretaryID = ?";
        $params = ['types' => 'i', 'values' => [$secretaryID]];
        $appointmentsResult = queryDatabase($role, $sql, $params);
        $appointments = [];
        while ($row = $appointmentsResult->fetch_assoc()) {
            $appointments[] = $row;
        }
    }

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secretary Dashboard - <?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <!-- Custom Styles -->
    <style>
        /* Same styles as in create_order.php */
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .logout-link {
            margin-top: 20px;
            text-align: center;
        }
        .logout-link a {
            color: #dc3545;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .back-link {
            margin-top: 20px;
            text-align: center;
        }
        .back-link a {
            margin: 0 10px;
        }
        .alert {
            margin-top: 20px;
        }
        .table-responsive {
            margin-bottom: 20px;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .no-appointments {
            text-align: center;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .datetime-input {
            position: relative;
        }
        .datetime-input input[type="datetime-local"] {
            padding-right: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center"><?php echo htmlspecialchars($heading); ?></h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($appointment)): ?>
            <?php if (count($appointments) > 0): ?>
                <div class="form-container mx-auto" style="max-width: 600px;">
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="appointmentID">Select Appointment:</label>
                            <select name="appointmentID" id="appointmentID" class="form-control" required>
                                <?php foreach ($appointments as $appt): ?>
                                    <option value="<?php echo htmlspecialchars($appt['appointmentID']); ?>">
                                        Order ID: <?php echo htmlspecialchars($appt['orderID']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="selectAppointment" class="btn btn-primary btn-block">Select Appointment</button>
                    </form>
                    <div class="back-link">
                        <a href="welcome_secretary.php" class="btn btn-secondary mt-3">Back</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-appointments">
                    <p>No appointments available to update.</p>
                    <div class="back-link">
                        <a href="welcome_secretary.php" class="btn btn-secondary mt-3">Back to Welcome Page</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="form-container mx-auto" style="max-width: 600px;">
                <form method="post" action="">
                    <input type="hidden" name="appointmentID" value="<?php echo htmlspecialchars($appointment['appointmentID']); ?>">

                    <div class="form-group">
                        <label for="appointmentDateTime">Appointment Date and Time:</label>
                        <input type="datetime-local" name="appointmentDateTime" id="appointmentDateTime" class="form-control" value="<?php echo htmlspecialchars($appointment['appointmentDateTime']); ?>" required>
                    </div>

                    <button type="submit" name="updateAppointment" class="btn btn-primary btn-block">Update Appointment</button>
                    <button type="submit" name="deleteAppointment" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to delete this appointment?');">Delete Appointment</button>
                </form>
                <div class="back-link">
                    <a href="update_appointment.php" class="btn btn-secondary mt-3">Back</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>