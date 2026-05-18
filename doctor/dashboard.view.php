<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");

require_role('doctor');

$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);

$today = [];
$stats = ['completed'=>0, 'cancelled'=>0, 'no_show'=>0, 'total'=>0];

if ($doctor_id != 0) {
    $today = $model->getDoctorScheduleToday($doctor_id);
    $stats = $model->getDoctorStats($doctor_id);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>

<?php include_once("menu.php"); ?>

<div class="container">
    <h2>Doctor Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></p>

    <?php show_msg(); ?>

    <?php if ($doctor_id == 0) { ?>
        <div class="card">Doctor profile not found for this logged-in account.</div>
    <?php } ?>

    <div class="card">Today's Appointments: <?php echo count($today); ?></div>
    <div class="card">
        Total: <?php echo $stats['total']; ?> |
        Completed: <?php echo $stats['completed']; ?> |
        Cancelled: <?php echo $stats['cancelled']; ?> |
        No Show: <?php echo $stats['no_show']; ?>
    </div>
</div>

</body>
</html>
