<?php
// Update fixture from passed parameters
$Fixtureid=$_POST['Fixtureid'];
$NewFixtureOwner=$_POST['owner'];
$NewFixtureDate=$_POST['date'];
$NewFixtureTime=$_POST['time'];

require_once('ConnectDB.php');
$conn = ConnectDB();

$sql="SELECT FixtureOwner, FixtureDate, FixtureTime FROM Fixtures
WHERE Fixtureid=$Fixtureid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$FixtureOwner=$row['FixtureOwner'];
$FixtureDate=$row['FixtureDate'];
$FixtureTime=substr($row['FixtureTime'],0,5);
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

<p><b>Update fixture</b></p> 

<?php
// Update if any changes
if ($NewFixtureOwner!=$FixtureOwner or
    $NewFixtureDate!=$FixtureDate or 
    $NewFixtureTime!=$FixtureTime) {
    $sql="UPDATE Fixtures 
    SET FixtureOwner='$NewFixtureOwner', FixtureDate='$NewFixtureDate', FixtureTime='$NewFixtureTime'
    WHERE Fixtureid=$Fixtureid;";
    $result=$conn->query($sql);
    if ($NewFixtureOwner!=$FixtureOwner) {echo "<p>Owner ID $FixtureOwner -> $NewFixtureOwner</p>\n";}
    if ($NewFixtureDate!=$FixtureDate) {echo "<p>Fixture date $FixtureDate -> $NewFixtureDate</p>\n";}
    if ($NewFixtureTime!=$FixtureTime) {echo "<p>Fixture time $FixtureTime -> $NewFixtureTime</p>\n";}
} else {
    echo "<p>No changes made</p>\n";
}
$conn->close();
?>

<a class="pure-button pure-button-primary" href="Fixture.php?Fixtureid=<?=$Fixtureid?>">Done</a>

</body>
</html>