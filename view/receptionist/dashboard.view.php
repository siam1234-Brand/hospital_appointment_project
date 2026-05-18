<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");

require_role('receptionist');

$model = new ReceptionistModel();

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : "";

$today = $model->getAllAppointments($date, $status);
$queue = $model->getWaitingQueue();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receptionist Dashboard</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>

<?php include_once("menu.php"); ?>

<div class="container">
    <h2>Receptionist Dashboard - Daily Schedule</h2>

    <?php show_msg(); ?>

    <form method="get">
        <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
        <select name="status">
            <option value="">All Status</option>
            <?php foreach(['pending','confirmed','checked_in','completed','cancelled','no_show'] as $st){ ?>
                <option value="<?php echo $st; ?>" <?php selected($status,$st); ?>>
                    <?php echo $st; ?>
                </option>
            <?php } ?>
        </select>
        <input type="submit" value="Filter">
    </form>

    <table>
        <tr>
            <th>Doctor</th>
            <th>Time</th>
            <th>Patient</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php if (count($today) > 0) { ?>
            <?php foreach($today as $a){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['appointment_time']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($a['patient_name']); ?>
                        (<?php echo htmlspecialchars($a['phone']); ?>)
                    </td>
                    <td><?php echo htmlspecialchars($a['status']); ?></td>
                    <td>
                        <?php if ($a['status'] == 'pending' || $a['status'] == 'confirmed') { ?>
                            <form method="post" action="../../controller/receptionist/appointmentHandler.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <button name="action" value="checkin">Check In</button>
                                <button name="action" value="cancel" onclick="return confirm('Cancel this appointment?');">Cancel</button>
                            </form>
                        <?php } elseif ($a['status'] == 'checked_in') { ?>
                            Waiting
                        <?php } else { ?>
                            No action
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">No appointments found.</td>
            </tr>
        <?php } ?>
    </table>

    <h2>Waiting Room Queue</h2>

    <table>
        <tr>
            <th>Doctor</th>
            <th>Time</th>
            <th>Patient</th>
        </tr>

        <?php if (count($queue) > 0) { ?>
            <?php foreach($queue as $q){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($q['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($q['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($q['patient_name']); ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="3">No patients in waiting queue.</td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
