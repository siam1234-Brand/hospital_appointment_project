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
<head>
    <title>Billing Dashboard</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
<?php include_once("menu.php"); ?>

<div class="container">
    <h2>Billing Dashboard</h2>

    <?php
    if (isset($_SESSION['success'])) {
        echo "<p style='color:green;'>" . htmlspecialchars($_SESSION['success']) . "</p>";
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        if (is_array($_SESSION['error'])) {
            foreach ($_SESSION['error'] as $msg) {
                echo "<p style='color:red;'>" . htmlspecialchars($msg) . "</p>";
            }
        } else {
            echo "<p style='color:red;'>" . htmlspecialchars($_SESSION['error']) . "</p>";
        }
        unset($_SESSION['error']);
    }
    ?>

    <div class="card">
        Paid Count: <?php echo htmlspecialchars($dash['total_paid_count']); ?> |
        Pending Count: <?php echo htmlspecialchars($dash['total_pending_count']); ?>
        <br>
        Paid Amount: <?php echo htmlspecialchars($dash['paid_amount']); ?> |
        Pending Amount: <?php echo htmlspecialchars($dash['pending_amount']); ?>
        <br>
        Overdue Bills: <?php echo htmlspecialchars($dash['overdue_count']); ?> |
        Overdue Amount: <?php echo htmlspecialchars($dash['overdue_amount']); ?>
    </div>

    <h3>Pending Bills</h3>

    <table>
        <tr>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Amount</th>
            <th>Payment Method</th>
            <th>Action</th>
        </tr>

        <?php if (count($pending) > 0) { ?>
            <?php foreach ($pending as $p) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['amount']); ?></td>
                    <td>
                        <form method="post" action="../../controller/admin/billingHandler.php">
                            <input type="hidden" name="action" value="mark_paid">
                            <input type="hidden" name="billing_id" value="<?php echo htmlspecialchars($p['billing_id']); ?>">
                            <select name="payment_method">
                                <option value="cash">Cash</option>
                                <option value="bkash">Bkash</option>
                                <option value="card">Card</option>
                            </select>
                    </td>
                    <td>
                            <button type="submit" onclick="return confirm('Mark this bill as paid?');">
                                Mark as Paid
                            </button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">No pending bills found.</td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <button onclick="window.print()">Export / Print Billing</button>
</div>
</body>
</html>
