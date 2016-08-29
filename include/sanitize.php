<?php
# ========================================================================#
# Sanitize all user input
# ========================================================================#

class Sanitize
{
	// ------------------------------------------------------
	//  Sanitize all input
	// ------------------------------------------------------
	public function clean($data) 
	{
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  
	  return $data;
	}
}

?>