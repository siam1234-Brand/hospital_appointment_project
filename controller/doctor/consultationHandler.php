<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['symptoms']) || empty($_POST['diagnosis']) || empty($_POST['prescription'])) {
        set_msg("Symptoms, diagnosis and prescription are required");
    } else {
        $model->completeAppointment($_POST['appointment_id'], $doctor_id, $_POST['patient_id'], $_POST['symptoms'], $_POST['diagnosis'], $_POST['prescription'], $_POST['follow_up_date']);
        set_msg("Appointment completed and consultation note saved");
    }
}
header('Location: ../../view/doctor/appointments.view.php');
?>
