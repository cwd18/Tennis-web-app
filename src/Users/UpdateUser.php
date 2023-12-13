<?php
// Update specified user data
$Userid=$_POST['Userid'];
$NewFirstName=$_POST['fname'];
$NewLastName=$_POST['lname'];
$NewEmailAddress=$_POST['email'];

require_once('ConnectDB.php');
$conn = ConnectDB();

// Get existing user data
$sql="SELECT FirstName, LastName, EmailAddress FROM Users WHERE Userid=$Userid;";
$result=$conn->query($sql);
$row=$result->fetch_assoc();
$FirstName=$row['FirstName'];
$LastName=$row['LastName'];
$EmailAddress=$row['EmailAddress'];
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

<?php
// Update if any changes
if ($NewFirstName!=$FirstName or $NewLastName!=$LastName or $NewEmailAddress!=$EmailAddress){
    $sql="UPDATE Users SET FirstName='$NewFirstName', LastName='$NewLastName', EmailAddress='$NewEmailAddress'
    WHERE Userid=$Userid;";
    $result=$conn->query($sql);
    if ($NewFirstName!=$FirstName) {echo "<p>$FirstName -> $NewFirstName</p>\n";}
    if ($NewLastName!=$LasttName) {echo "<p>$LastName -> $NewLastName</p>\n";}
    if ($NewEmailAddress!=$EmailAddress) {echo "<p>$EmailAddress -> $NewEmailAddress</p>\n";}
} else {
    echo "<p>No changes made</p>\n";
}

$conn->close();
?>

<br>
<a class="pure-button pure-button-primary" href="ListUsers.php">Done</a>

</body>
</html>