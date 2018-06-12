<?php include_once 'signups.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="../css/bootstrap.css">
		<link href="https://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet">
		<link rel="stylesheet" href="../css/signup-style.css">
		<link rel="stylesheet" href="../css/accordion.css">
		<script src="../js/jquery-3.1.1.js"></script>
		<script src="../js/bootstrap.js"></script>
		<script src="../js/accordion.js"></script>
		
		<title>Sign Up | University of Nottingham Gliding club</title>
		<link rel="icon" href="../images/icon.png" type="image/png" sizes="16x16"> 
		<meta name="keywords" content="university, nottingham, gliding, aviation, flying, club, sports">
		<meta name="description" content="To provide you with information and basic knowledge about gliding">

	</head>
	
	<body>

<!----------------------------------------------------Navbar-------------------------------------------------------------->
		<?php include '../navbar.html'; ?>
<!---------------------------------------------------/Navbar-------------------------------------------------------------->
	
		<div class="signup_container" id="signup-form"> 
						
			
				<br />
				<?php
				$signupSys = new SignupSystem();
				$signupSys->loadSettingsFile();
				$signupSys->displayPage();
				?>
			</span>
		</div>
	
	</body>
</html>