<?php
require_once("createClassTemplate.php");
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
	$classNameForTemplate = $argv[1];
	
	// see if user has specified filename otherwise use default of class.classname.php
	if(isSet($argv[2]) && trim($argv[2]) != ""){
		$filenameForTemplate = $argv[2].".php";
	}else{
		$filenameForTemplate = "";
	}
	
	createClass($classNameForTemplate, $filenameForTemplate);

?>