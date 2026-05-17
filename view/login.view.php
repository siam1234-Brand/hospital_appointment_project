<?php
session_start();
if (isset($_SESSION['role'])) {
    header('Location: ' . $_SESSION['role'] . '/dashboard.view.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hospital Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h2>Hospital Appointment Booking System</h2>
    <h3>Login</h3>
    <form method="post" action="../controller/user/loginHandler.php">
        <input type="email" name="email" placeholder="Email"><br>
        <input type="password" name="password" placeholder="Password"><br>
        <input type="submit" value="Login">
        <?php
        if (isset($_SESSION['error']['login'])) {
            echo "<p class='msg'>" . $_SESSION['error']['login'] . "</p>";
            unset($_SESSION['error']['login']);
        }
        ?>
    </form>
    <p><a href="register.view.php">Patient Registration</a></p>
    <div class="card">
        <b>Demo Login</b><br>
        Admin: admin@hospital.com / 123456<br>
        Patient: patient@hospital.com / 123456<br>
        Doctor: doctor@hospital.com / 123456<br>
        Receptionist: reception@hospital.com / 123456
    </div>
</div>
</body>
</html>
