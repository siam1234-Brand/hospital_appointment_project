<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
$availability = $model->getDoctorAvailability($doctor_id);
$leaveDates = $model->getLeaveDates($doctor_id);
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
?>
<!DOCTYPE html>
<html>
<head><title>Availability</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Weekly Availability</h2>
<?php show_msg(); ?>
<form method="post" action="../../controller/doctor/availabilityHandler.php">
    <select name="day_of_week">
        <?php foreach($days as $day){ ?><option value="<?php echo $day; ?>"><?php echo $day; ?></option><?php } ?>
    </select><br>
    <input type="time" name="start_time"> Start<br>
    <input type="time" name="end_time"> End<br>
    Slot Duration: <input type="number" name="slot_duration_minutes" value="30" placeholder="Slot duration"><br>
    <select name="is_available"><option value="1">Available</option><option value="0">Not Available</option></select><br>
    <input type="submit" value="Save Availability">
</form>
<table>
    <tr>
        <th>Day</th>
        <th>Start</th>
        <th>End</th>
        <th>Duration</th>
        <th>Available</th>
    </tr>
<?php foreach($availability as $a){ ?>
<tr>
    <td><?php echo $a['day_of_week']; ?></td>
    <td><?php echo $a['start_time']; ?></td>
    <td><?php echo $a['end_time']; ?></td>
    <td><?php echo $a['slot_duration_minutes']; ?></td>
    <td><?php echo $a['is_available']; ?></td>
</tr>
<?php } ?>
</table>

<br><br>
<h2>Leave Dates</h2>
<form method="post" action="../../controller/doctor/leaveHandler.php">
    <input type="hidden" name="action" value="add">
    <input type="date" name="leave_date"><br>
    <input type="text" name="reason" placeholder="Reason"><br>
    <input type="submit" value="Add Leave Date">
</form>
<table>
    <tr>
        <th>Date</th>
        <th>Reason</th>
        <th>Action</th>
    </tr>
<?php foreach($leaveDates as $l){ ?>
<tr>
    <td><?php echo $l['leave_date']; ?></td>
    <td><?php echo $l['reason']; ?></td>
    <td><form method="post" action="../../controller/doctor/leaveHandler.php"><input type="hidden" name="id" value="<?php echo $l['id']; ?>"><button name="action" value="delete">Remove</button></form></td>
</tr>
<?php } ?>
</table>
</div>
</body>
</html>
