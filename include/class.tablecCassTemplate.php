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
	
class TablecCassTemplate{
	
	
	public $tablename; 
	public $error; 
	
	private $className;
	private $filename;
	private $arrayFieldNames = array();
	private $primaryKey;
	private $primaryKeyType;
	
	private $classFile;
	private $txt;
	private $listOfCommaSeparatedFieldNames;
	private $MethodSeparator;
	
	private $listOfFieldNames;
	private $listOfSETFieldNames;
	private $placeholders;
	private $s;
	
	
	function __construct(){

		// used to create space between the methods
		$this->MethodSeparator = PHP_EOL.PHP_EOL.PHP_EOL;
	}
	
	
	// ------------------------------------------------------
	// clear all the variables incase this is called in a loop
	// ------------------------------------------------------
	private function clearVars(){
		
		$this->error = "";
		$this->tablename = "";
		$this->className = "";
		$this->filename = "";
		$this->arrayFieldNames = array();
		$this->primaryKey = "";
		$this->primaryKeyType = "";
		
		$this->classFile = "";
		$this->txt = "";
		$this->listOfCommaSeparatedFieldNames = "";
		
		$this->listOfFieldNames = "";
		$this->listOfSETFieldNames = "";
		$this->placeholders = "";
		$this->s = "";

	}
	

	
	// ------------------------------------------------------
	// public function to create the class template
	// ------------------------------------------------------
	public function createClass($tablename, $filename){

		if(!isSet($tablename) || trim($tablename) == ""){
			$this->$error = "ERROR - the table name is empty";
			return false;
		}
		
		// clear properties inclase this is being called in a loop
		$this->clearVars();
		
		$this->tablename = $tablename;
		
		// set class name
		$this->className = ucfirst(strtolower($this->tablename));
		
		// set the file name for the template file
		if(!$this->setFilename($filename)){
			return false;
		}
		
		if(!$this->createFile()){
			return false;
		}
		
		return true;
	}
	
	
	
	// ------------------------------------------------------
	// driving fiunction to check errors and create the file
	// ------------------------------------------------------
	private function createFile(){
		
		if(!$this->isFileWritable()){
			return false;
		}
		
		if(!$this->isDatabaseConnectionValid()){
			return false;
		}
		
		if(!$this->setFieldNames()){
			return false;
		}
		
		// check we have a primary key field defined
		if(!$this->setPrimaryKey()){
			return false;
		}
		
		if(!$this->setFileDirectory()){
			return false;
		}
		
		if(!$this->createTheFile()){
			return false;
		}
		
		if(!$this->createVariableLists()){
			return false;
		}
		
		if(!isSet($this->arrayFieldNames) || empty($this->arrayFieldNames)){
			return false;
		}
		
		if(!$this->createText()){
			return false;
		}

		if(!$this->writeToTheFile()){
			return false;
		}
		
		return true;
	}
	
	
	
	// --------------------------------------------------------
	// create all of the text for the file
	// --------------------------------------------------------
	private function createText(){
		
		$this->txt = $this->startPHPFile();
		
		$this->txt .= $this->returnIncludes();
		
		$this->txt .= $this->startClass();
		
		$this->txt .= $this->classProperties();
		
		$this->txt .= $this->MethodSeparator;
		
		$this->txt .= $this->createParentFunction("create", "createRecord", "INSERT", "", true);
		
		$this->txt .= $this->MethodSeparator;
		
		$this->txt .= $this->createChildFunction("createRecord", "INSERT");
		
		$this->txt .= $this->MethodSeparator;
		
		$this->txt .= $this->createParentFunction("update", "updateRecord", "UPDATE", "", true);
		
		$this->txt .= $this->MethodSeparator;
		
		$this->txt .= $this->createChildFunction("updateRecord", "UPDATE");
		
		$this->txt .= $this->MethodSeparator;
		
		$this->txt .= $this->createParentFunction("delete", "deleteRecord", "DELETE", "", false);
		//$this->txt .= $this->createParentFunctionNoProps("delete", "deleteRecord", "DELETE", "");
		
		$this->txt .= $this->MethodSeparator;
		
		$this->txt .= $this->createChildFunction("deleteRecord", "DELETE");
		
		$this->txt .= $this->MethodSeparator;

		$this->txt .= $this->createParentFunction("get_user_for_field_with_value", "get_user_for_field_with_value_private", "SELECT", '$field, $value', false);
		//$this->txt .= $this->createParentFunctionNoProps("get_user_for_field_with_value", "get_user_for_field_with_value_private", "SELECT", '$field, $value');
		
		$this->txt .= $this->MethodSeparator;
		
		$this->txt .= $this->returnSelectMethod();
		
		$this->txt .= $this->MethodSeparator;
		
		$this->txt .= $this->endPHPFile();
		
		return true;
		
	}



	// ------------------------------------------------------
	// return closing php tag for end of file 
	// ------------------------------------------------------
	private function endPHPFile(){
		return '} ?> '.PHP_EOL;
	}
	
	
	// ------------------------------------------------------
	// return opening php tag for begining of file 
	// ------------------------------------------------------
	private function startPHPFile(){
		return '<?php '.PHP_EOL;
	}
	
		
	// ------------------------------------------------------
	// return all the includes 
	// ------------------------------------------------------
	private function returnIncludes(){
		$txt = 'if(file_exists("../include/initialize.php")){require_once("../include/initialize.php");};'.PHP_EOL.PHP_EOL;
		// add these lines if you don't want the requires to be in the initialize.php file
		//$txt .= 'if(!isSet($database)){require_once(LIB_PATH.DS."database.php");};'.PHP_EOL;
		//$txt .= 'if(!class_exists("ErrorLog")){require_once(LIB_PATH.DS."class.error_log.php");};'.PHP_EOL.PHP_EOL;
		return $txt;
	}
	
	
	// ------------------------------------------------------
	// return start of class
	// ------------------------------------------------------
	private function startClass(){
		$txt = 'class '.$this->className.' extends MySQLDatabase '.PHP_EOL;
		$txt .= '{ '.PHP_EOL;
		$txt .= '	protected static $table_name="'.strtolower($this->className).'";'.PHP_EOL;
		$txt .= '	public $error; '.PHP_EOL;
		$txt .= PHP_EOL;
		return $txt;
	}
	
	
	// ------------------------------------------------------
	// return all properties of class - each is a table column 
	// ------------------------------------------------------
	private function classProperties(){

		$txt = "";
		
		// create the list of field names as class properties
		foreach ($this->arrayFieldNames as &$item) {
			$fieldname = trim($item["fieldname"]);
			$txt .= "	public $".$fieldname.";".PHP_EOL;
		}
		
		return $txt;
	}
	
	
	
	// ------------------------------------------------------
	// return all escaped properties of class - each is a table column 
	// ------------------------------------------------------
	private function classEscapedProperties(){

		$txt = "";
		
		// create the list of field names as class properties
		foreach ($this->arrayFieldNames as &$item) {
			$fieldname = trim($item["fieldname"]);
			$txt .=  "		$".$fieldname.' = $this->escape_value($this->'.$fieldname.");".PHP_EOL;
		}
		
		return $txt;
	}
	
	
	// ------------------------------------------------------
	// return parent function which uses try / catch
	// this one does not have the class properties added 
	// ------------------------------------------------------
	private function createParentFunctionNoProps($functionName, $childFunction, $type, $functionVars){
		
		// create method
		$txt ='	// --------------------------------------------------------------------------'.PHP_EOL;
		$txt .='	// Public function to '.strtolower($type).' a record'.PHP_EOL;
		$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
		$txt .= "	public function ".$functionName."(".$functionVars."){ ".PHP_EOL;
		$txt .= PHP_EOL;		
		$txt .= '		try'.PHP_EOL;
		$txt .= '		{ '.PHP_EOL;
		$txt .= '			return $this->'.$childFunction.'('.$functionVars.'); '.PHP_EOL;
		$txt .= '		} '.PHP_EOL;
		$txt .= '		catch (Exception $e){ '.PHP_EOL;
		$txt .= '			$error = new ErrorLog();'.PHP_EOL;
		$txt .= '			$error->createErrorLog($e);'.PHP_EOL;
		$txt .= '			return false;		'.PHP_EOL;
		$txt .= '		}'.PHP_EOL;
		$txt .= "	} ".PHP_EOL;
		return $txt;
		
	}
	
	
	
	// ------------------------------------------------------
	// return parent function which uses try / catch
	// this one does have the class properties added 
	// ------------------------------------------------------
	private function createParentFunction($functionName, $childFunction, $type, $functionVars, $useProperties){
		
		// create method
		$txt ='	// --------------------------------------------------------------------------'.PHP_EOL;
		$txt .='	// Public function to '.strtolower($type).' a record'.PHP_EOL;
		$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
		$txt .= "	public function ".$functionName."(".$functionVars."){ ".PHP_EOL;
		$txt .= PHP_EOL;
		if($useProperties){
			$txt .= $this->classEscapedProperties();
			$txt .= PHP_EOL;
		}
		$txt .= '		try'.PHP_EOL;
		$txt .= '		{ '.PHP_EOL;
		if($useProperties){
			$txt .= '			return $this->'.$childFunction.'('.$this->listOfCommaSeparatedFieldNames.'); '.PHP_EOL;
		}else{
			$txt .= '			return $this->'.$childFunction.'('.$functionVars.'); '.PHP_EOL;
		}
		$txt .= '		} '.PHP_EOL;
		$txt .= '		catch (Exception $e){ '.PHP_EOL;
		$txt .= '			$error = new ErrorLog();'.PHP_EOL;
		$txt .= '			$error->createErrorLog($e);'.PHP_EOL;
		$txt .= '			return false;		'.PHP_EOL;
		$txt .= '		}'.PHP_EOL;
		$txt .= "	} ".PHP_EOL;
		
		return $txt;
		
	}
	
	
	
	// ------------------------------------------------------
	// return child function which interacts with database
	// using prepared statements
	// ------------------------------------------------------
	private function createChildFunction($functionName, $type){
		
		// createRecord method
		$txt ='	// --------------------------------------------------------------------------'.PHP_EOL;
		$txt .='	// Private function used with try / catch to '.strtolower($type).' a record'.PHP_EOL;
		$txt .='	// --------------------------------------------------------------------------'.PHP_EOL;
		if($type == "DELETE"){
			$txt .= '	private function '.$functionName.'(){'.PHP_EOL; // no params for delete
		}else{
			$txt .= '	private function '.$functionName.'('.$this->listOfCommaSeparatedFieldNames.'){'.PHP_EOL;
		}
		$txt .= PHP_EOL;
		$txt .= '		global $database;'.PHP_EOL;
		$txt .= PHP_EOL;
		
		if($type == "DELETE"){
			$txt .= '		$keyField = $this->'.$this->primaryKey.';'.PHP_EOL;
			$txt .= PHP_EOL;	
			$txt .= '		if(!isSet($keyField))'.PHP_EOL;
			$txt .= ' 		{'.PHP_EOL;
			$txt .= ' 			return false;'.PHP_EOL;
			$txt .= ' 		}'.PHP_EOL;
			$txt .= PHP_EOL;
			
		}
		$txt .= $this->returnSQL($type);
		$txt .= PHP_EOL;
		$txt .= '		if ($stmt = $database->get_connection()->prepare($sql)) {'.PHP_EOL;
		$txt .= PHP_EOL; 
		$txt .= $this->returnBind($type);
		$txt .= '			if ( false===$stmt) {'.PHP_EOL;
		$txt .= '				throw new Exception("Exception thrown ('.$this->className.':'.$functionName.') Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
		$txt .= '			}'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '			$stmt->execute();'.PHP_EOL;
		$txt .= '			if ($stmt->errno) {'.PHP_EOL;
		$txt .= '				throw new Exception("Exception thrown ('.$this->className.':'.$functionName.') Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
		$txt .= '			}'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '			return (mysqli_affected_rows($database->get_connection()) == 1) ? true : false;'.PHP_EOL;
		$txt .= '		}'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '		throw new Exception("ERROR - Querying email database table (class:'.$this->className.' ; method:'.$functionName.') : ".$database->get_connection()->error,E_ALL);'.PHP_EOL;
		$txt .= '		return false;'.PHP_EOL;
		$txt .= '	}'.PHP_EOL;
		
		return $txt;
	}
	
	
	
	// ------------------------------------------------------
	// return bind_param call
	// ------------------------------------------------------
	private function returnBind($type){
		
		$txt = "";
		
		switch ($type) {
		    case "INSERT":
				$txt .= '			$stmt->bind_param("'.$this->s.'", '.$this->listOfCommaSeparatedFieldNames.');'.PHP_EOL;
		        break;
		    case "UPDATE":
				$txt .= '			$stmt->bind_param("'.$this->s.$this->primaryKeyType.'", '.$this->listOfCommaSeparatedFieldNames.' , $'.$this->primaryKey.');'.PHP_EOL;
		        break;
		    case "DELETE":
		        $txt .= '			$stmt->bind_param("'.$this->primaryKeyType.'", $keyField);'.PHP_EOL;
		        break;
		    default:
		        echo "";
		}
		return $txt;		
	}
	
	
	
	// ------------------------------------------------------
	// return select function which is written separately
	// because it differs so much
	// ------------------------------------------------------
	private function returnSelectMethod(){
		
		// get_user_for_field_with_value_private method
		$txt ='	// --------------------------------------------------------------------------'.PHP_EOL;
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
		$txt .= '		$sql = "SELECT '.$this->listOfFieldNames.' FROM ".self::$table_name." WHERE {$cleanField} = ? ";'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '		if ($stmt = $database->get_connection()->prepare($sql)) {'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '			$stmt->bind_param("s", $cleanValue);'.PHP_EOL;
		$txt .= '			if ( false===$stmt) {'.PHP_EOL;
		$txt .= '				throw new Exception("Exception thrown ('.$this->className.':get_user_for_field_with_value_private) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
		$txt .= '			}'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '			$stmt->execute();'.PHP_EOL;
		$txt .= '			if ($stmt->errno) {'.PHP_EOL;
		$txt .= '				throw new Exception("Exception thrown ('.$this->className.':get_user_for_field_with_value_private) Error logged as: ".$stmt->error ,E_ALL);'.PHP_EOL;
		$txt .= '			}'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '			$stmt->store_result();'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '			$stmt->bind_result('.$this->listOfCommaSeparatedFieldNames.');'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '			$num_of_rows = $stmt->num_rows;'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '			$stmt->fetch();'.PHP_EOL;
		$txt .= '			'.PHP_EOL;
		foreach ($this->arrayFieldNames as &$item) {
			$fieldname = trim($item["fieldname"]);
			$txt .= '			$this->'.$fieldname.' = $'.$fieldname.';'.PHP_EOL;
		}
		$txt .= PHP_EOL;
		$txt .= '			return ($num_of_rows == 1) ? true : false;'.PHP_EOL;
		$txt .= '		}'.PHP_EOL;
		$txt .= PHP_EOL;
		$txt .= '		throw new Exception("ERROR - Querying email database table  (class:'.$this->className.' ; method:get_user_for_field_with_value_private): ".$database->get_connection()->error,E_ALL);'.PHP_EOL;
		$txt .= '		return false;'.PHP_EOL;
		$txt .= '	}'.PHP_EOL;
		
		return $txt;
		
	}
	
	
	
	
	// ------------------------------------------------------
	// return SQL for each type update, insert & delete 
	// ------------------------------------------------------
	private function returnSQL($type){
		
		$txt = ""; 
		
		switch ($type) {
		    case "INSERT":
				$txt .= '		$sql = "INSERT INTO ".self::$table_name." ('.$this->listOfFieldNames.') '.PHP_EOL;
				$txt .= '				VALUES ('.$this->placeholders.')";'.PHP_EOL;
		        break;
		    case "UPDATE":
				$txt .= '		$sql = "UPDATE ".self::$table_name." SET '.$this->listOfSETFieldNames.PHP_EOL;
				$txt .= '				WHERE '.$this->primaryKey.'=? LIMIT 1 ";'.PHP_EOL;
		        break;
		    case "DELETE":
		        $txt .= '		$sql = "DELETE FROM  ".self::$table_name." WHERE '.$this->primaryKey.'=? LIMIT 1 ";'.PHP_EOL;
		        break;
		    default:
		        echo "";
		}
		return $txt;
	}
	
	
	// --------------------------------------------------------
	// create the list of variables and placeholders needed 
	// to construct the class
	// --------------------------------------------------------
	private function createVariableLists(){
		
		$comma = "";
		$commaSpace = "";
		
		foreach ($this->arrayFieldNames as &$item) {
			
			$field = trim($item["fieldname"]); // field name
			$type = trim($item["type"]); // field type
			
			// creates a comma separated list of $variables
			$this->listOfCommaSeparatedFieldNames .=  $commaSpace."$".$field;
			
			// creates a comma separated list of field names
			$this->listOfFieldNames .=  $commaSpace.$field;
			
			// creates a comma separated list of field=? (for update query)
			$this->listOfSETFieldNames .= $commaSpace.$field."=?";
			
			// creates a comma separated list of placeholders ?
			$this->placeholders .= $comma."?"; // create placeholders for sql
			
			// create s for placeholders
			$this->s .= $this->returnPlaceholderType($type);
			
			$comma = ",";
			$commaSpace = ", ";
		}
		
		return true;
	}
	
	
	// --------------------------------------------------------
	// create the file
	// --------------------------------------------------------
	private function createTheFile(){
		
		if (!$this->classFile = fopen($this->filename, "w")) {
			$this->$error = "The file could not be created";
		    return fasle;
		}
		return true;
	}
	
	
	
	// --------------------------------------------------------
	// write to the file
	// --------------------------------------------------------
	private function writeToTheFile(){
		
		if(!fwrite($this->classFile, $this->txt)){
			$this->$error = "There was an error creating the file";
		    return fasle;
		}
		return true;
	}
	
	
	
	// --------------------------------------------------------
	// prepend filename with directories
	// --------------------------------------------------------
	private function setFileDirectory(){
		
		$this->filename = "include/classes/".$this->filename;
		
		return true;
	}
	
	
	// --------------------------------------------------------
	// Check we have primary key defined otherwise use first
	// column in the table
	// --------------------------------------------------------
	private function setPrimaryKey(){
		
		if(isSet($this->primaryKey) && trim($this->primaryKey) != ""){
			return true;
		}
		
		if(isSet($this->arrayFieldNames[0]['fieldname'])){
			$this->primaryKey = $this->arrayFieldNames[0]['fieldname'];
			$this->primaryKeyType = $this->returnPlaceholderType($this->arrayFieldNames[0]['type']);
			return true;
		}
		
		$this->$error  = "The table primary key column could not be found for: {$table}";
	    return false;
	}
	
	
	
	// --------------------------------------------------------
	// Get a list of field names & types for coluns in the table
	// --------------------------------------------------------
	private function setFieldNames(){
		
		global $database;
		
		// clean the field name
		$clean = new Sanitize();
		$table = $clean->clean($this->tablename);
		
		if ($result = mysqli_query($database->get_connection(), "SHOW COLUMNS FROM {$table}")) {
		
			// no columns found - is tabel name correct?
			if(mysqli_num_rows($result) < 1){
				$this->$error = "The table columns could not be found for: {$table}";
			    return false;
			}
	
			// create an array of arrays (fieldname, type)
			$i = 0;
			while ($row = mysqli_fetch_array($result)){		
				
				$fielditem = array("fieldname" => $row[0], "type" => $row[1]); //(fieldname, type)
			
				// add array to $arrayFieldNames array
				array_push($this->arrayFieldNames, $fielditem);
						
				// populate $primaryKey var if this is a primary key field 
				// with field name and $primaryKeyType with placeholder type 
				// (for prepared sql statements) 
				if($row[3] == "PRI" && !isSet($this->primaryKey)){
					$this->primaryKey = $row[0];
					$this->primaryKeyType = $this->returnPlaceholderType($row[1]);
				}
			}
	
		    mysqli_free_result($result);
			
			return true;
		}
		
		$this->error = "The table columns could not be found for: {$table}";
	    return false;
		
	}
	
	
	// ----------------------------------------------------------
	// return the place holder type eg s = string, d = double etc
	// ----------------------------------------------------------
	private function returnPlaceholderType($value){
			
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
	
	
	// ---------------------------------------------
	// check database connection is working
	// ---------------------------------------------
	private function isDatabaseConnectionValid(){
		
		global $database;
		
		if (!$database->get_connection()) {
			$this->$error = "ERROR  - Database Connect failed.<br/><br/>
			Ensure you have entered the database login information into the initialize.php file";
		    return false;
		}
		
		return true;
	}
	
	
	// ---------------------------------------------
	// set the filename for the template file
	// ---------------------------------------------
	private function setFilename($filename){
		
		// see if user has specified filename otherwise use default of class.classname.php
		if(isSet($filename) && trim($filename) != ""){
			$this->filename = $filename.".php";
		}else{
			$this->filename = strtolower("class.".$this->className).".php";
		}
		
		return true;		
	}
	
	
	// ---------------------------------------------
	// check we have permissions to create the file
	// ---------------------------------------------
	private function isFileWritable(){
		
		if (file_exists($this->filename) && !is_writable($this->filename)) {			
			$this->$error = "The file exists and is not writable";
			return false;
		}
		
		return true;
	}

	
}

?>