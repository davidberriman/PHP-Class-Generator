<?PHP require_once("controllers/generator_controller.php"); ?>
<!DOCTYPE html>
<html lang='en-gb'>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>PHP Class Generator</title>
	<link rel="STYLESHEET" href="css/main.css"/>
</head>
<body>

<form action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>' method='post' accept-charset='UTF-8'>
<fieldset>
<legend>PHP Class Generator</legend>
<p>Creates a default template PHP file with a class that has CRUD methods for <strong>EACH</strong> of the tables in the database.</p>
<a href="index.php">Home</a>

<?php echo isset($message) ? "<div class='".$messageClass."'>".$message."</div>" : "" ?>

<hr/>

<input type='hidden' name='updateAll' value="1"/>

<input type='submit' name='Submit' value='Generate'/>

</fieldset>
</form>
</body>
</html>