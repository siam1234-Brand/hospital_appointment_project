<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action == 'add') {
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['license_number'])) {
            set_msg("Doctor name, email, password and license are required");
        } else {
            $model->adminCreateDoctor($_POST['name'], $_POST['email'], $_POST['password'], $_POST['phone'], $_POST['specialization_id'], $_POST['bio'], $_POST['consultation_fee'], $_POST['license_number'], $_POST['experience_years']);
            set_msg("Doctor account created and approved");
        }
    } elseif ($action == 'update') {
        $model->adminUpdateDoctor($_POST['doctor_id'], $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['specialization_id'], $_POST['bio'], $_POST['consultation_fee'], $_POST['license_number'], $_POST['experience_years'], $_POST['is_approved']);
        set_msg("Doctor updated / approved status changed");
    } elseif ($action == 'deactivate') {
        $model->deactivateDoctor($_POST['doctor_id']);
        set_msg("Doctor deactivated");
    }
}
header('Location: ../../view/admin/manage_doctors.view.php');
?>
