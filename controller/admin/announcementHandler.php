<?php
include_once("../../helper/auth.php");
include_once("../../model/admin/AdminModel.php");
require_role('admin');
$model = new AdminModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] == 'add') {
        if (empty($_POST['title']) || empty($_POST['body'])) {
            set_msg("Announcement title and body required");
        } else {
            $model->addAnnouncement($_SESSION['user_id'], $_POST['title'], $_POST['body'], $_POST['target_role']);
            set_msg("Announcement published");
        }
    } elseif ($_POST['action'] == 'delete') {
        $model->deleteAnnouncement($_POST['id']);
        set_msg("Announcement deleted");
    }
}
header('Location: ../../view/admin/announcements.view.php');
?>
