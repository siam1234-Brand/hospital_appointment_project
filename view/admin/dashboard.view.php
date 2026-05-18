<?php
include_once("../../helper/auth.php");
require_role('admin');
?>
<!DOCTYPE html>
<html>
<head><title>Admin Dashboard</title><link rel="stylesheet" href="../../assets/style.css"></head>
<body>
<?php include_once("menu.php"); ?>
<div class="container">
<h2>Admin Dashboard</h2>
<p>Today's total appointments, registered patients, active doctors and pending billings</p>
<div id="stats_box" class="card">Loading...</div>
<?php show_msg(); ?>
</div>
<script>
function loadAdminStats() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../../api/admin_stats.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var s = JSON.parse(xhr.responseText);
            document.getElementById('stats_box').innerHTML =
                'Today Appointments: ' + s.todays_appointments + '<br>' +
                'Total Patients: ' + s.total_patients + '<br>' +
                'Active Doctors: ' + s.active_doctors + '<br>' +
                'Pending Bills: ' + s.pending_bills;
        }
    }
    xhr.send();
}
document.addEventListener('DOMContentLoaded', loadAdminStats());
</script>
</body>
</html>
