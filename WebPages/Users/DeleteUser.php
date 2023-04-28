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

<?php
require_once('ConnectDB.php');
$conn = ConnectDB();

$Userid=$_GET['UseridToDelete'];

$sql="SELECT  FirstName, LastName FROM Users WHERE Userid = $Userid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$FirstName=$row['FirstName'];
$LastName=$row['LastName'];

echo "<p>Deleted $FirstName $LastName</p><br>\n";

$sql="DELETE FROM Users WHERE Userid = $Userid;";
$result = $conn->query($sql);

$conn->close();
?>

<a href="ListUsers.php" class="pure-button pure-button-primary">Done</a>

</body>
</html>