<?php
include_once("../../helper/auth.php");
include_once("../../model/receptionist/ReceptionistModel.php");

require_role('receptionist');

$model = new ReceptionistModel();

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$patients = $model->searchPatients($keyword);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patients</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>

<?php include_once("menu.php"); ?>

<div class="container">
    <h2>Search Patients</h2>

    <?php show_msg(); ?>

    <form method="get">
        <input type="text" name="keyword" placeholder="Name, phone or ID" value="<?php echo htmlspecialchars($keyword); ?>">
        <input type="submit" value="Search">
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Status</th>
        </tr>

        <?php if (count($patients) > 0) { ?>
            <?php foreach($patients as $p){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['id']); ?></td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars($p['phone']); ?></td>
                    <td><?php echo htmlspecialchars($p['email']); ?></td>
                    <td><?php echo $p['is_active'] == 1 ? "Active" : "Inactive"; ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">No patients found.</td>
            </tr>
        <?php } ?>
    </table>

    <h2>Register New Patient</h2>

    <form method="post" action="../../controller/receptionist/patientHandler.php">
        <input type="text" name="name" placeholder="Name"><br>
        <input type="email" name="email" placeholder="Email"><br>
        <input type="password" name="password" placeholder="Password"><br>
        <input type="text" name="phone" placeholder="Phone"><br>
        <input type="date" name="date_of_birth"><br>
        <input type="text" name="blood_group" placeholder="Blood Group"><br>

        <select name="gender">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select><br>

        <textarea name="address" placeholder="Address"></textarea><br>
        <input type="text" name="emergency_contact_name" placeholder="Emergency Contact Name"><br>
        <input type="text" name="emergency_contact_phone" placeholder="Emergency Contact Phone"><br>
        <input type="submit" value="Register Patient">
    </form>
</div>

</body>
</html>
