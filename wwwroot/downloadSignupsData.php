<?php 
/* Returns the current data file path */
function getDataFile($xml)
{
	$weekend = date("Y-m-d", strtotime('Saturday this week'));
	$file = $weekend . ".csv";
	if(!file_exists("../signups/" . $file))
	{
		// If it doesn't exist then create one with the correct headings
		$data = array();
		$i = 0;
		foreach($xml->children() as $field)
		{ 
			if($field->getName() == "field")
			{
				$fieldName = str_replace(" ", "-", $field['name']);
				$data[$i] = $fieldName;
				$i++;
			}
		}
		$csvfile = fopen("../signups/" . $file, "w");
		fputcsv($csvfile, $data);
		fclose($csvfile);
	}
	return $file;
}

	$username = (isset($_GET['user']) ? $_GET['user'] : null);
	$password = (isset($_GET['pass']) ? $_GET['pass'] : null);

	//username and password is required so the data does not get downloaded when the url www.nugc.net/downloadSignupsData.php is used
	if($username == "admin" && $password == "PortmoakNU2")
	{
		$xml = simplexml_load_file("../signup-form.xml") or die("Error loading settings file.");
		$file = getDataFile($xml);
		header('Content-Description: File Transfer');
		header("Content-Type: application/csv") ;
		header("Content-Disposition: attachment; filename=$file");
		header("Expires: 0");
		echo file_get_contents("../signups/" . $file); 
	}
?>