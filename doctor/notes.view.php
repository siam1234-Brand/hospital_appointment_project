<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
$patients = $model->searchPatients(isset($_GET['keyword']) ? $_GET['keyword'] : "");
$notes = [];
if (isset($_GET['patient_id'])) {
    $notes = $model->getDoctorPatientNotes($doctor_id, $_GET['patient_id']);
}
?>
<!DOCTYPE html>
<html>
<head><title>Patient Notes</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>View Past Consultation Notes For A Patient</h2>
<form method="get"><input type="text" name="keyword" placeholder="Search patient"><input type="submit" value="Search"></form>
<table><tr><th>ID</th><th>Name</th><th>Phone</th><th>Action</th></tr>
<?php foreach($patients as $p){ ?><tr><td><?php echo $p['id']; ?></td><td><?php echo $p['name']; ?></td><td><?php echo $p['phone']; ?></td><td><a href="notes.view.php?patient_id=<?php echo $p['id']; ?>">View Notes</a></td></tr><?php } ?>
</table>
<h3>Notes</h3>
<?php foreach($notes as $n){ ?><div class="card">Date: <?php echo $n['appointment_date']; ?><br>Symptoms: <?php echo $n['symptoms']; ?><br>Diagnosis: <?php echo $n['diagnosis']; ?><br>Prescription: <?php echo $n['prescription']; ?></div><?php } ?>
</div>
</body>
</html>
