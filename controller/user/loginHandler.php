<?php
include_once("../../model/user/UserModel.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $model = new UserModel();
        $success = $model->login($email, $password);
        if ($success === TRUE) {
            if ($_SESSION['role'] == 'patient') {
                header('Location: ../../view/patient/dashboard.view.php');
            } elseif ($_SESSION['role'] == 'doctor') {
                header('Location: ../../view/doctor/dashboard.view.php');
            } elseif ($_SESSION['role'] == 'receptionist') {
                header('Location: ../../view/receptionist/dashboard.view.php');
            } else {
                header('Location: ../../view/admin/dashboard.view.php');
            }
        } else {
            header('Location: ../../view/login.view.php');
        }
    } else {
        $_SESSION['error']['login'] = "Email and password are required";
        header('Location: ../../view/login.view.php');
    }
}
?>
