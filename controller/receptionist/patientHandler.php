<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");
require_role('receptionist');
$model = new ReceptionistModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['phone'])) {
        set_msg("Name, email, password and phone are required");
    } else {
        $ok = $model->receptionistRegisterPatient($_POST['name'], $_POST['email'], $_POST['password'], $_POST['phone'], $_POST['date_of_birth'], $_POST['blood_group'], $_POST['gender'], $_POST['address'], $_POST['emergency_contact_name'], $_POST['emergency_contact_phone']);
        set_msg($ok ? "Patient registered" : "Patient registration failed");
    }
}
header('Location: ../../view/receptionist/patients.view.php');
?>
