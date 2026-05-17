<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
$list = $model->getStaff('receptionist');
?>
<!DOCTYPE html>
<html>
<head><title>Receptionists</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Manage Receptionist Accounts</h2>
<?php show_msg(); ?>
<form method="post" action="../../controller/admin/staffHandler.php"><input type="hidden" name="action" value="add"><input type="text" name="name" placeholder="Name"><br><input type="email" name="email" placeholder="Email"><br><input type="password" name="password" placeholder="Password"><br><input type="text" name="phone" placeholder="Phone"><br><input type="submit" value="Create Receptionist"></form>
<table><tr><th>Name</th><th>Email</th><th>Phone</th><th>Active</th><th>Action</th></tr>
<?php foreach($list as $u){ ?><tr><form method="post" action="../../controller/admin/staffHandler.php"><td><input type="text" name="name" value="<?php echo $u['name']; ?>"></td><td><input type="email" name="email" value="<?php echo $u['email']; ?>"></td><td><input type="text" name="phone" value="<?php echo $u['phone']; ?>"></td><td><select name="is_active"><option value="1" <?php selected($u['is_active'],1); ?>>Active</option><option value="0" <?php selected($u['is_active'],0); ?>>Inactive</option></select></td><td><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><button name="action" value="update">Edit/Deactivate</button></td></form></tr><?php } ?>
</table>
</div>
</body>
</html>
