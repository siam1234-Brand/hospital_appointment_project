<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");

require_role('receptionist');

$model = new ReceptionistModel();

$bill_id = $_GET['bill_id'] ?? 0;
$bill = null;

if ($bill_id != 0) {
    $bill = $model->getBill($bill_id);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>

<div class="print-box">
    <h2>Hospital Payment Receipt</h2>

    <?php if($bill != null){ ?>
        <p>Receipt No: <?php echo htmlspecialchars($bill['id']); ?></p>
        <p>Patient: <?php echo htmlspecialchars($bill['patient_name']); ?></p>
        <p>Doctor: <?php echo htmlspecialchars($bill['doctor_name']); ?></p>
        <p>Appointment: <?php echo htmlspecialchars($bill['appointment_date']); ?> <?php echo htmlspecialchars($bill['appointment_time']); ?></p>
        <p>Amount: <?php echo htmlspecialchars($bill['amount']); ?></p>
        <p>Method: <?php echo htmlspecialchars($bill['payment_method']); ?></p>
        <p>Status: <?php echo htmlspecialchars($bill['payment_status']); ?></p>
        <p>Paid At: <?php echo htmlspecialchars($bill['paid_at']); ?></p>

        <button onclick="window.print()">Print</button>
    <?php } else { ?>
        <p>Receipt not found.</p>
    <?php } ?>

    <a href="payments.view.php">Back</a>
</div>

</body>
</html>
