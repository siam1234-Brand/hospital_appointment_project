<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");

require_role('doctor');

$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$app = null;

if ($doctor_id != 0 && $id != 0) {
    $app = $model->getAppointmentForDoctor($id, $doctor_id);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Appointment</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>

<?php include_once("menu.php"); ?>

<div class="container">
    <h2>Complete Appointment and Add Consultation Notes</h2>

    <?php if($app != null){ ?>
        <p>
            Patient: <?php echo htmlspecialchars($app['patient_name']); ?> |
            Date: <?php echo htmlspecialchars($app['appointment_date']); ?>
            <?php echo htmlspecialchars($app['appointment_time']); ?>
        </p>

        <form method="post" action="../../controller/doctor/consultationHandler.php">
            <input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
            <input type="hidden" name="patient_id" value="<?php echo $app['patient_id']; ?>">

            <textarea name="symptoms" placeholder="Symptoms"></textarea><br>
            <textarea name="diagnosis" placeholder="Diagnosis"></textarea><br>
            <textarea name="prescription" placeholder="Prescription details"></textarea><br>

            Follow-up Date:<br>
            <input type="date" name="follow_up_date"><br>

            <input type="submit" value="Complete Appointment">
        </form>
    <?php } else { ?>
        <div class="card">Appointment not found or this appointment does not belong to your doctor account.</div>
    <?php } ?>
</div>

</body>
</html>
