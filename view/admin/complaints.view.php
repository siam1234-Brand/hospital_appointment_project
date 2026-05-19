<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
$list = $model->getComplaints();
?>
<!DOCTYPE html>
<html>
<head><title>Complaints</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Patient Complaints</h2>
<?php show_msg(); ?>
<table><tr><th>Patient</th><th>Complaint</th><th>Status</th><th>Admin Response</th></tr>
<?php foreach($list as $c){ ?><tr><form method="post" action="../../controller/admin/complaintHandler.php"><td><?php echo $c['patient_name']; ?></td><td><?php echo $c['complaint_text']; ?></td><td><?php echo $c['status']; ?></td><td><input type="hidden" name="id" value="<?php echo $c['id']; ?>"><input type="text" name="admin_response" value="<?php echo $c['admin_response']; ?>"><input type="submit" value="Mark Resolved"></td></form></tr><?php } ?>
</table>
</div>
</body>
</html>
