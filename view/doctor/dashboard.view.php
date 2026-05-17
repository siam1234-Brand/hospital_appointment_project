<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
$today = $model->getDoctorScheduleToday($doctor_id);
$stats = $model->getDoctorStats($doctor_id);
?>
<!DOCTYPE html>
<html>
<head><title>Doctor Dashboard</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Doctor Dashboard</h2>
<p>Welcome, <?php echo $_SESSION['name']; ?></p>
<?php show_msg(); ?>
<div class="card">Today's Appointments: <?php echo count($today); ?></div>
<div class="card">Completed: <?php echo $stats['completed']; ?> | Cancelled: <?php echo $stats['cancelled']; ?> | No Show: <?php echo $stats['no_show']; ?></div>
</div>
</body>
</html>
