<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] == 'add') {
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password'])) {
            set_msg("Name, email and password required");
        } else {
            $model->createStaff($_POST['name'], $_POST['email'], $_POST['password'], $_POST['phone'], 'receptionist');
            set_msg("Receptionist account created");
        }
    } elseif ($_POST['action'] == 'update') {
        $model->updateStaff($_POST['id'], $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['is_active']);
        set_msg("Receptionist updated");
    }
}
header('Location: ../../view/admin/manage_receptionists.view.php');
