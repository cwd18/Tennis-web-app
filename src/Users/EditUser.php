<?php
// Edit specified user data
$Userid=$_GET['Userid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

// Get existing user data for form values
$sql="SELECT FirstName, LastName, EmailAddress FROM Users WHERE Userid=$Userid";
$result=$conn->query($sql);
$row=$result->fetch_assoc();
$FirstName=$row['FirstName'];
$LastName=$row['LastName'];
$EmailAddress=$row['EmailAddress'];
$conn->close();
?>

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
<legend>User ID: <?php echo $Userid;?></legend>
<input type="hidden" name="Userid" value="<?php echo $Userid;?>">
<label for="fname">First Name</label>
<input type="text" name="fname" id="fname" value="<?php echo $FirstName;?>"/>
<label for="lname">Last Name</label>
<input type="text" name="lname" id="lname" value="<?php echo $LastName;?>"/>
<label for="email">Email</label>
<input style="width: 300px;" type="email" name="email" id="email" value="<?php echo $EmailAddress;?>"/>

<button type="submit" class="pure-button pure-button-primary">Update user data</button>

</fieldset>
</form>

<a class="pure-button" href="ListUsers.php">Cancel any edits</a>
<br><br>

<a class="pure-button" href="DeleteUser.php?UseridToDelete=<?php echo $Userid;?>">Delete this user</a>

</body>
</html>