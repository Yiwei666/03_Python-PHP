<?php
session_start();
session_destroy(); // destroy all session data
header('Location: login.php'); // redirect to the login page
exit;
?>