<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$specializations = $model->getSpecializations();
$search = isset($_GET['search']) ? $_GET['search'] : "";
$spec = isset($_GET['specialization_id']) ? $_GET['specialization_id'] : "";
$min = isset($_GET['min_fee']) ? $_GET['min_fee'] : "";
$max = isset($_GET['max_fee']) ? $_GET['max_fee'] : "";
$day = isset($_GET['day']) ? $_GET['day'] : "";
$doctors = $model->getApprovedDoctors($search, $spec, $min, $max, $day);
?>
<!DOCTYPE html>
<html>
<head><title>Doctors</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
    <h2>Browse Approved Doctors</h2>
    <form method="get">
        <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search name or specialization">
        <select name="specialization_id">
            <option value="">All Specializations</option>
            <?php foreach($specializations as $s){ ?>
            <option value="<?php echo $s['id']; ?>" <?php selected($spec, $s['id']); ?>><?php echo $s['name']; ?></option>
            <?php } ?>
        </select>
        <input type="number" name="min_fee" value="<?php echo $min; ?>" placeholder="Min Fee">
        <input type="number" name="max_fee" value="<?php echo $max; ?>" placeholder="Max Fee">
        <select name="day">
            <option value="">Any Day</option>
            <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $dd){ ?>
            <option value="<?php echo $dd; ?>" <?php selected($day, $dd); ?>><?php echo $dd; ?></option>
            <?php } ?>
        </select>
        <input type="submit" value="Search">
    </form>
    <table>
        <tr><th>Name</th><th>Specialization</th><th>Experience</th><th>Fee</th><th>Rating</th><th>Action</th></tr>
        <?php foreach ($doctors as $d) { ?>
        <tr>
            <td><?php echo $d['name']; ?></td>
            <td><?php echo $d['specialization']; ?></td>
            <td><?php echo $d['experience_years']; ?> years</td>
            <td><?php echo $d['consultation_fee']; ?></td>
            <td><?php echo round($d['avg_rating'], 2); ?></td>
            <td><a href="doctor_detail.view.php?id=<?php echo $d['id']; ?>">Details</a> <a href="book.view.php?doctor_id=<?php echo $d['id']; ?>">Book</a></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
