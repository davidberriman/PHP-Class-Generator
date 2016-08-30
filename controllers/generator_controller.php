<?php
if(file_exists("initialize.php")){require_once("initialize.php");};
if(file_exists("include/initialize.php")){require_once("include/initialize.php");};
require_once("include/createClassTemplate.php");

if(isSet($_POST['tableName'])){

	// set the div class as an error until it is set otherwise
	$messageClass = "errorBox";
	
	// check they have entered the table name  
	if(!isSet($_POST['tableName']) || trim($_POST['tableName']) == ""){
		$message = 'Please enter a table name for the generation process';
        return false;
	}
	
	$classNameForTemplate = $_POST['tableName'];
	
	// see if user has specified filename otherwise use default of class.classname.php
	if(isSet($_POST['fileName']) && trim($_POST['fileName']) != ""){
		$filenameForTemplate = $_POST['fileName'];
	}else{
		$filenameForTemplate = "";
	}
	
	$error = "";
	
	// this file runs off arguments so it can be used in the command line as well
	// run the createClass.php file passing in the arguments 
	// $classNameForTemplate = table name;
	// $filenameForTemplate = (optional) file name
	$error = createClass($classNameForTemplate, $filenameForTemplate);
	
	// see if there were any php errors likely to be
	// caused when trying to write the file
	if($error == "")
	{
		$messageClass = "successBox";
		$message = 'Your class for '.$_POST['tableName'].' was created.<br>';
	}else
	{
		$message = 'ERROR - There was an error creating your class.<br/><br/>
			Please check the write permissions on the directory.<br/><br/>
		    ERROR MESSAGE:<br/><br/>'.$error;
	}
}

if(isSet($_POST['updateAll'])){
	
	// set the div class as an error until it is set otherwise
	$messageClass = "errorBox";
	
	// check database connection is working
	if (!$database->get_connection()) {
	    trigger_error("ERROR  - Database Connect failed.<br/><br/>
		Ensure you have entered the database login information into the initialize.php file");
	    return false;
	}
	
	$arrayTableNames = array();

	// Get a list of table names in the database
	if ($result = mysqli_query($database->get_connection(), "SHOW TABLES ")) {
	
		// no tables found
		if(mysqli_num_rows($result) < 1){
			trigger_error( "The tables could not be found in the database");
			return false;
		}

		// create an array of table names to loop through
		$i = 0;
		while ($row = mysqli_fetch_array($result)){
			array_push($arrayTableNames, $row[0]);
		}

	    mysqli_free_result($result);
	
	}else{
		trigger_error("The tables could not be found in the database");
		return false;
	}
	
	$errorList = "";
	
	// create the class for each of the tables
	foreach ($arrayTableNames as &$table) {
		
		// create the class for this table
		$error = createClass($table, "");
				
		// check for error messages and concatinate errors
		if(isSet($error) && trim($error)  != ""){
			$errorList .= $error."<br/><br/>";
			unset($error);
		}
		
	}
	
	// see if there were any php errors likely to be
	// caused when trying to write the file
	if($errorList == "")
	{
		$messageClass = "successBox";
		$message = 'Your table classes were created.<br>';
	}else
	{
		$message = 'ERROR(S) - There were errors when creating your classes.<br/><br/>
			Please check the write permissions on the directory.<br/><br/>
		    ERROR MESSAGE:<br/><br/>'.$errorList;
	}
	
}

?>