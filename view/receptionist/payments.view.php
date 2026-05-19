<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");

require_role('receptionist');

$model = new ReceptionistModel();
$bills = $model->getPendingBills();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payments</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>

<?php include_once("menu.php"); ?>

<div class="container">
    <h2>Process Appointment Payments</h2>

    <?php show_msg(); ?>

    <table>
        <tr>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Appointment</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Action</th>
        </tr>

        <?php if (count($bills) > 0) { ?>
            <?php foreach($bills as $b){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($b['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['appointment_date'] . " " . $b['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($b['amount']); ?></td>
                    <td>
                        <form method="post" action="../../controller/receptionist/billingHandler.php">
                            <input type="hidden" name="bill_id" value="<?php echo $b['id']; ?>">
                            <select name="payment_method">
                                <option value="cash">Cash</option>
                                <option value="bkash">bKash</option>
                                <option value="card">Card</option>
                            </select>
                    </td>
                    <td>
                            <input type="submit" value="Mark Paid">
                        </form>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="6">No pending bills found.</td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
