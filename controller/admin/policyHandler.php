<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['setting_value'] === '') {
        set_msg("Policy value cannot be empty");
    } else {
        $model->updateSetting($_POST['setting_name'], $_POST['setting_value']);
        set_msg("Policy updated");
    }
}
header('Location: ../../view/admin/policies.view.php');
?>
