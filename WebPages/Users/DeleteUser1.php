<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/grids-responsive-min.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.custom-restricted-width {display: inline-block;}
* {margin-left: 2px;}
</style>
</head>
<body>

<form class="pure-form pure-form-aligned" action="ListUsers.php" method="post"> 
<fieldset>
<legend>Delete person</legend> 

<?php
$servername = "localhost";
$username = "tennisapp";
$password = "Tennis=LT28";
$dbname = "Tennis";
$conn = new mysqli($servername, $username, $password, $dbname);

$Userid=$_POST['UseridToDelete'];

$sql="SELECT  FirstName, LastName FROM Users WHERE Userid = $Userid";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$FirstName=$row['FirstName'];
$LastName=$row['LastName'];

echo "<p>Deleted $FirstName $LastName</p><br>\n";

$sql="DELETE FROM Users WHERE Userid = $Userid;";
$result = $conn->query($sql);

$conn->close();
?>

<button type="submit" class="pure-button pure-button-primary">Done</button>

</fieldset>
</form>

</body>
</html>