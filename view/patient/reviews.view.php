<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);
$past = $model->getPastAppointments($patient_id);
$reviews = $model->getOwnReviews($patient_id);
?>
<!DOCTYPE html>
<html>
<head><title>Reviews</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Rate Doctor After Completed Appointment</h2>
<?php show_msg(); ?>
<form method="post" action="../../controller/patient/reviewHandler.php">
<input type="hidden" name="action" value="add">
<select name="appointment_id">
<option value="">Select Completed Appointment</option>
<?php foreach($past as $a){ if($a['status']=='completed'){ ?>
<option value="<?php echo $a['id']; ?>"><?php echo $a['appointment_date']; ?> - <?php echo $a['doctor_name']; ?></option>
<?php }} ?>
</select><br>
<input type="number" name="rating" min="1" max="5" placeholder="Rating 1-5"><br>
<textarea name="review_text" placeholder="Review"></textarea><br>
<input type="submit" value="Add Review">
</form>

<h2>My Reviews</h2>
<table>
<tr><th>Doctor</th><th>Rating</th><th>Review</th><th>Doctor Reply</th><th>Action</th></tr>
<?php foreach($reviews as $r){ ?>
<tr>
<form method="post" action="../../controller/patient/reviewHandler.php">
<td><?php echo $r['doctor_name']; ?></td>
<td><input type="number" name="rating" min="1" max="5" value="<?php echo $r['rating']; ?>"></td>
<td><textarea name="review_text"><?php echo $r['review_text']; ?></textarea></td>
<td><?php echo $r['doctor_reply']; ?></td>
<td><input type="hidden" name="id" value="<?php echo $r['id']; ?>"><button name="action" value="update">Update</button><button name="action" value="delete">Delete</button></td>
</form>
</tr>
<?php } ?>
</table>
</div>
</body>
</html>
