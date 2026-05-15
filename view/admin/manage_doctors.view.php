<?php
include_once("../../helper/auth.php");
include_once("../../model/HospitalModel.php");
require_role('admin');
$model = new HospitalModel();
$doctors = $model->getAllDoctorsAdmin();
$specs = $model->getSpecializations();
?>
<!DOCTYPE html>
<html>
<head><title>Manage Doctors</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Manage Doctor Accounts</h2>
<?php show_msg(); ?>
<h3>Add New Doctor</h3>
<form method="post" action="../../controller/admin/doctorHandler.php">
<input type="hidden" name="action" value="add">
<input type="text" name="name" placeholder="Name"><br>
<input type="email" name="email" placeholder="Email"><br>
<input type="password" name="password" placeholder="Password"><br>
<input type="text" name="phone" placeholder="Phone"><br>
<select name="specialization_id"><?php foreach($specs as $s){ ?><option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option><?php } ?></select><br>
<textarea name="bio" placeholder="Bio"></textarea><br>
<input type="number" name="consultation_fee" placeholder="Fee"><br>
<input type="text" name="license_number" placeholder="License"><br>
<input type="number" name="experience_years" placeholder="Experience"><br>
<input type="submit" value="Add Doctor">
</form>
<table><tr><th>Name</th><th>Email</th><th>Spec</th><th>Fee</th><th>Approved</th><th>Active</th><th>Action</th></tr>
<?php foreach($doctors as $d){ ?>
<tr><form method="post" action="../../controller/admin/doctorHandler.php">
<td><input type="text" name="name" value="<?php echo $d['name']; ?>"></td>
<td><input type="email" name="email" value="<?php echo $d['email']; ?>"><input type="text" name="phone" value="<?php echo $d['phone']; ?>"></td>
<td><select name="specialization_id"><?php foreach($specs as $s){ ?><option value="<?php echo $s['id']; ?>" <?php selected($d['specialization_id'],$s['id']); ?>><?php echo $s['name']; ?></option><?php } ?></select></td>
<td><input type="number" name="consultation_fee" value="<?php echo $d['consultation_fee']; ?>"></td>
<td><select name="is_approved"><option value="1" <?php selected($d['is_approved'],1); ?>>Approved</option><option value="0" <?php selected($d['is_approved'],0); ?>>Pending/Reject</option></select></td>
<td><?php echo $d['is_active']; ?></td>
<td><input type="hidden" name="doctor_id" value="<?php echo $d['id']; ?>"><input type="hidden" name="bio" value="<?php echo $d['bio']; ?>"><input type="hidden" name="license_number" value="<?php echo $d['license_number']; ?>"><input type="hidden" name="experience_years" value="<?php echo $d['experience_years']; ?>"><button name="action" value="update">Update</button><button name="action" value="deactivate">Deactivate</button></td>
</form></tr>
<?php } ?>
</table>
</div>
</body>
</html>
