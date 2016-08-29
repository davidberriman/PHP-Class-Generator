<?php
# ========================================================================#
#
#  Keep a lof of all errors in either a log file / email which is specified
#  in the initialize.php form
# 
# ========================================================================#
class ErrorLog extends MySQLDatabase
{
	protected static $table_name="errorLogs";
	
	public $email;
	public $errorlog;
	public $errornumber;
	public $errorDateTime;
	public $id;
	
	public $errors = array();
	
	
	public function save(){	
			
		if(isset($this->id))
		{
			return $this->update();
		}else
		{
			return $this->create();
		}
	}
	
	
	public function createErrorLog($error){
		
		if(!isSet($error) || $error == "")
		{
			return;
		}
		
		if(ERROR_LOG_TYPE == "email")
		{			
			if($this->emailError($error))
			{
				return true;
			}
			
			return false;
		}	
		
		if(ERROR_LOG_TYPE == "file")
		{
			$this->logError($error);
			return;
		}
		
	}
	
	
	private function logError($error){
		
		$error = $error.PHP_EOL;
		
		if(null !== ERROR_LOG_DIR && file_exists(ERROR_LOG_DIR))
		{
			error_log($error, 3, ERROR_LOG_DIR, "");
			return;
		}
		
		// try to make a log file if it doesn't exist
		if(mkdir(LIB_PATH.DS."logs", 0777, true)) {
			if(fopen(ERROR_LOG_DIR, "w")){
				error_log($error, 3, ERROR_LOG_DIR, "");
				return;
			}
		}
		
		error_log($error,0, "", "");
	}
	
	
	private function emailError($error){
		
        $mailer = new PHPMailer();
    
        $mailer->CharSet = 'utf-8';
    
        $mailer->AddAddress(ADMIN_EMAIL, ADMIN_NAME);
    
        $mailer->Subject = "Error log";

        $mailer->From = WEBSITE_EMAIL;        

        $mailer->Body = $error;

        if(!$mailer->Send())
        {
            return false;
        }
		
		return true;
	}
	
}

?>