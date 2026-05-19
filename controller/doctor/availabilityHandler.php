<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['day_of_week']) || empty($_POST['start_time']) || empty($_POST['end_time'])) {
        set_msg("Day, start time and end time are required");
    } else {
        $model->saveAvailability($doctor_id, $_POST['day_of_week'], $_POST['start_time'], $_POST['end_time'], $_POST['slot_duration_minutes'], $_POST['is_available']);
        set_msg("Availability saved");
    }
}
header('Location: ../../view/doctor/availability.view.php');
?>
