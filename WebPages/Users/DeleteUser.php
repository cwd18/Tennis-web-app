<?php
// Delete specified user ID from Users table
$Userid=$_GET['UseridToDelete'];
require_once('ConnectDB.php');
$conn = ConnectDB();
$sql="SELECT  FirstName, LastName FROM Users WHERE Userid = $Userid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$FirstName=$row['FirstName'];
$LastName=$row['LastName'];
$sql="DELETE FROM Users WHERE Userid = $Userid;";
$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.custom-restricted-width {display: inline-block;}
* {margin-left: 2px;}
</style>
</head>
<body>

<?php
echo "<p>Deleted $FirstName $LastName</p><br>\n";
?>

<a href="ListUsers.php" class="pure-button pure-button-primary">Done</a>

</body>
</html>