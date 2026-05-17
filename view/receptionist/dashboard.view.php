<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");
require_role('receptionist');
$model = new ReceptionistModel();
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : "";
$today = $model->getAllAppointments($date, $status);
$queue = $model->getWaitingQueue();
?>
<!DOCTYPE html>
<html>
<head><title>Receptionist Dashboard</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Receptionist Dashboard - Daily Schedule</h2>
<?php show_msg(); ?>
<form method="get"><input type="date" name="date" value="<?php echo $date; ?>"><select name="status"><option value="">All Status</option><?php foreach(['pending','confirmed','checked_in','completed','cancelled','no_show'] as $st){ ?><option value="<?php echo $st; ?>" <?php selected($status,$st); ?>><?php echo $st; ?></option><?php } ?></select><input type="submit" value="Filter"></form>
<table><tr><th>Doctor</th><th>Time</th><th>Patient</th><th>Status</th><th>Action</th></tr>
<?php foreach($today as $a){ ?>
<tr><td><?php echo $a['doctor_name']; ?></td><td><?php echo $a['appointment_time']; ?></td><td><?php echo $a['patient_name']; ?> (<?php echo $a['phone']; ?>)</td><td><?php echo $a['status']; ?></td><td><form method="post" action="../../controller/receptionist/appointmentHandler.php"><input type="hidden" name="id" value="<?php echo $a['id']; ?>"><button name="action" value="checkin">Check In</button><button name="action" value="cancel">Cancel</button></form></td></tr>
<?php } ?>
</table>
<h2>Waiting Room Queue</h2>
<table><tr><th>Doctor</th><th>Time</th><th>Patient</th></tr>
<?php foreach($queue as $q){ ?><tr><td><?php echo $q['doctor_name']; ?></td><td><?php echo $q['appointment_time']; ?></td><td><?php echo $q['patient_name']; ?></td></tr><?php } ?>
</table>
</div>
</body>
</html>
