<?php
include_once("../../helper/auth.php");
include_once("../../model/patient/PatientModel.php");
require_role('patient');
$model = new PatientModel();
$patient_id = $model->getPatientIdByUser($_SESSION['user_id']);
$doctors = $model->getApprovedDoctors();
$deps = $model->getDependents($patient_id);
$selectedDoctor = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : "";
?>
<!DOCTYPE html>
<html>
<head><title>Book Appointment</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
    <h2>Book Appointment</h2>
    <?php show_msg(); ?>
    <form method="post" action="../../controller/patient/appointmentHandler.php">
        <input type="hidden" name="action" value="book">
        <select name="doctor_id" id="doctor_id">
            <option value="">Select Doctor</option>
            <?php foreach($doctors as $d){ ?>
                <option value="<?php echo $d['id']; ?>" <?php selected($selectedDoctor, $d['id']); ?>><?php echo $d['name']; ?> - <?php echo $d['specialization']; ?> - Fee <?php echo $d['consultation_fee']; ?></option>
            <?php } ?>
        </select><br>
        Book For:<br>
        <select name="dependent_id">
            <option value="0">Self</option>
            <?php foreach($deps as $dep){ ?>
                <option value="<?php echo $dep['id']; ?>"><?php echo $dep['name']; ?> (<?php echo $dep['relationship']; ?>)</option>
            <?php } ?>
        </select><br>
        Date:<br>
        <input type="date" name="appointment_date" id="appointment_date" onchange="loadSlots()"><br>
        Available Slot:<br>
        <select name="appointment_time" id="slot_box">
            <option value="">Select Date First</option>
        </select><br>
        <textarea name="reason" placeholder="Reason for visit"></textarea><br>
        <input type="submit" value="Book Appointment">
    </form>
</div>
<script>
function loadSlots() {
    var doctor_id = document.getElementById('doctor_id').value;
    var date = document.getElementById('appointment_date').value;
    var slot_box = document.getElementById('slot_box');
    slot_box.innerHTML = '<option>Loading...</option>';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../../api/patient_slots.php?doctor_id=' + doctor_id + '&date=' + date, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var slots = JSON.parse(xhr.responseText);
            slot_box.innerHTML = '';
            if (slots.length === 0) {
                slot_box.innerHTML = '<option value="">No slot available</option>';
            } else {
                for (var i = 0; i < slots.length; i++) {
                    var op = document.createElement('option');
                    op.value = slots[i];
                    op.innerHTML = slots[i];
                    slot_box.appendChild(op);
                }
            }
        }
    }
    xhr.send();
}
</script>
</body>
</html>
