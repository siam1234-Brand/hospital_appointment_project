<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);
$bills = $model->getBillingByPatient($patient_id);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Billing</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>

<body>
    <?php include_once("menu.php"); ?>
    <div class="container">
        <h2>Billing History</h2>
        <?php show_msg(); ?>
        <table>
            <tr>
                <th>Doctor</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Payment Intent</th>
            </tr>
            <?php foreach ($bills as $b) { ?>
                <tr>
                    <td><?php echo $b['doctor_name']; ?></td>
                    <td><?php echo $b['appointment_date']; ?></td>
                    <td><?php echo $b['amount']; ?></td>
                    <td><?php echo $b['payment_method']; ?></td>
                    <td><?php echo $b['payment_status']; ?></td>
                    <td>
                        <?php if ($b['payment_status'] == 'pending') { ?>
                            <form method="post" action="../../controller/patient/paymentHandler.php">
                                <input type="hidden" name="bill_id" value="<?php echo $b['id']; ?>">
                                <select name="payment_method">
                                    <option value="cash">Cash</option>
                                    <option value="bkash">bKash</option>
                                    <option value="card">Card</option>
                                </select>
                                <input type="submit" value="Submit Payment Intent">
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>

</html>
