<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");
require_role('receptionist');
$model = new ReceptionistModel();
$bill = $model->getBill($_GET['bill_id']);
?>
<!DOCTYPE html>
<html>
<head><title>Receipt</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<div class="print-box">
<h2>Hospital Payment Receipt</h2>
<?php if($bill != null){ ?>
<p>Receipt No: <?php echo $bill['id']; ?></p>
<p>Patient: <?php echo $bill['patient_name']; ?></p>
<p>Doctor: <?php echo $bill['doctor_name']; ?></p>
<p>Appointment: <?php echo $bill['appointment_date']; ?> <?php echo $bill['appointment_time']; ?></p>
<p>Amount: <?php echo $bill['amount']; ?></p>
<p>Method: <?php echo $bill['payment_method']; ?></p>
<p>Status: <?php echo $bill['payment_status']; ?></p>
<p>Paid At: <?php echo $bill['paid_at']; ?></p>
<button onclick="window.print()">Print</button>
<?php } ?>
<a href="payments.view.php">Back</a>
</div>
</body>
</html>
