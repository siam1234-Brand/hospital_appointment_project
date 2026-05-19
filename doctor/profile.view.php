<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
$d = $model->getDoctorDetail($doctor_id);
$specs = $model->getSpecializations();
?>
<!DOCTYPE html>
<html>
<head><title>Doctor Profile</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Professional Profile</h2>
<?php show_msg(); ?>
<?php if ($d['photo_path'] != "") { ?><img class="small-img" src="../../<?php echo $d['photo_path']; ?>"><?php } ?>
<form method="post" action="../../controller/doctor/profileHandler.php" enctype="multipart/form-data">
    <textarea name="bio" placeholder="Bio"><?php echo $d['bio']; ?></textarea><br>
    Specialization: <select name="specialization_id">
        <?php foreach($specs as $s){ ?><option value="<?php echo $s['id']; ?>" <?php selected($d['specialization_id'], $s['id']); ?>><?php echo $s['name']; ?></option><?php } ?>
    </select><br>
    Consultation Fee: <input type="number" name="consultation_fee" value="<?php echo $d['consultation_fee']; ?>" placeholder="Fee"><br>
    License Number: <input type="text" name="license_number" value="<?php echo $d['license_number']; ?>" placeholder="License Number"><br>
    Experience Years: <input type="number" name="experience_years" value="<?php echo $d['experience_years']; ?>" placeholder="Experience Years"><br>
    Profile Photo: <br><input type="file" name="photo"><br>
    <input type="submit" value="Update Profile">
</form>
</div>
</body>
</html>
