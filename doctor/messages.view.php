<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
$messages = $model->getDoctorMessages($doctor_id);
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
</div>
</body>
</html>
