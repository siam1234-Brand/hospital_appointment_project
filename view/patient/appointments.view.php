<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);
$upcoming = $model->getUpcomingAppointments($patient_id);
$past = $model->getPastAppointments($patient_id);
?>
<!DOCTYPE html>
<html>
<head><title>Appointments</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Upcoming Appointments</h2>
<?php show_msg(); ?>
<table>
<tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th><th>Action</th></tr>
<?php foreach($upcoming as $a){ ?>
<tr>
<td><?php echo $a['appointment_date']; ?></td><td><?php echo $a['appointment_time']; ?></td><td><?php echo $a['doctor_name']; ?></td><td><span class="badge"><?php echo $a['status']; ?></span></td>
<td>
    <form method="post" action="../../controller/patient/appointmentHandler.php">
        <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
        <input type="text" name="cancel_reason" placeholder="Cancel reason">
        <button name="action" value="cancel">Cancel</button>
    </form>
    <form method="post" action="../../controller/patient/appointmentHandler.php">
        <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
        <input type="date" name="appointment_date">
        <input type="time" name="appointment_time">
        <input type="text" name="reschedule_note" placeholder="Reason">
        <button name="action" value="reschedule">Reschedule</button>
    </form>
</td>
</tr>
<?php } ?>
</table>

<h2>Past Appointment History</h2>
<table>
<tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th><th>Notes</th></tr>
<?php foreach($past as $a){ ?>
<tr>
<td><?php echo $a['appointment_date']; ?></td><td><?php echo $a['appointment_time']; ?></td><td><?php echo $a['doctor_name']; ?></td><td><?php echo $a['status']; ?></td>
<td><?php if($a['status']=='completed'){ ?><a href="notes.view.php?appointment_id=<?php echo $a['id']; ?>">View Notes</a><?php } ?></td>
</tr>
<?php } ?>
</table>
</div>
</body>
</html>
