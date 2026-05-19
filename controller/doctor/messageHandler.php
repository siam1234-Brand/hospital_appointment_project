<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->replyMessage($_POST['id'], $doctor_id, $_POST['reply']);
    set_msg("Message replied");
}
header('Location: ../../view/doctor/messages.view.php');
?>
