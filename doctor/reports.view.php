<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);

// Read the chosen period from URL, default to 'month'
$period = isset($_GET['period']) && in_array($_GET['period'], ['day', 'week', 'month']) ? $_GET['period']: 'month';
$earnings  = $model->getEarningsReport($doctor_id, $period);
$stats     = $model->getDoctorStats($doctor_id);
$busyTimes  = $model->getDoctorBusiestTimes($doctor_id); 
$followUps = $model->getFollowUps($doctor_id);
$reviews   = $model->getDoctorReviews($doctor_id);
?>
<!DOCTYPE html>
<html>
<head><title>Doctor Reports</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Earnings Report</h2>

<a href="reports.view.php?period=day">Today</a> |
<a href="reports.view.php?period=week">This Week</a> |
<a href="reports.view.php?period=month">This Month</a>

<p>Currently showing: <strong><?php echo htmlspecialchars($period); ?></strong></p>

<table>
    <tr>
        <th>Date</th>
        <th>Completed Appointments</th>
        <th>Total Earnings</th>
    </tr>

    <?php if (count($earnings) > 0) { ?>
        <?php foreach ($earnings as $e) { ?>
            <tr>
                <td><?php echo htmlspecialchars($e['appointment_date']); ?></td>
                <td><?php echo $e['completed_count']; ?></td>
                <td><?php echo number_format($e['total_earning'], 2); ?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <!-- This shows when there's no data for the chosen period -->
        <tr><td colspan="3">No earnings found for this period.</td></tr>
    <?php } ?>
</table>
<h2>Appointment Statistics</h2>

<div class="card">
    Total: <?php echo $stats['total']; ?> |
    Completed: <?php echo $stats['completed']; ?> |
    Cancelled: <?php echo $stats['cancelled']; ?> |
    No Show: <?php echo $stats['no_show']; ?>
</div>

<h3>Busiest Days</h3>
<table>
    <tr>
        <th>Day</th>
        <th>Completed Appointments</th>
    </tr>
    <?php if (count($busyTimes['busiest_days']) > 0) { ?>
        <?php foreach ($busyTimes['busiest_days'] as $row) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['day_name']); ?></td>
                <td><?php echo $row['total']; ?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td colspan="2">No data yet.</td></tr>
    <?php } ?>
</table>

<h3>Busiest Times</h3>
<table>
    <tr>
        <th>Hour</th>
        <th>Completed Appointments</th>
    </tr>
    <?php if (count($busyTimes['busiest_hours']) > 0) { ?>
        <?php foreach ($busyTimes['busiest_hours'] as $row) { ?>
            <tr>
                <!-- converts 9 → "09:00 AM", 14 → "02:00 PM" -->
                <td><?php echo date('h:i A', mktime($row['hour'], 0, 0)); ?></td>
                <td><?php echo $row['total']; ?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td colspan="2">No data yet.</td></tr>
    <?php } ?>
</table>
<h2>Patient Reviews</h2>
<table><tr><th>Patient</th><th>Rating</th><th>Review</th><th>Reply</th></tr>
<?php foreach($reviews as $r){ ?>
<tr><form method="post" action="../../controller/doctor/reviewReplyHandler.php">
    <td><?php echo $r['patient_name']; ?></td><td><?php echo $r['rating']; ?></td>
    <td><?php echo $r['review_text']; ?></td>
    <td><input type="hidden" name="id" value="<?php echo $r['id']; ?>"><input type="text" name="reply" value="<?php echo $r['doctor_reply']; ?>"><input type="submit" value="Reply"></td>
</form></tr>
<?php } ?>
</table>
</div>
</body>
</html>
