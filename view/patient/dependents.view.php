<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);
$dependents = $model->getDependents($patient_id);
?>
<!DOCTYPE html>
<html>
<head><title>Dependents</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
    <h2>Family Dependents</h2>
    <?php show_msg(); ?>
    <h3>Add Dependent</h3>
    <form method="post" action="../../controller/patient/dependentHandler.php">
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="Name"><br>
        <input type="date" name="date_of_birth"><br>
        <input type="text" name="relationship" placeholder="Relationship"><br>
        <input type="text" name="blood_group" placeholder="Blood Group"><br>
        <input type="submit" value="Add">
    </form>
    <table>
        <tr><th>Name</th><th>DOB</th><th>Relationship</th><th>Blood</th><th>Action</th></tr>
        <?php foreach ($dependents as $d) { ?>
        <tr>
            <form method="post" action="../../controller/patient/dependentHandler.php">
            <td><input type="text" name="name" value="<?php echo $d['name']; ?>"></td>
            <td><input type="date" name="date_of_birth" value="<?php echo $d['date_of_birth']; ?>"></td>
            <td><input type="text" name="relationship" value="<?php echo $d['relationship']; ?>"></td>
            <td><input type="text" name="blood_group" value="<?php echo $d['blood_group']; ?>"></td>
            <td>
                <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                <button name="action" value="update">Update</button>
                <button name="action" value="delete">Delete</button>
            </td>
            </form>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
