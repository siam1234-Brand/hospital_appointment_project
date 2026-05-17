<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->submitPaymentIntent($_POST['bill_id'], $patient_id, $_POST['payment_method']);
    set_msg("Payment intent submitted. Receptionist will confirm payment.");
}
header('Location: ../../view/patient/bills.view.php');
?>
