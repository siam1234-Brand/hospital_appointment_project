<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");

require_role('receptionist');

$model = new ReceptionistModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? "";

    if ($action == 'book_walkin') {
        $patient_id = $_POST['patient_id'] ?? "";
        $doctor_id = $_POST['doctor_id'] ?? "";
        $appointment_date = $_POST['appointment_date'] ?? "";
        $appointment_time = $_POST['appointment_time'] ?? "";
        $reason = $_POST['reason'] ?? "";

        if ($patient_id == "" || $doctor_id == "" || $appointment_date == "" || $appointment_time == "") {
            set_msg("Patient, doctor, date and slot are required");
        } else {
            $ok = $model->bookAppointment($patient_id, null, $doctor_id, $appointment_date, $appointment_time, $reason, 'receptionist');
            set_msg($ok ? "Walk-in appointment booked" : "Slot not available or booking failed");
        }

        header('Location: ../../view/receptionist/book_walkin.view.php');
        exit();
    }

    if ($action == 'checkin') {
        $id = $_POST['id'] ?? "";
        if ($id != "") {
            $ok = $model->updateAppointmentStatus($id, 'checked_in');
            set_msg($ok ? "Patient checked in" : "Check-in failed");
        }
    }

    if ($action == 'cancel') {
        $id = $_POST['id'] ?? "";
        if ($id != "") {
            $ok = $model->updateAppointmentStatus($id, 'cancelled');
            set_msg($ok ? "Appointment cancelled" : "Cancel failed");
        }
    }
}

header('Location: ../../view/receptionist/dashboard.view.php');
exit();
?>
