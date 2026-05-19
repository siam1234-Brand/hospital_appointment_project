<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
$revenue = $model->getRevenueReport();
$volume = $model->getVolumeReport();
$performance = $model->getPerformanceReport();
?>
<!DOCTYPE html>
<html>
<head><title>Admin Reports</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Hospital-wide Revenue Report</h2>
<table><tr><th>Date</th><th>Doctor</th><th>Specialization</th><th>Revenue</th></tr><?php foreach($revenue as $r){ ?><tr><td><?php echo $r['paid_date']; ?></td><td><?php echo $r['doctor_name']; ?></td><td><?php echo $r['specialization']; ?></td><td><?php echo $r['total_revenue']; ?></td></tr><?php } ?></table>
<h2>Appointment Volume Report</h2>
<table><tr><th>Doctor</th><th>Specialization</th><th>Day</th><th>Total</th></tr><?php foreach($volume as $v){ ?><tr><td><?php echo $v['doctor_name']; ?></td><td><?php echo $v['specialization']; ?></td><td><?php echo $v['day_name']; ?></td><td><?php echo $v['total']; ?></td></tr><?php } ?></table>
<h2>Doctor Performance Report</h2>
<table><tr><th>Doctor</th><th>Consultation Count</th><th>Average Rating</th><th>No-show</th></tr><?php foreach($performance as $p){ ?><tr><td><?php echo $p['doctor_name']; ?></td><td><?php echo $p['total_consultation']; ?></td><td><?php echo round($p['average_rating'],2); ?></td><td><?php echo $p['no_show_count']; ?></td></tr><?php } ?></table>
<button onclick="window.print()">Export / Print Report</button>
</div>
</body>
</html>
