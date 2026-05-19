<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");

require_role('doctor');

$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = ['confirmed', 'rejected', 'no_show'];
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? "";

    if ($doctor_id == 0) {
        set_msg("Doctor profile not found for this account");
    } elseif ($id != 0 && in_array($status, $allowed)) {
        $ok = $model->updateAppointmentStatus($id, $status, $doctor_id);
        set_msg($ok ? "Appointment status updated" : "Appointment update failed");
    } else {
        set_msg("Invalid appointment action");
    }
}

header('Location: ../../view/doctor/appointments.view.php');
exit();
?>
