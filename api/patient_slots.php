<?php
include_once("../helper/auth.php");
include_once("../model/patient/PatientModel.php");
require_role('patient');
header('Content-Type: application/json');
$model = new PatientModel();
$doctor_id = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : "";
$slots = [];
if ($doctor_id != 0 && $date != "") {
    $slots = $model->getAvailableSlots($doctor_id, $date);
}
echo json_encode($slots);
?>
