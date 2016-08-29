<?php
if(file_exists("initialize.php")){require_once("initialize.php");};
if(file_exists("include/initialize.php")){require_once("include/initialize.php");};

# ========================================================================#
#
#  Author:    David Berriman
#  Version:	  1.1
#  Date:      28/08/2016
#  Purpose:   Create Class with CRUD metods for MySQL table
#
# ========================================================================#

	
	// this file can be called from the command line
	// first argument should be class name
	// second argument can be the file name for the class (optional)
	
	// table name passed in as argument 1
	$tableName = $argv[1];
	
	// capitalise the first letter to make the class name 
	$className = ucfirst(strtolower($tableName));
	
	// see if user has specified filename otherwise use default of class.classname.php
	if(isSet($argv[2]) && trim($argv[2]) != ""){
		$filename = $argv[2].".php";
	}else{
		$filename = strtolower("class.".$className).".php";
	}
	
	// check we have permissions to create the file
	if (file_exists($filename) && !is_writable($filename)) {
		trigger_error("The file exists and is not writable");
		return false;
	}
	
	// having $_POST['tableName'] unset
	// means this form is being called from the command line so prepend 
	// the filename with ../ so it all gets generated in the same place
	if(!isSet($_POST['tableName'])){
		$filename = "../".$filename;
	}
	
	// create the file
	if (!$myfile = fopen($filename, "w")) {
		trigger_error("The file could not be created");
		return false;
	}
	
	// -----------------------------------------------------
	// get the list of fields in the database table
	// -----------------------------------------------------
	
	// check database connection is working
	if (!$database->get_connection()) {
	    trigger_error("ERROR  - Database Connect failed.<br/><br/>
		Ensure you have entered the database login information into the initialize.php file");
	    return false;
	}
	
	
	// clean the field name
	$clean = new Sanitize();
	$table = $clean->clean($tableName);
	
	$arrayFieldNames = array();

	// Get a list of field names & types for the table
	if ($result = mysqli_query($database->get_connection(), "SHOW COLUMNS FROM {$table}")) {
		
		// no columns found - is tabel name correct?
		if(mysqli_num_rows($result) < 1){
			trigger_error( "The table columns could not be found for: {$table}");
			return false;
		}
	
		// create an array of arrays (fieldname, type)
		$i = 0;
		while ($row = mysqli_fetch_array($result)){		
				
			$fielditem = array("fieldname" => $row[0], "type" => $row[1]); //(fieldname, type)
			
			// add array to $arrayFieldNames array
			array_push($arrayFieldNames, $fielditem);
						
			// populate $primaryKey var if this is a primary key field 
			// with field name and $primaryKeyType with placeholder type 
			// (for prepared sql statements) 
			if($row[3] == "PRI" && !isSet($primaryKey)){
				$primaryKey = $row[0];
				$primaryKeyType = returnPlaceholderType($row[1]);
			}
		}
	
	    mysqli_free_result($result);
		
	}else{
		trigger_error("The table columns could not be found for: {$table}");
		return false;
	}
	
	// get the primary key column for the table key used in update / delete methods
	if(!isSet($primaryKey)){
		
		if(isSet($arrayFieldNames[0]['fieldname'])){
			$primaryKey = $arrayFieldNames[0]['fieldname'];
			$primaryKeyType = returnPlaceholderType($arrayFieldNames[0]['type']);
		}else{
			trigger_error("The table primary key column could not be found for: {$table}");
			return false;
		}
	}
	
	// return the place holder type eg s = string, d = double etc.
	function returnPlaceholderType($value){
				
		$pos = strpos(strtolower($value), "int");
		if ($pos !== false) {
			return "i";
		}
		
		$pos = strpos(strtolower($value), "double");
		if ($pos !== false) {
			return "d";
		}
		
		$pos = strpos(strtolower($value), "blob");
		if ($pos !== false) {
			return "b";
		}
				
		return "s";
	} 


	$listOfCommaSeparatedFieldNames = "";
	
	// used to create space between the methods
	$MethodSeparator = PHP_EOL.PHP_EOL.PHP_EOL;
	
	// create empty variables
	$listOfFieldNames = "";$listOfSETFieldNames = "";$placeholders = "";$s = "";$comma = "";$commaSpace = "";
	
	// create the list of variables and placeholders needed to construct the class
	if(isSet($arrayFieldNames) && !empty($arrayFieldNames)){
		// loop array of field names
		foreach ($arrayFieldNames as &$item) {
			$field = trim($item["fieldname"]); // field name
			$type = trim($item["type"]); // field type
			// creates a comma separated list of $variables
			$listOfCommaSeparatedFieldNames .=  $commaSpace."$".$field;
			// creates a comma separated list of field names
			$listOfFieldNames .=  $commaSpace.$field;
			// creates a comma separated list of field=? (for update query)
			$listOfSETFieldNames .= $commaSpace.$field."=?";
			// creates a comma separated list of placeholders ?
			$placeholders .= $comma."?"; // create placeholders for sql
			// create s for placeholders
			$s .= returnPlaceholderType($type);
			$comma = ",";
			$commaSpace = ", ";
		}
	}	
	
	// import database files
	$txt = '<?php '.PHP_EOL;
	
	// includes
	$txt .= 'if(file_exists("include/initialize.php")){require_once("include/initialize.php");};'.PHP_EOL.PHP_EOL;
	// add these lines if you don't want the requires to be in the initialize.php file
	//$txt .= 'if(!isSet($database)){require_once(LIB_PATH.DS."database.php");};'.PHP_EOL;
	//$txt .= 'if(!class_exists("ErrorLog")){require_once(LIB_PATH.DS."class.error_log.php");};'.PHP_EOL.PHP_EOL;
	
	// start of class
	$txt .= 'class '.$className.' extends MySQLDatabase '.PHP_EOL;
	$txt .= '{ '.PHP_EOL;
	
	
	$txt .= '	protected static $table_name="'.strtolower($className).'";'.PHP_EOL;
	$txt .= '	public $error; '.PHP_EOL;
	$txt .= PHP_EOL;
		
	// create the list of field names as class properties
	if(isSet($arrayFieldNames) && !empty($arrayFieldNames)){
		// loop array of field names
		foreach ($arrayFieldNames as &$item) {
			$fieldname = trim($item["fieldname"]);
			$txt .= "	public $".$fieldname.";".PHP_EOL;
		}
	}
	$txt .= $MethodSeparator;
	
			
	// create method
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .='	// Public function to create a new record'.PHP_EOL;
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .= "	public function create(){ ".PHP_EOL;
	$txt .= PHP_EOL;		
	if(isSet($arrayFieldNames) && !empty($arrayFieldNames)){
		// loop array of field names
		foreach ($arrayFieldNames as &$item) {
			$fieldname = trim($item["fieldname"]);
			$txt .=  "		$".$fieldname.' = $this->escape_value($this->'.$fieldname.");".PHP_EOL;
		}
	}
	$txt .= PHP_EOL;
	$txt .= '		try'.PHP_EOL;
	$txt .= '		{ '.PHP_EOL;
	$txt .= '			return $this->createRecord('.$listOfCommaSeparatedFieldNames.'); '.PHP_EOL;
	$txt .= '		} '.PHP_EOL;
	$txt .= '		catch (Exception $e){ '.PHP_EOL;
	$txt .= '			$error = new ErrorLog();'.PHP_EOL;
	$txt .= '			$error->createErrorLog($e);'.PHP_EOL;
	$txt .= '			return false;		'.PHP_EOL;
	$txt .= '		}'.PHP_EOL;
	$txt .= "	} ".PHP_EOL;
	$txt .= $MethodSeparator;
	// end create method
			
			
			
	// createRecord method
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .='	// Private function used with try / catch to create a new record'.PHP_EOL;
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .= '	private function createRecord('.$listOfCommaSeparatedFieldNames.'){'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		global $database;'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		$sql = "INSERT INTO ".self::$table_name." ('.$listOfFieldNames.') '.PHP_EOL;
	$txt .= '				VALUES ('.$placeholders.')";'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		if ($stmt = $database->get_connection()->prepare($sql)) {'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->bind_param("'.$s.'", '.$listOfCommaSeparatedFieldNames.');'.PHP_EOL;
	$txt .= '			if ( false===$stmt) {'.PHP_EOL;
	$txt .= '				throw new Exception("Exception thrown ('.$className.':createRecord) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
	$txt .= '			}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->execute();'.PHP_EOL;
	$txt .= '			if ($stmt->errno) {'.PHP_EOL;
	$txt .= '				throw new Exception("Exception thrown ('.$className.':createRecord) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
	$txt .= '			}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			return (mysqli_affected_rows($database->get_connection()) == 1) ? true : false;'.PHP_EOL;
	$txt .= '		}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		throw new Exception("ERROR - Querying email database table (class:'.$className.' ; method:createRecord) : ".$database->get_connection()->error,E_ALL);'.PHP_EOL;
	$txt .= '		return false;'.PHP_EOL;
	$txt .= '	}'.PHP_EOL;
	$txt .= $MethodSeparator;
	// end createRecord method 
	
	
	
	// update method
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .='	// Public function to update an existing record'.PHP_EOL;
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .= "	public function update(){ ".PHP_EOL;
	$txt .= PHP_EOL;		
	if(isSet($arrayFieldNames) && !empty($arrayFieldNames)){
		// loop array of field names
		foreach ($arrayFieldNames as &$item) {
			$fieldname = trim($item["fieldname"]);
			$txt .=  "		$".$fieldname.' = $this->escape_value($this->'.$fieldname.");".PHP_EOL;
		}
	}
	$txt .= PHP_EOL;
	$txt .= '		try'.PHP_EOL;
	$txt .= '		{ '.PHP_EOL;
	$txt .= '			return $this->updateRecord('.$listOfCommaSeparatedFieldNames.'); '.PHP_EOL;
	$txt .= '		} '.PHP_EOL;
	$txt .= '		catch (Exception $e){ '.PHP_EOL;
	$txt .= '			$error = new ErrorLog();'.PHP_EOL;
	$txt .= '			$error->createErrorLog($e);'.PHP_EOL;
	$txt .= '			return false;		'.PHP_EOL;
	$txt .= '		}'.PHP_EOL;
	$txt .= "	} ".PHP_EOL;
	$txt .= $MethodSeparator;
	// end update method
	
	
	// updateRecord method
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .='	// Private function used with try / catch to update an existing record'.PHP_EOL;
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .= '	private function updateRecord('.$listOfCommaSeparatedFieldNames.'){'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		global $database;'.PHP_EOL;
	$txt .= PHP_EOL;	
	$txt .= '		$sql = "UPDATE ".self::$table_name." SET '.$listOfSETFieldNames.' 
							WHERE '.$primaryKey.'=? LIMIT 1 ";'.PHP_EOL;
	
	$txt .= PHP_EOL;
	$txt .= '		if ($stmt = $database->get_connection()->prepare($sql)) {'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->bind_param("'.$s.$primaryKeyType.'", '.$listOfCommaSeparatedFieldNames.' , $'.$primaryKey.');'.PHP_EOL;
	$txt .= '			if ( false===$stmt) {'.PHP_EOL;
	$txt .= '				throw new Exception("Exception thrown ('.$className.':updateRecord) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
	$txt .= '			}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->execute();'.PHP_EOL;
	$txt .= '			if ($stmt->errno) {'.PHP_EOL;
	$txt .= '				throw new Exception("Exception thrown ('.$className.':updateRecord) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
	$txt .= '			}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			return (mysqli_affected_rows($database->get_connection()) == 1) ? true : false;'.PHP_EOL;
	$txt .= '		}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		throw new Exception("ERROR - Querying email database table (class:'.$className.' ; method:updateRecord) : ".$database->get_connection()->error,E_ALL);'.PHP_EOL;
	$txt .= '		return false;'.PHP_EOL;
	$txt .= '	}'.PHP_EOL;
	$txt .= $MethodSeparator;
	// end updateRecord method
				
				
	// delete method
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .='	// Public function to delete an existing record'.PHP_EOL;
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .= "	public function delete(){ ".PHP_EOL;
	$txt .= PHP_EOL;		
	$txt .= '		try'.PHP_EOL;
	$txt .= '		{ '.PHP_EOL;
	$txt .= '			return $this->deleteRecord(); '.PHP_EOL;
	$txt .= '		} '.PHP_EOL;
	$txt .= '		catch (Exception $e){ '.PHP_EOL;
	$txt .= '			$error = new ErrorLog();'.PHP_EOL;
	$txt .= '			$error->createErrorLog($e);'.PHP_EOL;
	$txt .= '			return false;		'.PHP_EOL;
	$txt .= '		}'.PHP_EOL;
	$txt .= "	} ".PHP_EOL;
	$txt .= $MethodSeparator;
	// end delete method	
	
	
	
	// deleteRecord method
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .='	// Private function used with try / catch to delete an existing record'.PHP_EOL;
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .= '	private function deleteRecord(){'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		global $database;'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		$keyField = $this->'.$primaryKey.';'.PHP_EOL;
	$txt .= PHP_EOL;	
	$txt .= '		if(!isSet($keyField))'.PHP_EOL;
	$txt .= ' 		{'.PHP_EOL;
	$txt .= ' 			return false;'.PHP_EOL;
	$txt .= ' 		}'.PHP_EOL;
	$txt .= PHP_EOL;	
	$txt .= '		$sql = "DELETE FROM  ".self::$table_name." WHERE '.$primaryKey.'=? LIMIT 1 ";'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		if ($stmt = $database->get_connection()->prepare($sql)) {'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->bind_param("'.$primaryKeyType.'", $keyField);'.PHP_EOL;
	$txt .= '			if ( false===$stmt) {'.PHP_EOL;
	$txt .= '				throw new Exception("Exception thrown ('.$className.':deleteRecord) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
	$txt .= '			}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->execute();'.PHP_EOL;
	$txt .= '			if ($stmt->errno) {'.PHP_EOL;
	$txt .= '				throw new Exception("Exception thrown ('.$className.':deleteRecord) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
	$txt .= '			}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			return (mysqli_affected_rows($database->get_connection()) == 1) ? true : false;'.PHP_EOL;
	$txt .= '		}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		throw new Exception("ERROR - Querying email database table  (class:'.$className.' ; method:deleteRecord) : ".$database->get_connection()->error,E_ALL);'.PHP_EOL;
	$txt .= '		return false;'.PHP_EOL;
	$txt .= '	}'.PHP_EOL;
	$txt .= $MethodSeparator;
	// end deleteRecord method 
	
	
	// get_user_for_field_with_value method
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .='	// Public function to retrieve a record using a field value eg email=test@test.com'.PHP_EOL;
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .= '	public function get_user_for_field_with_value($field, $value){ '.PHP_EOL;
	$txt .= PHP_EOL;		
	$txt .= '		try'.PHP_EOL;
	$txt .= '		{ '.PHP_EOL;
	$txt .= '			return $this->get_user_for_field_with_value_private($field, $value); '.PHP_EOL;
	$txt .= '		} '.PHP_EOL;
	$txt .= '		catch (Exception $e){ '.PHP_EOL;
	$txt .= '			$error = new ErrorLog();'.PHP_EOL;
	$txt .= '			$error->createErrorLog($e);'.PHP_EOL;
	$txt .= '			return false;		'.PHP_EOL;
	$txt .= '		}'.PHP_EOL;
	$txt .= "	} ".PHP_EOL;
	$txt .= $MethodSeparator;
	// end get_user_for_field_with_value method
	
	
	
	// get_user_for_field_with_value_private method
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .='	// Private function used with try / catch to retrieve an existing record'.PHP_EOL;
	$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
	$txt .= '	private function get_user_for_field_with_value_private($field, $value){'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		global $database;'.PHP_EOL;
	$txt .= PHP_EOL;	
	$txt .= '		$cleanField = $this->escape_value($field);'.PHP_EOL;
	$txt .= '		$cleanValue = $this->escape_value($value);'.PHP_EOL;
	$txt .= PHP_EOL;	
	$txt .= '		if(!isset($cleanValue) || !isset($cleanField))'.PHP_EOL;
	$txt .= ' 		{'.PHP_EOL;
	$txt .= ' 			return false;'.PHP_EOL;
	$txt .= ' 		}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		$sql = "SELECT '.$listOfFieldNames.' FROM ".self::$table_name." WHERE {$cleanField} = ? ";'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		if ($stmt = $database->get_connection()->prepare($sql)) {'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->bind_param("s", $cleanValue);'.PHP_EOL;
	$txt .= '			if ( false===$stmt) {'.PHP_EOL;
	$txt .= '				throw new Exception("Exception thrown ('.$className.':get_user_for_field_with_value_private) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
	$txt .= '			}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->execute();'.PHP_EOL;
	$txt .= '			if ($stmt->errno) {'.PHP_EOL;
	$txt .= '				throw new Exception("Exception thrown ('.$className.':get_user_for_field_with_value_private) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
	$txt .= '			}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->store_result();'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->bind_result('.$listOfCommaSeparatedFieldNames.');'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$num_of_rows = $stmt->num_rows;'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '			$stmt->fetch();'.PHP_EOL;
	$txt .= '			'.PHP_EOL;
	if(isSet($arrayFieldNames) && !empty($arrayFieldNames)){
		// loop array of field names
		foreach ($arrayFieldNames as &$item) {
			$fieldname = trim($item["fieldname"]);
			$txt .= '			$this->'.$fieldname.' = $'.$fieldname.';'.PHP_EOL;
		}
	}
	$txt .= PHP_EOL;
	$txt .= '			return ($num_of_rows == 1) ? true : false;'.PHP_EOL;
	$txt .= '		}'.PHP_EOL;
	$txt .= PHP_EOL;
	$txt .= '		throw new Exception("ERROR - Querying email database table  (class:'.$className.' ; method:get_user_for_field_with_value_private): ".$database->get_connection()->error,E_ALL);'.PHP_EOL;
	$txt .= '		return false;'.PHP_EOL;
	$txt .= '	}'.PHP_EOL;
	$txt .= $MethodSeparator;
	// end get_user_for_field_with_value_private method 
		
		
					
	// close the class	
	$txt .= "} ?> ";
	
	// create the class file
	if(!fwrite($myfile, $txt)){
		trigger_error("There was an error creating the file");
		return fasle;
	}

?>