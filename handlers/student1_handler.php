<?php
require_once('includes/user.php');
require_once('includes/lecture.php');
$error = "";
session_start();
if (isset($_SESSION['LAST_ACTIVITY'])==0 || (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago
    $_SESSION = array();    // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    header('Location: login.php');
}
else
{
		$_SESSION['LAST_ACTIVITY'] = time();
}
if ($_SESSION['loggedin'] == true && $_SESSION['role'] == 0)
{
	$user = new user();
	$userinfo = $user->getUserInformation($_SESSION['user']);
	$lecture = new lecture();
	$alllectures = $lecture->listAlllectures();
}
else
{
	header('Location: login.php');
}
if($loginerror !== "")
	$loginerror = "<p class='error'>{$loginerror}</p>";

?>