<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
$list = $model->getSettings();
?>
<!DOCTYPE html>
<html>
<head><title>Policies</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Global Appointment Policies</h2>
<?php show_msg(); ?>
<table><tr><th>Policy</th><th>Value</th><th>Action</th></tr>
<?php foreach($list as $s){ ?><tr><form method="post" action="../../controller/admin/policyHandler.php"><td><?php echo $s['setting_name']; ?></td><td><input type="text" name="setting_value" value="<?php echo $s['setting_value']; ?>"></td><td><input type="hidden" name="setting_name" value="<?php echo $s['setting_name']; ?>"><input type="submit" value="Update"></td></form></tr><?php } ?>
</table>
</div>
</body>
</html>
