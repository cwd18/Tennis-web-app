<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
* {margin-left: 2px;}
</style>
</head>
<body>

<form class="pure-form pure-form-stacked" action="UpdateUser.php" method="post">
<fieldset>
   
<?php
// Edit specified user data
$Userid=$_GET['Userid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

// Get existing user data
$sql="SELECT FirstName, LastName, EmailAddress FROM Users WHERE Userid=$Userid";
$result=$conn->query($sql);
$row=$result->fetch_assoc();
$FirstName=$row['FirstName'];
$LastName=$row['LastName'];
$EmailAddress=$row['EmailAddress'];

// create form elements
echo "<legend>User ID: $Userid</legend>\n";
echo "<input type=\"hidden\" name=\"Userid\" value=\"$Userid\">\n";
echo "<label for=\"fname\">First Name</label>\n";
echo "<input type=\"text\" name=\"fname\" id=\"fname\" value=\"$FirstName\"/>\n";
echo "<label for=\"lname\">Last Name</label>\n";
echo "<input type=\"text\" name=\"lname\" id=\"lname\" value=\"$LastName\"/>\n";
echo "<label for=\"email\">Email</label>\n";
echo "<input style=\"width: 300px;\" type=\"email\" name=\"email\" id=\"email\" value=\"$EmailAddress\"/>\n";

$conn->close();
?>

<button type="submit" class="pure-button pure-button-primary">Update user data</button>

</fieldset>
</form>

<a class="pure-button" href="ListUsers.php">Cancel any edits</a>
<br><br>

<?php
echo "<a class=\"pure-button\" href=\"DeleteUser.php?UseridToDelete=$Userid\">Delete this user</a>\n";
?>

</body>
</html>