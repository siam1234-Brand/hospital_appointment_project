<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Registration</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h2>Patient Registration</h2>
    <?php
    if (isset($_SESSION['message'])) {
        echo "<p class='msg'>" . $_SESSION['message'] . "</p>";
        unset($_SESSION['message']);
    }
    ?>
    <form method="post" action="../controller/user/registerHandler.php">
        <input type="text" name="name" placeholder="Full Name"><br>
        <input type="email" name="email" placeholder="Email"><br>
        <input type="password" name="password" placeholder="Password"><br>
        <input type="text" name="phone" placeholder="Phone"><br>
        Date of Birth:<br>
        <input type="date" name="date_of_birth"><br>
        <input type="text" name="blood_group" placeholder="Blood Group"><br>
        <select name="gender">
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select><br>
        <textarea name="address" placeholder="Address"></textarea><br>
        <input type="text" name="emergency_contact_name" placeholder="Emergency Contact Name"><br>
        <input type="text" name="emergency_contact_phone" placeholder="Emergency Contact Phone"><br>
        <input type="submit" value="Register">
    </form>
    <a href="login.view.php">Back to Login</a>
</div>
</body>
</html>
