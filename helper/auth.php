<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../../view/login.view.php');
        exit();
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        header('Location: ../../view/unauthorized.view.php');
        exit();
    }
}

function set_msg($msg) {
    $_SESSION['message'] = $msg;
}

function show_msg() {
    if (isset($_SESSION['message'])) {
        echo "<p class='msg'>" . $_SESSION['message'] . "</p>";
        unset($_SESSION['message']);
    }
}

function selected($a, $b) {
    if ($a == $b) {
        echo "selected";
    }
}

function checked($a, $b) {
    if ($a == $b) {
        echo "checked";
    }
}
?>
