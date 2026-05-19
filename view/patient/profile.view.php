<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$p = $model->getPatientByUser($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head><title>Patient Profile</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
    <h2>Manage Profile</h2>
    <?php show_msg(); ?>
    <?php if ($p['profile_pic'] != "") { ?><img class="small-img" src="../../<?php echo $p['profile_pic']; ?>"><?php } ?>
    <form method="post" action="../../controller/patient/profileHandler.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_profile">
        <input type="text" name="name" value="<?php echo $p['name']; ?>" placeholder="Name"><br>
        <input type="email" name="email" value="<?php echo $p['email']; ?>" placeholder="Email"><br>
        <input type="text" name="phone" value="<?php echo $p['phone']; ?>" placeholder="Phone"><br>
        <input type="date" name="date_of_birth" value="<?php echo $p['date_of_birth']; ?>"><br>
        <input type="text" name="blood_group" value="<?php echo $p['blood_group']; ?>" placeholder="Blood Group"><br>
        <select name="gender">
            <option value="Male" <?php selected($p['gender'], 'Male'); ?>>Male</option>
            <option value="Female" <?php selected($p['gender'], 'Female'); ?>>Female</option>
            <option value="Other" <?php selected($p['gender'], 'Other'); ?>>Other</option>
        </select><br>
        <textarea name="address" placeholder="Address"><?php echo $p['address']; ?></textarea><br>
        <input type="text" name="emergency_contact_name" value="<?php echo $p['emergency_contact_name']; ?>" placeholder="Emergency Contact Name"><br>
        <input type="text" name="emergency_contact_phone" value="<?php echo $p['emergency_contact_phone']; ?>" placeholder="Emergency Contact Phone"><br>
        Profile Picture:<br><input type="file" name="profile_pic"><br>
        <input type="submit" value="Update Profile">
    </form>

    <h3>Medical History Notes</h3>
    <form method="post" action="../../controller/patient/profileHandler.php">
        <input type="hidden" name="action" value="update_history">
        <textarea name="medical_history_notes"><?php echo $p['medical_history_notes']; ?></textarea><br>
        <input type="submit" value="Save Medical Notes">
    </form>

    <h3>Change Password</h3>
    <form method="post" action="../../controller/patient/profileHandler.php">
        <input type="hidden" name="action" value="change_password">
        <input type="password" name="old_password" placeholder="Old Password"><br>
        <input type="password" name="new_password" placeholder="New Password"><br>
        <input type="submit" value="Change Password">
    </form>
</div>
</body>
</html>
