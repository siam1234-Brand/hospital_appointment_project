<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");
require_role('receptionist');
$model = new ReceptionistModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['bill_id']) || empty($_POST['payment_method'])) {
        set_msg("Bill and payment method are required");
        header('Location: ../../view/receptionist/payments.view.php');
    } else {
        $model->markBillPaid($_POST['bill_id'], $_POST['payment_method']);
        set_msg("Payment processed");
        header('Location: ../../view/receptionist/receipt.view.php?bill_id=' . $_POST['bill_id']);
    }
}
?>
