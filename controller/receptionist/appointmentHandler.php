<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");
require_role('receptionist');
$model = new ReceptionistModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action == 'book_walkin') {
        if (empty($_POST['patient_id']) || empty($_POST['doctor_id']) || empty($_POST['appointment_date']) || empty($_POST['appointment_time'])) {
            set_msg("Patient, doctor, date and slot are required");
        } else {
            $ok = $model->bookAppointment($_POST['patient_id'], 0, $_POST['doctor_id'], $_POST['appointment_date'], $_POST['appointment_time'], $_POST['reason'], 'receptionist');
            set_msg($ok ? "Walk-in appointment booked" : "Slot already booked");
        }
        header('Location: ../../view/receptionist/book_walkin.view.php');
        exit();
    } elseif ($action == 'checkin') {
        $model->updateAppointmentStatus($_POST['id'], 'checked_in');
        set_msg("Patient checked in");
    } elseif ($action == 'cancel') {
        $model->updateAppointmentStatus($_POST['id'], 'cancelled');
        set_msg("Appointment cancelled");
    }
}
header('Location: ../../view/receptionist/dashboard.view.php');
?>
