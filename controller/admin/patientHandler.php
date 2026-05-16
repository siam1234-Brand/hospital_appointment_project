<?php
include_once("../../helper/auth.php");
include_once("../../model/HospitalModel.php");
require_role('admin');
$model = new HospitalModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->deactivatePatient($_POST['patient_id']);
    set_msg("Patient account deactivated");
}
header('Location: ../../view/admin/manage_patients.view.php');
?>
