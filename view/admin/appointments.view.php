<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
$date = isset($_GET['date']) ? $_GET['date'] : "";
$status = isset($_GET['status']) ? $_GET['status'] : "";
$doctor_id = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : "";
$booked_by = isset($_GET['booked_by']) ? $_GET['booked_by'] : "";
$doctors = $model->getApprovedDoctors();
$list = $model->getAllAppointments($date, $status, $doctor_id, $booked_by);
?>
<!DOCTYPE html>
<html>
<head><title>All Appointments</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>All Appointments With Filters</h2>
<form method="get">
<input type="date" name="date" value="<?php echo $date; ?>">
<select name="doctor_id"><option value="">All Doctors</option><?php foreach($doctors as $d){ ?><option value="<?php echo $d['id']; ?>" <?php selected($doctor_id,$d['id']); ?>><?php echo $d['name']; ?></option><?php } ?></select>
<select name="status"><option value="">All Status</option><?php foreach(['pending','confirmed','checked_in','completed','cancelled','no_show','rejected'] as $st){ ?><option value="<?php echo $st; ?>" <?php selected($status,$st); ?>><?php echo $st; ?></option><?php } ?></select>
<select name="booked_by"><option value="">All Source</option><option value="patient" <?php selected($booked_by,'patient'); ?>>Patient</option><option value="receptionist" <?php selected($booked_by,'receptionist'); ?>>Receptionist</option></select>
<input type="submit" value="Filter">
</form>
<table><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Patient</th><th>Status</th><th>Booked By</th></tr>
<?php foreach($list as $a){ ?><tr><td><?php echo $a['appointment_date']; ?></td><td><?php echo $a['appointment_time']; ?></td><td><?php echo $a['doctor_name']; ?></td><td><?php echo $a['patient_name']; ?></td><td><?php echo $a['status']; ?></td><td><?php echo $a['booked_by']; ?></td></tr><?php } ?>
</table>
<button onclick="window.print()">Export / Print Table</button>
</div>
</body>
</html>
