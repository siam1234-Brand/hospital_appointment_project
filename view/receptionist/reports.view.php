<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");

require_role('receptionist');

$model = new ReceptionistModel();

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$summary = $model->getDailySummary($date);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daily Summary</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>

<?php include_once("menu.php"); ?>

<div class="container">
    <h2>Daily Appointment Summary Report</h2>

    <form method="get">
        <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
        <input type="submit" value="Show">
    </form>

    <table>
        <tr>
            <th>Date</th>
            <th>Total</th>
            <th>Check-ins</th>
            <th>Completed</th>
            <th>Cancelled</th>
            <th>Revenue Collected</th>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars($date); ?></td>
            <td><?php echo $summary['total_appointments']; ?></td>
            <td><?php echo $summary['check_ins']; ?></td>
            <td><?php echo $summary['completed']; ?></td>
            <td><?php echo $summary['cancelled']; ?></td>
            <td><?php echo $summary['revenue']; ?></td>
        </tr>
    </table>

    <button onclick="window.print()">Print Report</button>
</div>

</body>
</html>
