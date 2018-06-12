<?php
/* This script will download the latest signup file */

session_start();
include_once 'signups.php';

// Check the user is logged in
if(isset($_SESSION['username']))
{
	// Get the latest signup sheet
	$signupSys = new SignupSystem();
	$signupSys->loadSettingsFile();
	$file = $signupSys->getSignupDataFile();
	
	// Display the file contents
	$name = basename($file);
	header('Content-Description: File Transfer');
	header("Content-Type: application/csv") ;
	header("Content-Disposition: attachment; filename=$name");
	header("Expires: 0");
	echo file_get_contents($file); 
}
else
{
	echo "<h2>Not logged in!</h2>";
}

?>