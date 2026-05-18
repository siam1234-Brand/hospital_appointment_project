<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] == 'add') {
        if (empty($_POST['leave_date'])) {
            set_msg("Leave date is required");
        } else {
            $model->addLeaveDate($doctor_id, $_POST['leave_date'], $_POST['reason']);
            set_msg("Leave date added");
        }
    } elseif ($_POST['action'] == 'delete') {
        $model->removeLeaveDate($_POST['id'], $doctor_id);
        set_msg("Leave date removed");
    }
}
header('Location: ../../view/doctor/availability.view.php');
?>
