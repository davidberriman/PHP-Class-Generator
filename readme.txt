# ========================================================================#
#
#  Author:    David Berriman
#  Version:	  1.1
#  Date:      29/08/2016
#  Purpose:   Create Class template with CRUD metods for MySQL table
#  Features:  Uses prepared statements and sanitizes user unput
#
#  Example:   To create a database record once the class has been made
#  
#  			  $class = new MyClass();
#  			  $class->id = 1
#			  $class->email = "userEmaail@userEmail.com";
#			  $class->create();
#
# ========================================================================#


This project is just a time saving tool to generate a PHP class for a database table. It comes with no warranty and is supplied 'as is' although you are free to use it as you please and I hope you find it as useful as I do!


How to Use:

*** Ensure you have entered the database login data in the include/initialize.php file ***

The structure of the classes generated uses a global variable called $database for the database connection which is required in include/initialize.php

The CRUD methdods use try/catch and logs all errors using the ErrorLog class which can be configured in the include/initialize.php file

The update & deleteRecord methods assume the table has only one key field - multiple fields will need manual intervention




---- To operate via the command line: ---- 
	
	
navigate to the 'include' directory then run the following replacing 'myClassName' with your class name and an optional filename. If not filename is provided it will default to class.myClassName.php
	
$ php createClass.php myClassName 

The PHP file will be genereted in the top level folder




----  To operate in a browser: ---- 

Enter the table name into the form

submit the form

The PHP file will be genereted in the top level folder
