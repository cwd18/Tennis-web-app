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
// Remove specified fixture participants
$Fixtureid=$_POST['Fixtureid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

echo "<p>Remove people</p>\n"; 

foreach($_POST as $x => $x_value) {
    if ($x!="Fixtureid") {
        $Userid=$x_value;
        $sql="SELECT  FirstName, LastName FROM Users WHERE Userid = $Userid;";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $FirstName=$row['FirstName'];
        $LastName=$row['LastName'];
        echo "<p>Removing $FirstName $LastName</p>\n";
        $sql="DELETE FROM FixtureParticipants WHERE Fixtureid=$Fixtureid AND Userid=$Userid;";
        $result = $conn->query($sql);
    }
}

echo "<br><br>\n";
echo "<a class=\"pure-button pure-button-primary\" href=\"Fixture.php?Fixtureid=$Fixtureid\">Done</a>\n";
?>

</body>
</html>