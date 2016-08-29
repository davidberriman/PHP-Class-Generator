<?php

if(isSet($_POST['tableName'])){

	// set the div class as an error until it is set otherwise
	$messageClass = "errorBox";
	
	// check they have entered the table name  
	if(!isSet($_POST['tableName']) || trim($_POST['tableName']) == ""){
		$message = 'Please enter a table name for the generation process';
        return false;
	}
	
	// set the table name
	$argv[1] = $_POST['tableName'];
	
	// set the file name as requested / default to class.tableName.php
	$fileName = $_POST['fileName'];	
	if(isSet($fileName) && $fileName != ""){
		$argv[2] = $fileName;
	}else{
		$argv[2] = "class.".strtolower($_POST['tableName']);
	}
	
	// this file runs off arguments so it can be used in the command line as well
	// run the createClass.php file passing in the arguments 
	// $argv[1] = table name;
	// $argv[2] = (optional) file name
	require_once("include/createClass.php");
	
	// see if there were any php errors likely to be
	// caused when trying to write the file
	$error = error_get_last();
	if(!isSet($error['message']))
	{
		$messageClass = "successBox";
		$message = 'Your class for '.$table.' was created.<br/><br/>Filename: '.$argv[2].".php";
	}else
	{
		$message = 'ERROR - There was an error creating your class.<br/><br/>
			Please check the write permissions on the directory.<br/><br/>
		    ERROR MESSAGE:<br/><br/>'.$error['message'];
	}
}
?>