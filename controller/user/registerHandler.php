<?php
include_once("../../model/user/UserModel.php");
include_once("../../helper/auth.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['phone'])) {
        set_msg("Name, email, password and phone are required");
        header('Location: ../../view/register.view.php');
        exit();
    }
    $model = new UserModel();
    $ok = $model->registerPatient($_POST['name'], $_POST['email'], $_POST['password'], $_POST['phone'], $_POST['date_of_birth'], $_POST['blood_group'], $_POST['gender'], $_POST['address'], $_POST['emergency_contact_name'], $_POST['emergency_contact_phone']);
    if ($ok) {
        set_msg("Registration successful. Now login.");
        header('Location: ../../view/login.view.php');
    } else {
        set_msg("Registration failed. Email may already exist.");
        header('Location: ../../view/register.view.php');
    }
}
?>
