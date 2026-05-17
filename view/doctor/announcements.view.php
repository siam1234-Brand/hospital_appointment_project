<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$list = $model->getAnnouncements('doctor');
?>
<!DOCTYPE html>
<html>
<head><title>Announcements</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Doctor Announcements</h2>
<?php foreach($list as $a){ ?><div class="card"><h3><?php echo $a['title']; ?></h3><p><?php echo $a['body']; ?></p><small><?php echo $a['published_at']; ?></small></div><?php } ?>
</div>
</body>
</html>
