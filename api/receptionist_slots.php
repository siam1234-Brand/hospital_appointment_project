<?php
include_once("../helper/auth.php");
include_once("../model/receptionist/ReceptionistModel.php");

require_role('receptionist');
header('Content-Type: application/json');

$model = new ReceptionistModel();

$doctor_id = $_GET['doctor_id'] ?? 0;
$date = $_GET['date'] ?? "";

$slots = [];

if ($doctor_id != 0 && $date != "") {
    $slots = $model->getAvailableSlots($doctor_id, $date);
}

echo json_encode($slots);
?>
