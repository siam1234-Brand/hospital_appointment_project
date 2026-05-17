<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action == 'add') {
        if (empty($_POST['appointment_id']) || empty($_POST['rating'])) {
            set_msg("Appointment and rating are required");
        } else {
            $app = $model->getAppointment($_POST['appointment_id']);
            if ($app != null && $app['patient_id'] == $patient_id && $app['status'] == 'completed') {
                $ok = $model->addReview($app['id'], $patient_id, $app['doctor_id'], $_POST['rating'], $_POST['review_text']);
                set_msg($ok ? "Review added" : "Review already exists");
            } else {
                set_msg("Only completed appointment can be reviewed");
            }
        }
    } elseif ($action == 'update') {
        $model->updateReview($_POST['id'], $patient_id, $_POST['rating'], $_POST['review_text']);
        set_msg("Review updated");
    } elseif ($action == 'delete') {
        $model->deleteReview($_POST['id'], $patient_id);
        set_msg("Review deleted");
    }
}
header('Location: ../../view/patient/reviews.view.php');
?>
