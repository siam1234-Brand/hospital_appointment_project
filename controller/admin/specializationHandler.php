<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] == 'add') {
        if (empty($_POST['name'])) { set_msg("Specialization name required"); }
        else { $model->addSpecialization($_POST['name'], $_POST['description']); set_msg("Specialization added"); }
    } elseif ($_POST['action'] == 'update') {
        $model->updateSpecialization($_POST['id'], $_POST['name'], $_POST['description']); set_msg("Specialization updated");
    } elseif ($_POST['action'] == 'delete') {
        $model->deleteSpecialization($_POST['id']); set_msg("Specialization deleted");
    }
}
header('Location: ../../view/admin/manage_specializations.view.php');
?>
