<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = ['confirmed','rejected','no_show'];
    if (in_array($_POST['status'], $allowed)) {
        $model->updateAppointmentStatus($_POST['id'], $_POST['status']);
        set_msg("Appointment status updated");
    }
}
header('Location: ../../view/doctor/appointments.view.php');
?>
