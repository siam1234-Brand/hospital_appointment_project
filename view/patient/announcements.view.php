<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$list = $model->getAnnouncements('patient');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Announcements</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>

<body>
    <?php include_once("menu.php"); ?>
    <div class="container">
        <h2>Hospital Announcements</h2>
        <?php foreach ($list as $a) { ?>
            <div class="card">
                <h3><?php echo $a['title']; ?></h3>
                <p><?php echo $a['body']; ?></p><small><?php echo $a['published_at']; ?> by <?php echo $a['author']; ?></small>
            </div>
        <?php } ?>
    </div>
</body>

</html>
