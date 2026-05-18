<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");

require_role('receptionist');

$model = new ReceptionistModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_id = $_POST['bill_id'] ?? "";
    $payment_method = $_POST['payment_method'] ?? "";

    if ($bill_id == "" || $payment_method == "") {
        set_msg("Bill and payment method are required");
        header('Location: ../../view/receptionist/payments.view.php');
        exit();
    }

    $ok = $model->markBillPaid($bill_id, $payment_method);

    if ($ok) {
        set_msg("Payment processed");
        header('Location: ../../view/receptionist/receipt.view.php?bill_id=' . $bill_id);
        exit();
    }

    set_msg("Payment failed or bill already paid");
    header('Location: ../../view/receptionist/payments.view.php');
    exit();
}

header('Location: ../../view/receptionist/payments.view.php');
exit();
?>
