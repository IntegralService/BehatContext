<?php

if (
        isset($_POST['_login-field'])
    &&  $_POST['_login-field'] === 'superadmin'
    &&  isset($_POST['_password-field'])
    &&  $_POST['_password-field'] === 'superpassword'
) {
    session_start();

    $_SESSION['loggedin'] = true;
}

ob_start();
header('Location: secured_area.php');
ob_end_flush();
die();
