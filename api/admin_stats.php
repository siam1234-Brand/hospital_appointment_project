<?php
include_once("../helper/auth.php");
include_once("../model/HospitalModel.php");
require_role('admin');
header('Content-Type: application/json');
$model = new HospitalModel();
$stats = $model->getAdminStats();
echo json_encode($stats);
?>
