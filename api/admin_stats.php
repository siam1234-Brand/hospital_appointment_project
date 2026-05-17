<?php
include_once("../helper/auth.php");
include_once("../model/admin/AdminModel.php");
require_role('admin');
header('Content-Type: application/json');
$model = new AdminModel();
$stats = $model->getAdminStats();
echo json_encode($stats);
?>
