<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
$messages = $model->getDoctorMessages($doctor_id);
$reviews = $model->getDoctorReviews($doctor_id);
?>
<!DOCTYPE html>
<html>
<head><title>Messages</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Patient Messages</h2>
<?php show_msg(); ?>
<table><tr><th>Patient</th><th>Message</th><th>Reply</th></tr>
<?php foreach($messages as $m){ ?>
<tr><form method="post" action="../../controller/doctor/messageHandler.php">
    <td><?php echo $m['patient_name']; ?></td>
    <td><?php echo $m['message_text']; ?></td>
    <td>
        <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
        <input type="text" name="reply" value="<?php echo $m['reply_text']; ?>">
        <input type="submit" value="Send Reply">
    </td>
</form></tr>
<?php } ?>
</table>
<h2>Patient Reviews</h2>
<table>
    <tr>
        <th>Patient</th>
        <th>Rating</th>
        <th>Review</th>
        <th>Reply</th>
    </tr>
<?php foreach($reviews as $r){ ?>
<tr>
    <form method="post" action="../../controller/doctor/reviewReplyHandler.php">
        <td><?php echo $r['patient_name']; ?></td>
        <td><?php echo $r['rating']; ?></td>
        <td><?php echo $r['review_text']; ?></td>
        <td>
            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
            <input type="text" name="reply" value="<?php echo $r['doctor_reply']; ?>">
            <input type="submit" value="Reply">
        </td>
    </form>
</tr>
<?php } ?>
</table>
</div>
</body>
</html>
