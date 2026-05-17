<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);
$upcoming = $model->getUpcomingAppointments($patient_id);
$bills = $model->getBillingByPatient($patient_id);
?>
<!DOCTYPE html>
<html>
<head><title>Patient Dashboard</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
    <h2>Patient Dashboard</h2>
    <p>Welcome, <?php echo $_SESSION['name']; ?></p>
    <?php show_msg(); ?>
    <div class="card">Upcoming Appointments: <?php echo count($upcoming); ?></div>
    <div class="card">Billing Records: <?php echo count($bills); ?></div>
</div>
</body>
</html>
