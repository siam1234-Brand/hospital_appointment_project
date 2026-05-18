<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
$earnings = $model->getDoctorEarnings($doctor_id);
$stats = $model->getDoctorStats($doctor_id);
$followUps = $model->getFollowUps($doctor_id);
$reviews = $model->getDoctorReviews($doctor_id);
?>
<!DOCTYPE html>
<html>
<head><title>Doctor Reports</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Earnings Report</h2>
<table><tr><th>Date</th><th>Completed</th><th>Earning</th></tr>
<?php foreach($earnings as $e){ ?><tr><td><?php echo $e['appointment_date']; ?></td><td><?php echo $e['completed_count']; ?></td><td><?php echo $e['total_earning']; ?></td></tr><?php } ?>
</table>
<h2>Appointment Statistics</h2>
<div class="card">Total: <?php echo $stats['total']; ?> | Completed: <?php echo $stats['completed']; ?> | Cancelled: <?php echo $stats['cancelled']; ?> | No Show: <?php echo $stats['no_show']; ?></div>
<h2>Upcoming Follow-ups</h2>
<table><tr><th>Patient</th><th>Follow-up Date</th><th>Diagnosis</th></tr>
<?php foreach($followUps as $f){ ?><tr><td><?php echo $f['patient_name']; ?></td><td><?php echo $f['follow_up_date']; ?></td><td><?php echo $f['diagnosis']; ?></td></tr><?php } ?>
</table>
<h2>Patient Reviews</h2>
<table><tr><th>Patient</th><th>Rating</th><th>Review</th><th>Reply</th></tr>
<?php foreach($reviews as $r){ ?>
<tr><form method="post" action="../../controller/doctor/reviewReplyHandler.php"><td><?php echo $r['patient_name']; ?></td><td><?php echo $r['rating']; ?></td><td><?php echo $r['review_text']; ?></td><td><input type="hidden" name="id" value="<?php echo $r['id']; ?>"><input type="text" name="reply" value="<?php echo $r['doctor_reply']; ?>"><input type="submit" value="Reply"></td></form></tr>
<?php } ?>
</table>
</div>
</body>
</html>
