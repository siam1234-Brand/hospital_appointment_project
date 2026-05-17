<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");
require_role('receptionist');
$model = new ReceptionistModel();
$patients = $model->searchPatients("");
$doctors = $model->getApprovedDoctors();
?>
<!DOCTYPE html>
<html>
<head><title>Walk-in Booking</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Book Appointment for Walk-in Patient</h2>
<?php show_msg(); ?>
<form method="post" action="../../controller/receptionist/appointmentHandler.php">
<input type="hidden" name="action" value="book_walkin">
<select name="patient_id"><option value="">Select Patient</option><?php foreach($patients as $p){ ?><option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?> - <?php echo $p['phone']; ?></option><?php } ?></select><br>
<select name="doctor_id" id="doctor_id"><option value="">Select Doctor</option><?php foreach($doctors as $d){ ?><option value="<?php echo $d['id']; ?>"><?php echo $d['name']; ?> - <?php echo $d['specialization']; ?></option><?php } ?></select><br>
Date:<br><input type="date" name="appointment_date" id="appointment_date" onchange="loadReceptionSlots()"><br>
Slot:<br><select name="appointment_time" id="slot_box"><option value="">Select Date First</option></select><br>
<textarea name="reason" placeholder="Reason"></textarea><br>
<input type="submit" value="Book Walk-in Appointment">
</form>
<h2>Doctor Availability By Date</h2>
<p>Choose a doctor and date. Slots load below without page reload.</p>
</div>
<script>
function loadReceptionSlots() {
    var doctor_id = document.getElementById('doctor_id').value;
    var date = document.getElementById('appointment_date').value;
    var slot_box = document.getElementById('slot_box');
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../../api/receptionist_slots.php?doctor_id=' + doctor_id + '&date=' + date, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var slots = JSON.parse(xhr.responseText);
            slot_box.innerHTML = '';
            if (slots.length == 0) { slot_box.innerHTML = '<option value="">No slot</option>'; }
            for (var i=0; i<slots.length; i++) {
                var op = document.createElement('option');
                op.value = slots[i];
                op.innerHTML = slots[i];
                slot_box.appendChild(op);
            }
        }
    }
    xhr.send();
}
</script>
</body>
</html>
