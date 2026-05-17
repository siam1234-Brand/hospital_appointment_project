<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$app = $model->getAppointment($_GET['id']);
?>
<!DOCTYPE html>
<html>
<head><title>Complete Appointment</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Complete Appointment and Add Consultation Notes</h2>
<?php if($app != null){ ?>
<p>Patient: <?php echo $app['patient_name']; ?> | Date: <?php echo $app['appointment_date']; ?> <?php echo $app['appointment_time']; ?></p>
<form method="post" action="../../controller/doctor/consultationHandler.php">
<input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
<input type="hidden" name="patient_id" value="<?php echo $app['patient_id']; ?>">
<textarea name="symptoms" placeholder="Symptoms"></textarea><br>
<textarea name="diagnosis" placeholder="Diagnosis"></textarea><br>
<textarea name="prescription" placeholder="Prescription details"></textarea><br>
Follow-up Date:<br><input type="date" name="follow_up_date"><br>
<input type="submit" value="Complete Appointment">
</form>
<?php } else { echo "Appointment not found"; } ?>
</div>
</body>
</html>
