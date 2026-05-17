<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action == 'add') {
        if (empty($_POST['name']) || empty($_POST['relationship'])) {
            set_msg("Dependent name and relationship are required");
        } else {
            $model->addDependent($patient_id, $_POST['name'], $_POST['date_of_birth'], $_POST['relationship'], $_POST['blood_group']);
            set_msg("Dependent added");
        }
    } elseif ($action == 'update') {
        $model->updateDependent($_POST['id'], $patient_id, $_POST['name'], $_POST['date_of_birth'], $_POST['relationship'], $_POST['blood_group']);
        set_msg("Dependent updated");
    } elseif ($action == 'delete') {
        $model->deleteDependent($_POST['id'], $patient_id);
        set_msg("Dependent deleted");
    }
}
header('Location: ../../view/patient/dependents.view.php');
?>
