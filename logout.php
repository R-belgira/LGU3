<?php
// logout.php

// Magsimula ng session (kailangan para ma-access ang session variables)
session_start();

// I-unset ang lahat ng session variables
$_SESSION = array();

// I-destroy ang session.
session_destroy();

// I-redirect ang user sa login page
header("location: ../index.php");
exit;
?>