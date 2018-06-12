<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="css/bootstrap.css">
		<link href="https://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet">
		<link rel="stylesheet" href="css/gallery.css">
		<link rel="stylesheet" href="css/accordion.css">
		<script src="js/jquery-3.1.1.js"></script>
		<script src="js/bootstrap.js"></script>
		<script src="js/accordion.js"></script>

		<title>Gallery | University of Nottingham Gliding club</title>
		<link rel="icon" href="images/icon.png" type="image/png" sizes="16x16"> 
		<meta name="keywords" content="university, nottingham, gliding, aviation, flying, club, sports, gallery, picture, photograph, photographs">
		<meta name="description" content="Photographs taken by members of the University of Nottingham Gliding club">
	</head>
	
	<body>

<!----------------------------------------------------Navbar-------------------------------------------------------------->
		<?php include 'navbar.html'; ?>
<!---------------------------------------------------/Navbar-------------------------------------------------------------->

    <header id="myCarousel" class="carousel slide">

        <!-- Wrapper for Slides -->
        <div class="carousel-inner">
			<?php 
			$dir = 'images/Gallery';
			
			//only display images with those file formats
		    $file_display = array('jpg', 'jpeg', 'png', 'gif');
				
			//get all file names from folder
			$dir_contents = scandir($dir);			
			
			$count = 0;

			//for loop has been added to allow every file in the images/Gallery to be displayed
			foreach ($dir_contents as $file) 
				{
				//get file type of current file
				$temp1 = explode('.', $file);
				$temp2 = end($temp1);
				$file_type = strtolower($temp2);
				
				//if the value of count is 0, the file name is valid and the file type is valid
				if($count == 0 && $file !== '.' && $file !== '..' && in_array($file_type, $file_display) == true)
					{
					//get filename
					$name = basename($file);
						
					//display image (this image is the first image that will be displayed)
					echo "<div class='item active'>";
					echo "<div class='fill' style='background-image:url(images/Gallery/{$name});'></div>";
					echo "</div>";
					
					$count++;	
					}
				//if the value of count is greater than 0, the file name is valid and the file type is valid
				else if ($count > 0 && $file !== '.' && $file !== '..' && in_array($file_type, $file_display) == true)
					{
					$name = basename($file);
					echo "<div class='item'>";
					echo "<div class='fill' style='background-image:url(images/Gallery/{$name});'></div>";
					echo "</div>";
					}					
				}
			?>
        </div>

        <!-- Controls -->
        <a class="left carousel-control" href="#myCarousel" data-slide="prev">
            <span class="icon-prev"></span>
        </a>
        <a class="right carousel-control" href="#myCarousel" data-slide="next">
            <span class="icon-next"></span>
        </a>

    </header>

</body>
</html>