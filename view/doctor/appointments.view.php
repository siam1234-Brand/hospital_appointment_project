<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
$today = $model->getDoctorScheduleToday($doctor_id);
$week = $model->getDoctorWeeklyCalendar($doctor_id);
?>
<!DOCTYPE html>
<html>
<head><title>Doctor Appointments</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Today's Schedule</h2>
<?php show_msg(); ?>
<table>
<tr><th>Time</th><th>Patient</th><th>Reason</th><th>Status</th><th>Action</th></tr>
<?php foreach($today as $a){ ?>
<tr id="row_<?php echo $a['id']; ?>">
<td><?php echo $a['appointment_time']; ?></td><td><?php echo $a['patient_name']; ?></td><td><?php echo $a['reason']; ?></td><td id="status_<?php echo $a['id']; ?>"><?php echo $a['status']; ?></td>
<td>
<form method="post" action="../../controller/doctor/appointmentHandler.php">
<input type="hidden" name="id" value="<?php echo $a['id']; ?>">
<button name="status" value="confirmed">Confirm</button>
<button name="status" value="rejected">Reject</button>
<button name="status" value="no_show">No Show</button>
</form>
<button onclick="checkIn(<?php echo $a['id']; ?>)">Check In AJAX</button>
<a href="complete.view.php?id=<?php echo $a['id']; ?>">Complete & Note</a>
</td>
</tr>
<?php } ?>
</table>

<h2>Weekly Calendar</h2>
<table><tr><th>Date</th><th>Time</th><th>Patient</th><th>Status</th></tr>
<?php foreach($week as $a){ ?>
<tr><td><?php echo $a['appointment_date']; ?></td><td><?php echo $a['appointment_time']; ?></td><td><?php echo $a['patient_name']; ?></td><td><?php echo $a['status']; ?></td></tr>
<?php } ?>
</table>
</div>
<script>
function checkIn(id) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../api/doctor_checkin.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            if (data.success) {
                document.getElementById('status_' + id).innerHTML = 'checked_in';
            } else {
                alert(data.message);
            }
        }
    }
    xhr.send('id=' + id);
}
</script>
</body>
</html>
