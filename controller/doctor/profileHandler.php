<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");
require_role('doctor');
$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['bio']) || empty($_POST['consultation_fee']) || empty($_POST['license_number'])) {
        set_msg("Bio, fee and license number are required");
    } else {
        $photo = "";
        if (isset($_FILES['photo']) && $_FILES['photo']['name'] != "") {
            $target_dir = "../../uploads/doctor/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            $file_name = time() . "_" . basename($_FILES['photo']['name']);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $photo = "uploads/doctor/" . $file_name;
            }
        }
        $model->updateDoctorProfile($doctor_id, $_POST['bio'], $_POST['specialization_id'], $_POST['consultation_fee'], $_POST['license_number'], $_POST['experience_years'], $photo);
        set_msg("Doctor profile updated");
    }
}
header('Location: ../../view/doctor/profile.view.php');
?>
