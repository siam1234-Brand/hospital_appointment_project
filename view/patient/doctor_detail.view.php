<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$d = $model->getDoctorDetail($id);
$availability = $model->getDoctorAvailability($id);
$reviews = $model->getDoctorReviews($id);
?>
<!DOCTYPE html>
<html>
<head><title>Doctor Detail</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
    <h2>Doctor Detail</h2>
    <?php if ($d != null) { ?>
    <div class="card">
        <?php if ($d['photo_path'] != "") { ?><img class="small-img" src="../../<?php echo $d['photo_path']; ?>"><?php } ?>
        <h3><?php echo $d['name']; ?></h3>
        <p>Specialization: <?php echo $d['specialization']; ?></p>
        <p>Bio: <?php echo $d['bio']; ?></p>
        <p>Experience: <?php echo $d['experience_years']; ?> years</p>
        <p>Fee: <?php echo $d['consultation_fee']; ?></p>
        <p>Average Rating: <?php echo round($d['avg_rating'], 2); ?></p>
        <a href="book.view.php?doctor_id=<?php echo $d['id']; ?>">Book Appointment</a>
    </div>
    <h3>Available Days</h3>
    <table><tr><th>Day</th><th>Start</th><th>End</th><th>Duration</th></tr>
    <?php foreach($availability as $a){ ?>
        <tr><td><?php echo $a['day_of_week']; ?></td><td><?php echo $a['start_time']; ?></td><td><?php echo $a['end_time']; ?></td><td><?php echo $a['slot_duration_minutes']; ?> min</td></tr>
    <?php } ?>
    </table>
    <h3>Reviews</h3>
    <?php foreach($reviews as $r){ ?>
        <div class="card"><b><?php echo $r['patient_name']; ?></b> Rating: <?php echo $r['rating']; ?><br><?php echo $r['review_text']; ?><br><?php if($r['doctor_reply'] != '') echo 'Doctor Reply: '.$r['doctor_reply']; ?></div>
    <?php } ?>
    <?php } else { echo "Doctor not found"; } ?>
</div>
</body>
</html>
