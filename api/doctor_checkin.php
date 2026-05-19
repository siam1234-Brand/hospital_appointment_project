<?php
include_once("../helper/auth.php");
include_once("../model/doctor/DoctorModel.php");

require_role('doctor');

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);

$ok = false;

if ($id != 0 && $doctor_id != 0) {
    $ok = $model->updateAppointmentStatus($id, 'checked_in', $doctor_id);
}

echo json_encode([
    'success' => $ok,
    'message' => $ok ? 'Checked in' : 'Check-in failed'
]);
?>
