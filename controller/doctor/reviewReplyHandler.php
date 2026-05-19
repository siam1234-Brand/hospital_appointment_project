<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->replyReview($_POST['id'], $doctor_id, $_POST['reply']);
    set_msg("Review reply saved");
}
header('Location: ../../view/doctor/messages.view.php');
?>
