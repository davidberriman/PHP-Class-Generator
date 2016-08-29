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
<p>Creates a default template PHP file with a class that has CRUD methods for the database table.</p>

<?php echo isset($message) ? "<div class='".$messageClass."'>".$message."</div>" : "" ?>

<hr/>
<label for="tableName">Table:</label>
<input type='text' name='tableName' id='tableName' value='<?php echo isSet($_POST['table']) ? $_POST['tableName'] : "" ; ?>' placeholder="Table name"/>

<label for="fileName">Filename:</label>
<input type='text' name='fileName' id='fileName'  placeholder="Filename for class file (optional)" />

<input type='submit' name='Submit' value='Submit'/>

</fieldset>
</form>
</body>
</html>