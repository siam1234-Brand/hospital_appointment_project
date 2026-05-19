<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['admin_response'])) {
        set_msg("Admin response required");
    } else {
        $model->resolveComplaint($_POST['id'], $_POST['admin_response']);
        set_msg("Complaint resolved");
    }
}
header('Location: ../../view/admin/complaints.view.php');
