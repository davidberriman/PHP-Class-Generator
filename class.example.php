<?php 
if(file_exists("include/initialize.php")){require_once("include/initialize.php");};

class Example extends MySQLDatabase 
{ 
	protected static $table_name="example";
	public $error; 

	public $username;
	public $login_count;
	public $last_time;
	public $address;



	// --------------------------------------------------------------------------
	// Public function to create a new record
	// --------------------------------------------------------------------------
	public function create(){ 

		$username = $this->escape_value($this->username);
		$login_count = $this->escape_value($this->login_count);
		$last_time = $this->escape_value($this->last_time);
		$address = $this->escape_value($this->address);

		try
		{ 
			return $this->createRecord($username, $login_count, $last_time, $address); 
		} 
		catch (Exception $e){ 
			$error = new ErrorLog();
			$error->createErrorLog($e);
			return false;		
		}
	} 



	// --------------------------------------------------------------------------
	// Private function used with try / catch to create a new record
	// --------------------------------------------------------------------------
	private function createRecord($username, $login_count, $last_time, $address){

		global $database;

		$sql = "INSERT INTO ".self::$table_name." (username, login_count, last_time, address) 
				VALUES (?,?,?,?)";

		if ($stmt = $database->get_connection()->prepare($sql)) {

			$stmt->bind_param("siis", $username, $login_count, $last_time, $address);
			if ( false===$stmt) {
				throw new Exception("Exception thrown (Example:createRecord) Error logged as: ".$stmt->error ,E_ALL);
			}

			$stmt->execute();
			if ($stmt->errno) {
				throw new Exception("Exception thrown (Example:createRecord) Error logged as: ".$stmt->error ,E_ALL);
			}

			return (mysqli_affected_rows($database->get_connection()) == 1) ? true : false;
		}

		throw new Exception("ERROR - Querying email database table (class:Example ; method:createRecord) : ".$database->get_connection()->error,E_ALL);
		return false;
	}



	// --------------------------------------------------------------------------
	// Public function to update an existing record
	// --------------------------------------------------------------------------
	public function update(){ 

		$username = $this->escape_value($this->username);
		$login_count = $this->escape_value($this->login_count);
		$last_time = $this->escape_value($this->last_time);
		$address = $this->escape_value($this->address);

		try
		{ 
			return $this->updateRecord($username, $login_count, $last_time, $address); 
		} 
		catch (Exception $e){ 
			$error = new ErrorLog();
			$error->createErrorLog($e);
			return false;		
		}
	} 



	// --------------------------------------------------------------------------
	// Private function used with try / catch to update an existing record
	// --------------------------------------------------------------------------
	private function updateRecord($username, $login_count, $last_time, $address){

		global $database;

		$sql = "UPDATE ".self::$table_name." SET username=?, login_count=?, last_time=?, address=? 
							WHERE username=? LIMIT 1 ";

		if ($stmt = $database->get_connection()->prepare($sql)) {

			$stmt->bind_param("siiss", $username, $login_count, $last_time, $address , $username);
			if ( false===$stmt) {
				throw new Exception("Exception thrown (Example:updateRecord) Error logged as: ".$stmt->error ,E_ALL);
			}

			$stmt->execute();
			if ($stmt->errno) {
				throw new Exception("Exception thrown (Example:updateRecord) Error logged as: ".$stmt->error ,E_ALL);
			}

			return (mysqli_affected_rows($database->get_connection()) == 1) ? true : false;
		}

		throw new Exception("ERROR - Querying email database table (class:Example ; method:updateRecord) : ".$database->get_connection()->error,E_ALL);
		return false;
	}



	// --------------------------------------------------------------------------
	// Public function to delete an existing record
	// --------------------------------------------------------------------------
	public function delete(){ 

		try
		{ 
			return $this->deleteRecord(); 
		} 
		catch (Exception $e){ 
			$error = new ErrorLog();
			$error->createErrorLog($e);
			return false;		
		}
	} 



	// --------------------------------------------------------------------------
	// Private function used with try / catch to delete an existing record
	// --------------------------------------------------------------------------
	private function deleteRecord(){

		global $database;

		$keyField = $this->username;

		if(!isSet($keyField))
 		{
 			return false;
 		}

		$sql = "DELETE FROM  ".self::$table_name." WHERE username=? LIMIT 1 ";

		if ($stmt = $database->get_connection()->prepare($sql)) {

			$stmt->bind_param("s", $keyField);
			if ( false===$stmt) {
				throw new Exception("Exception thrown (Example:deleteRecord) Error logged as: ".$stmt->error ,E_ALL);
			}

			$stmt->execute();
			if ($stmt->errno) {
				throw new Exception("Exception thrown (Example:deleteRecord) Error logged as: ".$stmt->error ,E_ALL);
			}

			return (mysqli_affected_rows($database->get_connection()) == 1) ? true : false;
		}

		throw new Exception("ERROR - Querying email database table  (class:Example ; method:deleteRecord) : ".$database->get_connection()->error,E_ALL);
		return false;
	}



	// --------------------------------------------------------------------------
	// Public function to retrieve a record using a field value eg email=test@test.com
	// --------------------------------------------------------------------------
	public function get_user_for_field_with_value($field, $value){ 

		try
		{ 
			return $this->get_user_for_field_with_value_private($field, $value); 
		} 
		catch (Exception $e){ 
			$error = new ErrorLog();
			$error->createErrorLog($e);
			return false;		
		}
	} 



	// --------------------------------------------------------------------------
	// Private function used with try / catch to retrieve an existing record
	// --------------------------------------------------------------------------
	private function get_user_for_field_with_value_private($field, $value){

		global $database;

		$cleanField = $this->escape_value($field);
		$cleanValue = $this->escape_value($value);

		if(!isset($cleanValue) || !isset($cleanField))
 		{
 			return false;
 		}

		$sql = "SELECT username, login_count, last_time, address FROM ".self::$table_name." WHERE {$cleanField} = ? ";

		if ($stmt = $database->get_connection()->prepare($sql)) {

			$stmt->bind_param("s", $cleanValue);
			if ( false===$stmt) {
				throw new Exception("Exception thrown (Example:get_user_for_field_with_value_private) Error logged as: ".$stmt->error ,E_ALL);
			}

			$stmt->execute();
			if ($stmt->errno) {
				throw new Exception("Exception thrown (Example:get_user_for_field_with_value_private) Error logged as: ".$stmt->error ,E_ALL);
			}

			$stmt->store_result();

			$stmt->bind_result($username, $login_count, $last_time, $address);

			$num_of_rows = $stmt->num_rows;

			$stmt->fetch();
			
			$this->username = $username;
			$this->login_count = $login_count;
			$this->last_time = $last_time;
			$this->address = $address;

			return ($num_of_rows == 1) ? true : false;
		}

		throw new Exception("ERROR - Querying email database table  (class:Example ; method:get_user_for_field_with_value_private): ".$database->get_connection()->error,E_ALL);
		return false;
	}



} ?> 