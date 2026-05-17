<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action == 'book') {
        if (empty($_POST['doctor_id']) || empty($_POST['appointment_date']) || empty($_POST['appointment_time']) || empty($_POST['reason'])) {
            set_msg("Doctor, date, time and reason are required");
        } else {
            $dep = intval($_POST['dependent_id']);
            $ok = $model->bookAppointment($patient_id, $dep, $_POST['doctor_id'], $_POST['appointment_date'], $_POST['appointment_time'], $_POST['reason'], 'patient');
            set_msg($ok ? "Appointment booked. Status is pending." : "Slot already booked or invalid.");
        }
        header('Location: ../../view/patient/book.view.php');
        exit();
    } elseif ($action == 'cancel') {
        $reason = $_POST['cancel_reason'];
        $ok = $model->cancelPatientAppointment($_POST['id'], $patient_id, $reason);
        set_msg($ok ? "Appointment cancelled" : "Cancellation failed. Notice period may be over.");
    } elseif ($action == 'reschedule') {
        if (empty($_POST['appointment_date']) || empty($_POST['appointment_time'])) {
            set_msg("New date and time are required");
        } else {
            $model->rescheduleAppointment($_POST['id'], $patient_id, $_POST['appointment_date'], $_POST['appointment_time'], $_POST['reschedule_note']);
            set_msg("Reschedule request submitted. Status changed to pending.");
        }
    }
}
header('Location: ../../view/patient/appointments.view.php');
?>
