<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$note = $model->getConsultationNoteByAppointment($_GET['appointment_id']);
?>
<!DOCTYPE html>
<html>
<head><title>Consultation Note</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Consultation Notes and Prescription</h2>
<?php if($note != null){ ?>
<div class="card">
Patient: <?php echo $note['patient_name']; ?><br>
Doctor: <?php echo $note['doctor_name']; ?><br>
Symptoms: <?php echo $note['symptoms']; ?><br>
Diagnosis: <?php echo $note['diagnosis']; ?><br>
Prescription: <?php echo $note['prescription']; ?><br>
Follow-up Date: <?php echo $note['follow_up_date']; ?>
</div>
<?php } else { echo "No note found"; } ?>
</div>
</body>
</html>
