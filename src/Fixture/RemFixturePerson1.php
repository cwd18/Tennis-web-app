<?php
// Remove specified fixture participants
$Fixtureid=$_POST['Fixtureid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

foreach($_POST as $x => $x_value) {
    if ($x!="Fixtureid") {
        $Userid=$x_value;
        $sql="SELECT  FirstName, LastName FROM Users WHERE Userid = $Userid;";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $FirstName=$row['FirstName'];
        $LastName=$row['LastName'];
        $echostr[$Userid]="<p>Removed $FirstName $LastName</p>\n";
        $sql="DELETE FROM FixtureParticipants WHERE Fixtureid=$Fixtureid AND Userid=$Userid;";
        $result = $conn->query($sql);
    }
}
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

<p>Remove people from fixture</p>

<?php
foreach($echostr as $x => $x_value) {
    echo $x_value;
}
?>

<br><br>
<a class="pure-button pure-button-primary" href="Fixture.php?Fixtureid=<?=$Fixtureid?>">Done</a>

</body>
</html>