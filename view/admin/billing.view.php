<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
$dash = $model->getBillingDashboard();
$pending = $model->getPendingBills();
?>
<!DOCTYPE html>
<html>
<head><title>Billing Dashboard</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Billing Dashboard</h2>
<div class="card">Paid Count: <?php echo $dash['total_paid_count']; ?> | Pending Count: <?php echo $dash['total_pending_count']; ?><br>Paid Amount: <?php echo $dash['paid_amount']; ?> | Pending Amount: <?php echo $dash['pending_amount']; ?></div>
<h3>Pending Bills</h3>
<table><tr><th>Patient</th><th>Doctor</th><th>Amount</th></tr><?php foreach($pending as $p){ ?><tr><td><?php echo $p['patient_name']; ?></td><td><?php echo $p['doctor_name']; ?></td><td><?php echo $p['amount']; ?></td></tr><?php } ?></table>
<button onclick="window.print()">Export / Print Billing</button>
</div>
</body>
</html>
