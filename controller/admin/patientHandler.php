<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->deactivatePatient($_POST['patient_id']);
    set_msg("Patient account deactivated");
}
header('Location: ../../view/admin/manage_patients.view.php');
?>

