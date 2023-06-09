<?php
// Add specified people to fixture
$Fixtureid=$_POST['Fixtureid'];
require_once('ConnectDB.php');
$conn = ConnectDB();
if (count($_POST)>1) {
    foreach($_POST as $x => $x_value) {
        if ($x!="Fixtureid") {
            $Userid=$x_value;
            $sql="SELECT  FirstName, LastName FROM Users WHERE Userid = $Userid;";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $FirstName=$row['FirstName'];
            $LastName=$row['LastName'];
            $echostr[$Userid]="Added $FirstName $LastName";
            $sql="INSERT INTO FixtureParticipants (Fixtureid, Userid, RequestTime)
            VALUES ($Fixtureid, $Userid, null);";
            $result = $conn->query($sql);
        }
    }
} else {
    $echostr[0]="No user selected";
}
$conn->close();
?>

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

<form class="pure-form pure-form-aligned" action="Fixture.php?Fixtureid=<?=$Fixtureid?>" method="post">
<fieldset>
<legend>Add people to fixture</legend>

<?php
foreach($echostr as $x => $x_value) {
    echo "<p>$x_value</p>\n";
}
?>

<br>
<button type="submit" class="pure-button pure-button-primary">Done</button>

</fieldset>
</form>

</body>
</html>