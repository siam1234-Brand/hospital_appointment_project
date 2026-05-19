<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");

require_role('receptionist');

$model = new ReceptionistModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? "";
    $email = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";
    $phone = $_POST['phone'] ?? "";

    $dob = $_POST['date_of_birth'] ?? null;
    $blood = $_POST['blood_group'] ?? "";
    $gender = $_POST['gender'] ?? "";
    $address = $_POST['address'] ?? "";
    $emergencyName = $_POST['emergency_contact_name'] ?? "";
    $emergencyPhone = $_POST['emergency_contact_phone'] ?? "";

    if ($name == "" || $email == "" || $password == "" || $phone == "") {
        set_msg("Name, email, password and phone are required");
    } else {
        $ok = $model->receptionistRegisterPatient($name, $email, $password, $phone, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone);
        set_msg($ok ? "Patient registered" : "Patient registration failed. Email may already exist.");
    }
}

header('Location: ../../view/receptionist/patients.view.php');
exit();
?>
