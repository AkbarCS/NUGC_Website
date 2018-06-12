<?php
	require_once 'Classes/PHPExcel.php';
	
	$username = (isset($_GET['user']) ? $_GET['user'] : null);
	$password = (isset($_GET['pass']) ? $_GET['pass'] : null);
	
	//username and password is required so the data does not get downloaded when the url www.nugc.net/downloadMasterData.php is used
	if($username == "admin" && $password == "PortmoakNU2")
	{
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
				
		//tell the reader to include charts when it loads a file
		$objReader->setIncludeCharts(TRUE);
		
		//load the file
		$masterDataFile = $objReader->load("../dataAnalysis/signupsDataAnalysis.xlsx");
						
		//write data to file
		$objWriter = PHPExcel_IOFactory::createWriter($masterDataFile, "Excel2007");
		$objWriter->setIncludeCharts(TRUE);
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml‌​.presentation');
		header('Content-Disposition: attachment;filename="signupsDataAnalysis.xlsx"');
		header('Cache-Control: max-age=0');
		
		$objWriter->save('php://output');	
	}
?>