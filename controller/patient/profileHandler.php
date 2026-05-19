<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action == 'update_profile') {
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['phone'])) {
            set_msg("Name, email and phone are required");
            header('Location: ../../view/patient/profile.view.php');
            exit();
        }
        $profile_pic = "";
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['name'] != "") {
            $target_dir = "../../uploads/profile/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            $file_name = time() . "_" . basename($_FILES['profile_pic']['name']);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                $profile_pic = "uploads/profile/" . $file_name;
            }
        }
        $model->updateUserBasic($_SESSION['user_id'], $_POST['name'], $_POST['email'], $_POST['phone'], $profile_pic);
        $model->updatePatientProfile($_SESSION['user_id'], $_POST['date_of_birth'], $_POST['blood_group'], $_POST['gender'], $_POST['address'], $_POST['emergency_contact_name'], $_POST['emergency_contact_phone']);
        $_SESSION['name'] = $_POST['name'];
        set_msg("Profile updated successfully");
    } elseif ($action == 'update_history') {
        $model->updateMedicalHistory($patient_id, $_POST['medical_history_notes']);
        set_msg("Medical history updated");
    } elseif ($action == 'change_password') {
        if (empty($_POST['old_password']) || empty($_POST['new_password'])) {
            set_msg("Old and new password are required");
        } else {
            $ok = $model->changePassword($_SESSION['user_id'], $_POST['old_password'], $_POST['new_password']);
            set_msg($ok ? "Password changed" : "Old password is wrong");
        }
    }
}
header('Location: ../../view/patient/profile.view.php');
?>
