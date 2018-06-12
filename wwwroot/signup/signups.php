<?php
session_start();

class SignupSystem
{
	private $settingsFilePath = "../../signup-form.json";
	private $adminUsername = "admin";
	private $adminPassword = "PortmoakNU2";
	private $signupDataFolder = "../../signups/";
	private $settings;
		
	public function __construct()
	{
		
    }
	
	/* Loads the settings from the JSON file */
	public function loadSettingsFile()
	{
		$jsondata = file_get_contents($this->settingsFilePath) or die("Error loading the settings file.");
		$this->settings = json_decode($jsondata);
	}
	
	/* Saves the settings back to the JSON file */
	public function saveSettingsFile()
	{
		$jsondata = json_encode($this->settings);
		file_put_contents($this->settingsFilePath, $jsondata) or die("Error saving the settings file.");
	}
	
	/* Get the path for the current week's CSV data file */
	public function getSignupDataFile()
	{
		$weekend = date("Y-m-d", strtotime('Saturday this week'));
		$file = $this->signupDataFolder . $weekend . ".csv";
		if(!file_exists($file))
		{
			// If it doesn't exist then create one with the correct headings
			$data = array();
			$i = 0;
			foreach($this->settings->fields as $field)
			{ 
				$fieldName = str_replace(" ", "-", $field->name);
				$data[$i] = $fieldName;
				$i++;
			}
			$csvfile = fopen($file, "w");
			fputcsv($csvfile, $data);
			fclose($csvfile);
		}
		return $file;
	}
	
	/* Displays all the form fields */
	private function displayFormFields()
	{
		// Display the form fields
		foreach($this->settings->fields as $field)
		{ 
			$fieldName = str_replace(" ", "-", $field->name);
			$fieldType = $field->type;
			$fieldLabel = $field->label;
			$fieldDescription = $field->description;
			$fieldRequired = $field->required;
			// Display the label and description
			echo "<b>$fieldLabel</b>";
			if($fieldRequired == "true")
				echo "<font color='red'>*</font>";
			echo "<br>\n";
			if($fieldDescription != "")
				echo "<i>$fieldDescription</i><br>\n";
			// Display the form field
			if($fieldType == "text")
			{
				echo "<input type='text' name='signup_$fieldName' ";
				if($fieldRequired == "true")
					echo "required ";
				echo " value='" . $this->getSavedFieldFromCookie($fieldName) . "' /><br><br>\n";
			}
			else if($fieldType == "option")
			{
				$savedOption = $this->getSavedFieldFromCookie($fieldName);
				foreach(explode("\n", $field->options) as $option)
				{
					$option = str_replace("\r", "", $option);
					$option = str_replace("\n", "", $option);
					$selected = "";
					if($savedOption == $option)
						$selected = "checked='checked'";					
					echo "<input type='radio' name='signup_$fieldName' value='$option' $selected> $option<br>\n";
				}
				echo "<br>\n";
			}
		}
	}
	
	private function saveFieldInCookie($field, $value)
	{
		setcookie("NUGC_" . $field, $value, time() + 315360000);
		setcookie("NUGC_Remember", true, time() + 315360000);
	}
	
	private function getSavedFieldFromCookie($field)
	{
		$name = "NUGC_" . $field;
		if(isset($_COOKIE['NUGC_Remember']) && isset($_COOKIE[$name]))
		{
			if($_COOKIE['NUGC_Remember'] == true)
				return $_COOKIE[$name];
			else
				return "";
		}
		else
		{
			return "";
		}
	}
	
	public function isFormOpenNow()
	{
		if($this->settings->formActive != "true")
			return false;
		date_default_timezone_set('Europe/London');
		$startDay = strtotime($this->settings->signupDay . ' this week');
		$start = strtotime('+' . $this->settings->signupTime . ' hours', $startDay);
		$end = strtotime('+' . $this->settings->formActivePeriod . ' hours', $start);
		$now = time();	
		if($start < $now && $end > $now)
			return true;
		else
			return false;
	}
	
	public function getFormClosedMessage()
	{
		if($this->settings->formActive == "true")
		{
			date_default_timezone_set('Europe/London');
			$startDay = strtotime($this->settings->signupDay . ' this week');
			$start = strtotime('+' . $this->settings->signupTime . ' hours', $startDay);
			$end = strtotime('+' . $this->settings->formActivePeriod . ' hours', $start);
			$now = time();	
			if($end < $now)
			{
				$startDay = strtotime($this->settings->signupDay . ' next week');
				$start = strtotime('+' . $this->settings->signupTime . ' hours', $startDay);
			}	
			return "Signups are currently closed until " . date("l j F g:ia", $start);
		}
		return $this->settings->formClosedMessage;	
	}		
	
	public function displaySignupForm()
	{
		$sat = date("l j F", strtotime('Saturday this week'));
		$sun = date("l j F", strtotime('Sunday this week'));
		?>
		<div> 
			<span>
				<font size="4"><b>Gliding Signups For <?php echo $sat; ?> And <?php echo $sun; ?></b></font><br />
				<form name="signups" action="" method="POST">
				<p>Use this form to let us know which day(s) you would like to fly this weekend. Once the form has closed we will allocate spaces and let you know via email which day(s) you are flying. If you have any questions please email the flying organiser: flyingorganiser@nugc.net</p>
				<br />
				<input type="hidden" name="signup_form" value="true" />
				<?php $this->displayFormFields(); ?>
				<p>
				<input type="checkbox" name="remember_me" value="true" <?php if($this->getSavedFieldFromCookie("Remember") == true) echo "checked='checked'"; ?>> <b>Save my details for future</b>
				</p>
				<i><font color="red">*</font> indicates required fields.</i><br>
				<input type="submit" value="Submit" />
				</form>
			</span>
		</div>
		<?php
	}
	
	public function submitForm()
	{
		try
		{
			$saveDetails = $_POST['remember_me'];
		
			// Get each field from the POST request and store it in an array
			$data = array();
			$i = 0;
			foreach($this->settings->fields as $field)
			{ 
				$fieldName = str_replace(" ", "-", $field->name);
				$fieldValue = htmlentities($_POST['signup_' . $fieldName]);
				$data[$i] = $fieldValue;
				// Save in cookies if the user selected the option to
				if($saveDetails == "true")
					$this->saveFieldInCookie($fieldName, $fieldValue);
				else
				{
					$this->saveFieldInCookie($fieldName, ""); // They have chosen to delete any saved preferences
					$this->saveFieldInCookie("Remember", false);
				}
				$i++;
			}
			
			// Save form data to the csv file
			$file = fopen($this->getSignupDataFile(), "a");
			fputcsv($file, $data);
			fclose($file);
			
			// Display a success message
			echo "<div><form name=\"signups\" style=\"font-size: 16px;\"><b>Thank you for signing up! The flying list allocations will be emailed to you later this week.</b></form></div>";
		}
		catch (Exception $ex)
		{
			// Display an error message
			echo "<div><form name=\"signups\" style=\"font-size: 16px;\"><b>An error occured! Please tell the webmaster/flying organiser.</b></form></div>";
		}	
	}
	
	public function displayClosedMessage()
	{
		echo "<div ><form name=\"signups\" style=\"font-size: 16px;\"><b>" . $this->getFormClosedMessage() . "</b></form></div>";
	}
	
	public function displayLandingPage()
	{
		echo $this->settings->landingPageNotice;
		echo "<div><span><button onclick=\"window.location='?page=form';\">I have flown before, take me to the signup form</button></span></div>";
	}
	
	public function displayAdminLoginPage()
	{
		echo "<div><form name=\"signups\" action=\"\" method=\"POST\"><b>Username: </b><input type='text' name='username' /><br /><b>Password: </b><input type='password' name='password' style='width: 100%;border: 1px solid #ccc;background: #FFF;margin: 0 0 5px;padding: 10px;' /><br /><input type='submit' value='Login' /></form></div>";
	}
		
	public function displayAdminPage()
	{
		if(isset($_POST['admin_action']) && $_POST['admin_action'] == "save")
		{
			/* Save the settings */
			// Get the general settings
			$data->formActive = $_POST['form-active'];
			$data->formClosedMessage = $_POST['form-closed-message'];
			$data->signupDay = $_POST['signup-day'];
			$data->signupTime = $_POST['signup-time'];
			$data->formActivePeriod = $_POST['form-active-period'];
			$data->landingPageNotice = $_POST['landing-page-notice'];
			
			// Get the form field settings
			for($i = 0; $i < 12; $i++)
			{
				$fieldName = "field-" . ($i + 1);
				if(strlen($_POST[$fieldName . '-name']) > 0)
				{
					$data->fields[$i]->name = $_POST[$fieldName . '-name'];
					$data->fields[$i]->label = $_POST[$fieldName . '-label'];
					$data->fields[$i]->description = $_POST[$fieldName . '-description'];
					$data->fields[$i]->required = $_POST[$fieldName . '-required'];
					$data->fields[$i]->type = $_POST[$fieldName . '-type'];
					$data->fields[$i]->options = $_POST[$fieldName . '-options'];
				}
			}
			
			// Save to file
			$this->settings = $data;
			$this->saveSettingsFile();
			echo "<h2>Settings saved!</h2>";
		}
		else if(isset($_POST['admin_action']) && $_POST['admin_action'] == "logout")
		{
			session_destroy();
			echo "<h2>Logout successful!</h2>";
		}
		else
		{
			/* Display the admin panel: */
			
			// Build an array containing all the XML field data
			$fields = array();
			$i = 0;
			foreach($this->settings->fields as $field)
			{ 
				$fields[$i] = $field;		
				$i += 1;		
			}

			?>
			<font size="4"><b>Signup Settings</b></font><br /><br />
			<div>
			<form action="" method="POST" name="admin_form" style="font-size: 16px;">
			<input type="hidden" name="admin_action" value="save" />
			<b>Form Enabled:</b> <input type="checkbox" name="form-active" value="true" <?php if($this->settings->formActive) echo "checked='checked'"; ?> /><br /><br />
			<b>Form closed message:</b> (Displayed when the form is not enabled) <br /><textarea name="form-closed-message" style="width:100%"><?php echo $this->settings->formClosedMessage; ?></textarea><br /><br />
			<b>Signup Day</b>: 
			<select name="signup-day">
				<option value="Monday" <?php if($this->settings->signupDay == "Monday") echo "selected";?>>Monday</option>
				<option value="Tuesday" <?php if($this->settings->signupDay == "Tuesday") echo "selected";?>>Tuesday</option>
				<option value="Wednesday" <?php if($this->settings->signupDay == "Wednesday") echo "selected";?>>Wednesday</option>
				<option value="Thursday" <?php if($this->settings->signupDay == "Thursday") echo "selected";?>>Thursday</option>
				<option value="Friday" <?php if($this->settings->signupDay == "Friday") echo "selected";?>>Friday</option>
				<option value="Saturday" <?php if($this->settings->signupDay == "Saturday") echo "selected";?>>Saturday</option>
				<option value="Sunday" <?php if($this->settings->signupDay == "Sunday") echo "selected";?>>Sunday</option>
			</select><br /><br />
			<b>Form Opening Time</b>: 
			<select name="signup-time" >
			  <option value="0" <?php if($this->settings->signupTime == 0) echo "selected";?>>12:00am</option>
			  <option value="1" <?php if($this->settings->signupTime == 1) echo "selected";?>>01:00am</option>
			  <option value="2" <?php if($this->settings->signupTime == 2) echo "selected";?>>02:00am</option>
			  <option value="3" <?php if($this->settings->signupTime == 3) echo "selected";?>>03:00am</option>
			  <option value="4" <?php if($this->settings->signupTime == 4) echo "selected";?>>04:00am</option>
			  <option value="5" <?php if($this->settings->signupTime == 5) echo "selected";?>>05:00am</option>
			  <option value="6" <?php if($this->settings->signupTime == 6) echo "selected";?>>06:00am</option>
			  <option value="7" <?php if($this->settings->signupTime == 7) echo "selected";?>>07:00am</option>
			  <option value="8" <?php if($this->settings->signupTime == 8) echo "selected";?>>08:00am</option>
			  <option value="9" <?php if($this->settings->signupTime == 9) echo "selected";?>>09:00am</option>
			  <option value="10" <?php if($this->settings->signupTime == 10) echo "selected";?>>10:00am</option>
			  <option value="11" <?php if($this->settings->signupTime == 11) echo "selected";?>>11:00am</option>
			  <option value="12" <?php if($this->settings->signupTime == 12) echo "selected";?>>12:00pm</option>
			  <option value="13" <?php if($this->settings->signupTime == 13) echo "selected";?>>13:00pm</option>
			  <option value="14" <?php if($this->settings->signupTime == 14) echo "selected";?>>14:00pm</option>
			  <option value="15" <?php if($this->settings->signupTime == 15) echo "selected";?>>15:00pm</option>
			  <option value="16" <?php if($this->settings->signupTime == 16) echo "selected";?>>16:00pm</option>
			  <option value="17" <?php if($this->settings->signupTime == 17) echo "selected";?>>17:00pm</option>
			  <option value="18" <?php if($this->settings->signupTime == 18) echo "selected";?>>18:00pm</option>
			  <option value="19" <?php if($this->settings->signupTime == 19) echo "selected";?>>19:00pm</option>
			  <option value="20" <?php if($this->settings->signupTime == 20) echo "selected";?>>20:00pm</option>
			  <option value="21" <?php if($this->settings->signupTime == 21) echo "selected";?>>21:00pm</option>
			  <option value="22" <?php if($this->settings->signupTime == 22) echo "selected";?>>22:00pm</option>
			  <option value="23" <?php if($this->settings->signupTime == 23) echo "selected";?>>23:00pm</option>
			</select><br /><br />
			<b>Form active period:</b> (Number of hours the form is open for) <input type="number" name="form-active-period" value="<?php echo $this->settings->formActivePeriod; ?>" min="1" /><br /><br />
			<b>Landing page notice:</b> (Displayed on the first page) <br /><textarea name="landing-page-notice" style="width:100%" rows="5"><?php echo $this->settings->landingPageNotice; ?></textarea><br /><br />
			
			<table style="text-align:center;border-spacing: 10px;border-collapse: separate;">

			<tr><td><b>#</b></td><td><b>Field Name</b></td><td><b>Field Label</b></td><td><b>Field Description</b></td><td><b>Required?</b></td><td><b>Field Type</b></td><td><b>Options</b> <i>(one per line)</i></td></tr>

			<tr><td><b>1.</b></td><td><input type="text" name="field-1-name" style="width:100%" value="<?php echo $fields[0]->name; ?>" /></td><td><input type="text" name="field-1-label" style="width:100%" value="<?php echo $fields[0]->label; ?>" /></td><td><input type="text" name="field-1-description" style="width:100%" value="<?php echo $fields[0]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-1-required" value="true" <?php if($fields[0]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-1-type" style="width:100%"><option value="text" <?php if($fields[0]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[0]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-1-options" style="width:150px;height:50px;"><?php echo $fields[0]->options; ?></textarea></td></tr>
			
			<tr><td><b>2.</b></td><td><input type="text" name="field-2-name" style="width:100%" value="<?php echo $fields[1]->name; ?>" /></td><td><input type="text" name="field-2-label" style="width:100%" value="<?php echo $fields[1]->label; ?>" /></td><td><input type="text" name="field-2-description" style="width:100%" value="<?php echo $fields[1]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-2-required" value="true" <?php if($fields[1]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-2-type" style="width:100%"><option value="text" <?php if($fields[1]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[1]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-2-options" style="width:150px;height:50px;"><?php echo $fields[1]->options; ?></textarea></td></tr>
			
			<tr><td><b>3.</b></td><td><input type="text" name="field-3-name" style="width:100%" value="<?php echo $fields[2]->name; ?>" /></td><td><input type="text" name="field-3-label" style="width:100%" value="<?php echo $fields[2]->label; ?>" /></td><td><input type="text" name="field-3-description" style="width:100%" value="<?php echo $fields[2]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-3-required" value="true" <?php if($fields[2]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-3-type" style="width:100%"><option <?php if($fields[2]->type == "text") echo "selected"; ?> value="text">Text</option><option value="option" <?php if($fields[2]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-3-options" style="width:150px;height:50px;"><?php echo $fields[2]->options; ?></textarea></td></tr>
			
			<tr><td><b>4.</b></td><td><input type="text" name="field-4-name" style="width:100%" value="<?php echo $fields[3]->name; ?>" /></td><td><input type="text" name="field-4-label" style="width:100%" value="<?php echo $fields[3]->label; ?>" /></td><td><input type="text" name="field-4-description" style="width:100%" value="<?php echo $fields[3]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-4-required" value="true" <?php if($fields[3]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-4-type" style="width:100%"><option value="text" <?php if($fields[3]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[3]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-4-options" style="width:150px;height:50px;"><?php echo $fields[3]->options; ?></textarea></td></tr>
			
			<tr><td><b>5.</b></td><td><input type="text" name="field-5-name" style="width:100%" value="<?php echo $fields[4]->name; ?>" /></td><td><input type="text" name="field-5-label" style="width:100%" value="<?php echo $fields[4]->label; ?>" /></td><td><input type="text" name="field-5-description" style="width:100%" value="<?php echo $fields[4]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-5-required" value="true" <?php if($fields[4]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-5-type" style="width:100%"><option value="text" <?php if($fields[4]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[4]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-5-options" style="width:150px;height:50px;"><?php echo $fields[4]->options; ?></textarea></td></tr>
			
			<tr><td><b>6.</b></td><td><input type="text" name="field-6-name" style="width:100%" value="<?php echo $fields[5]->name; ?>" /></td><td><input type="text" name="field-6-label" style="width:100%" value="<?php echo $fields[5]->label; ?>" /></td><td><input type="text" name="field-6-description" style="width:100%" value="<?php echo $fields[5]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-6-required" value="true" <?php if($fields[5]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-6-type" style="width:100%"><option value="text" <?php if($fields[5]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[5]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-6-options" style="width:150px;height:50px;"><?php echo $fields[5]->options; ?></textarea></td></tr>
			
			<tr><td><b>7.</b></td><td><input type="text" name="field-7-name" style="width:100%" value="<?php echo $fields[6]->name; ?>" /></td><td><input type="text" name="field-7-label" style="width:100%" value="<?php echo $fields[6]->label; ?>" /></td><td><input type="text" name="field-7-description" style="width:100%" value="<?php echo $fields[6]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-7-required" value="true" <?php if($fields[6]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-7-type" style="width:100%"><option value="text" <?php if($fields[6]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[6]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-7-options" style="width:150px;height:50px;"><?php echo $fields[6]->options; ?></textarea></td></tr>
			
			<tr><td><b>8.</b></td><td><input type="text" name="field-8-name" style="width:100%" value="<?php echo $fields[7]->name; ?>" /></td><td><input type="text" name="field-8-label" style="width:100%" value="<?php echo $fields[7]->label; ?>" /></td><td><input type="text" name="field-8-description" style="width:100%" value="<?php echo $fields[7]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-8-required" value="true" <?php if($fields[7]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-8-type" style="width:100%"><option value="text" <?php if($fields[7]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[7]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-8-options" style="width:150px;height:50px;"><?php echo $fields[7]->options; ?></textarea></td></tr>
			
			<tr><td><b>9.</b></td><td><input type="text" name="field-9-name" style="width:100%" value="<?php echo $fields[8]->name; ?>" /></td><td><input type="text" name="field-9-label" style="width:100%" value="<?php echo $fields[8]->label; ?>" /></td><td><input type="text" name="field-9-description" style="width:100%" value="<?php echo $fields[8]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-9-required" value="true" <?php if($fields[8]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-9-type" style="width:100%"><option value="text" <?php if($fields[8]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[8]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-9-options" style="width:150px;height:50px;"><?php echo $fields[8]->options; ?></textarea></td></tr>
			
			<tr><td><b>10.</b></td><td><input type="text" name="field-10-name" style="width:100%" value="<?php echo $fields[9]->name; ?>" /></td><td><input type="text" name="field-10-label" style="width:100%" value="<?php echo $fields[9]->label; ?>" /></td><td><input type="text" name="field-10-description" style="width:100%" value="<?php echo $fields[9]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-10-required" value="true" <?php if($fields[9]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-10-type" style="width:100%"><option value="text" <?php if($fields[9]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[9]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-10-options" style="width:150px;height:50px;"><?php echo $fields[9]->options; ?></textarea></td></tr>
			
			<tr><td><b>11.</b></td><td><input type="text" name="field-11-name" style="width:100%" value="<?php echo $fields[10]->name; ?>" /></td><td><input type="text" name="field-11-label" style="width:100%" value="<?php echo $fields[10]->label; ?>" /></td><td><input type="text" name="field-11-description" style="width:100%" value="<?php echo $fields[10]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-11-required" value="true" <?php if($fields[10]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-11-type" style="width:100%"><option value="text" <?php if($fields[10]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[10]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-11-options" style="width:150px;height:50px;"><?php echo $fields[10]->options; ?></textarea></td></tr>
			
			<tr><td><b>12.</b></td><td><input type="text" name="field-12-name" style="width:100%" value="<?php echo $fields[11]->name; ?>" /></td><td><input type="text" name="field-12-label" style="width:100%" value="<?php echo $fields[11]->label; ?>" /></td><td><input type="text" name="field-12-description" style="width:100%" value="<?php echo $fields[11]->description; ?>" /></td><td style="text-align:center;"><input type="checkbox" name="field-12-required" value="true" <?php if($fields[11]->required == "true") echo "checked='checked'"; ?> /></td><td><select name="field-12-type" style="width:100%"><option value="text" <?php if($fields[11]->type == "text") echo "selected"; ?>>Text</option><option value="option" <?php if($fields[11]->type == "option") echo "selected"; ?>>Option</option></select></td><td><textarea name="field-12-options" style="width:150px;height:50px;"><?php echo $fields[11]->options; ?></textarea></td></tr>	

			</table>
			
			<input type="submit" value="Save Changes" /><br /><br />
			</form>
			
			<form action="download.php" method="GET">
				<input type="submit" value="Download Signup Sheet" /><br /><br />
			</form>
			
			<form action="" method="POST">
				<input type="hidden" name="admin_action" value="logout" />
				<input type="submit" value="Logout" /><br /><br />
			</form>
			
			</div>
			<?php
		}
	}
	
	public function displayPage()
	{
		if($_GET['page'] == "form")
		{
			if($this->isFormOpenNow())
			{
				if(isset($_POST['signup_form']))
				{
					$this->submitForm();
				}
				else
				{
					$this->displaySignupForm();
				}
			}
			else
			{
				$this->displayClosedMessage();
			}
		}
		else if($_GET['page'] == "admin")
		{
			if(!isset($_SESSION['username']))
			{
				// No session yet, check if password/username correct
				if(isset($_POST['password']))
				{
					$pass = $_POST['password'];
					$user = $_POST['username'];
					if($pass == $this->adminPassword && $user == $this->adminUsername)
					{
						// Login successful, create session
						$_SESSION['username'] = $this->adminUsername;
						$this->displayAdminPage();
					}
					else
					{
						echo "<h2>Incorrect password</h2>";
					}
				}
				else
				{
					$this->displayAdminLoginPage();
				}
			}
			else
			{
				// Already logged in
				$this->displayAdminPage();
			}

		}
		else // show the landing page
		{
			$this->displayLandingPage(); 
		}
	}
}
?>