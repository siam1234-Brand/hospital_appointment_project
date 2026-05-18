<?php
include_once("../../helper/auth.php");
include_once("../../model/doctor/DoctorModel.php");

require_role('doctor');

$model = new DoctorModel();
$doctor_id = $model->getDoctorIdByUser($_SESSION['user_id']);

$today = [];
$week = [];
$all = [];

if ($doctor_id != 0) {
    $today = $model->getDoctorScheduleToday($doctor_id);
    $week = $model->getDoctorWeeklyCalendar($doctor_id);
    $all = $model->getAllDoctorAppointments($doctor_id);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Appointments</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>

<?php include_once("menu.php"); ?>

<div class="container">
    <h2>Doctor Appointments</h2>

    <?php show_msg(); ?>

    <?php if ($doctor_id == 0) { ?>
        <div class="card">
            Doctor profile not found for this logged-in account.
            Please check the doctors table user_id.
        </div>
    <?php } ?>

    <h2>Today's Schedule</h2>

    <table>
        <tr>
            <th>Time</th>
            <th>Patient</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php if (count($today) > 0) { ?>
            <?php foreach($today as $a){ ?>
                <tr id="row_<?php echo $a['id']; ?>">
                    <td><?php echo htmlspecialchars($a['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['reason']); ?></td>
                    <td id="status_<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['status']); ?></td>
                    <td>
                        <?php if ($a['status'] == 'pending') { ?>
                            <form method="post" action="../../controller/doctor/appointmentHandler.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <button name="status" value="confirmed">Confirm</button>
                                <button name="status" value="rejected">Reject</button>
                            </form>
                        <?php } ?>

                        <?php if ($a['status'] == 'confirmed') { ?>
                            <button onclick="checkIn(<?php echo $a['id']; ?>)">Check In</button>
                        <?php } ?>

                        <?php if ($a['status'] == 'checked_in') { ?>
                            <a href="complete.view.php?id=<?php echo $a['id']; ?>">Complete & Note</a>
                            <form method="post" action="../../controller/doctor/appointmentHandler.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <button name="status" value="no_show">No Show</button>
                            </form>
                        <?php } ?>

                        <?php if ($a['status'] == 'completed') { ?>
                            Completed
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">No appointments found for today.</td>
            </tr>
        <?php } ?>
    </table>

    <h2>Weekly Calendar</h2>

    <table>
        <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Patient</th>
            <th>Status</th>
        </tr>

        <?php if (count($week) > 0) { ?>
            <?php foreach($week as $a){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($a['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['status']); ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4">No appointments found for next 7 days.</td>
            </tr>
        <?php } ?>
    </table>

    <h2>All My Appointments</h2>

    <table>
        <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Patient</th>
            <th>Reason</th>
            <th>Status</th>
        </tr>

        <?php if (count($all) > 0) { ?>
            <?php foreach($all as $a){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($a['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['reason']); ?></td>
                    <td><?php echo htmlspecialchars($a['status']); ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">No appointments found for this doctor.</td>
            </tr>
        <?php } ?>
    </table>
</div>

<script>
function checkIn(id) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../api/doctor_checkin.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);

            if (data.success) {
                document.getElementById('status_' + id).innerHTML = 'checked_in';
                location.reload();
            } else {
                alert(data.message);
            }
        }
    };

    xhr.send('id=' + encodeURIComponent(id));
}
</script>

</body>
</html>
