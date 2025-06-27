<?php
session_start();
$_SESSION["no_js"] = "true";
$referrer = $_GET['referrer']; 
header("Location: ".$referrer);
?>