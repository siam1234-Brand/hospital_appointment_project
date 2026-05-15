<?php
include_once("../../helper/auth.php");
include_once("../../model/HospitalModel.php");
require_role('admin');
$model = new HospitalModel();
$list = $model->getSpecializations();
?>
<!DOCTYPE html>
<html>
<head><title>Specializations</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Manage Specializations</h2>
<?php show_msg(); ?>
<form method="post" action="../../controller/admin/specializationHandler.php"><input type="hidden" name="action" value="add"><input type="text" name="name" placeholder="Name"><input type="text" name="description" placeholder="Description"><input type="submit" value="Add"></form>
<table><tr><th>Name</th><th>Description</th><th>Action</th></tr>
<?php foreach($list as $s){ ?><tr><form method="post" action="../../controller/admin/specializationHandler.php"><td><input type="text" name="name" value="<?php echo $s['name']; ?>"></td><td><input type="text" name="description" value="<?php echo $s['description']; ?>"></td><td><input type="hidden" name="id" value="<?php echo $s['id']; ?>"><button name="action" value="update">Rename/Update</button><button name="action" value="delete">Delete</button></td></form></tr><?php } ?>
</table>
</div>
</body>
</html>
