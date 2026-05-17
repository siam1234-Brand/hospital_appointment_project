<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
$list = $model->getAnnouncements('all');
?>
<!DOCTYPE html>
<html>
<head><title>Announcements</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Post Hospital-wide Announcements</h2>
<?php show_msg(); ?>
<form method="post" action="../../controller/admin/announcementHandler.php"><input type="hidden" name="action" value="add"><input type="text" name="title" placeholder="Title"><br><textarea name="body" placeholder="Notice body"></textarea><br><select name="target_role"><option value="all">All</option><option value="patient">Patient</option><option value="doctor">Doctor</option></select><br><input type="submit" value="Post"></form>
<table><tr><th>Title</th><th>Body</th><th>Target</th><th>Action</th></tr>
<?php foreach($list as $a){ ?><tr><td><?php echo $a['title']; ?></td><td><?php echo $a['body']; ?></td><td><?php echo $a['target_role']; ?></td><td><form method="post" action="../../controller/admin/announcementHandler.php"><input type="hidden" name="id" value="<?php echo $a['id']; ?>"><button name="action" value="delete">Delete</button></form></td></tr><?php } ?>
</table>
</div>
</body>
</html>
