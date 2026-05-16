<?php
include_once("../../helper/auth.php");
include_once("../../model/HospitalModel.php");
require_role('admin');
$model = new HospitalModel();
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$list = $model->searchPatients($keyword);
?>
<!DOCTYPE html>
<html>
<head><title>Patients</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Manage Patient Accounts</h2>
<?php show_msg(); ?>
<form method="get"><input type="text" name="keyword" placeholder="Search patient" value="<?php echo $keyword; ?>"><input type="submit" value="Search"></form>
<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Active</th><th>Action</th></tr>
<?php foreach($list as $p){ ?><tr><td><?php echo $p['id']; ?></td><td><?php echo $p['name']; ?></td><td><?php echo $p['email']; ?></td><td><?php echo $p['phone']; ?></td><td><?php echo $p['is_active']; ?></td><td><form method="post" action="../../controller/admin/patientHandler.php"><input type="hidden" name="patient_id" value="<?php echo $p['id']; ?>"><input type="submit" value="Deactivate"></form></td></tr><?php } ?>
</table>
</div>
</body>
</html>
