<?php
// Update participant data including any bookings
$Fixtureid=$_POST['Fixtureid'];
$Userid=$_POST['Userid'];
switch ($_POST['WantsToPlay']) {
    case "Unknown":
        $WantsToPlay=null;
        break;
    case "Yes":
        $WantsToPlay=TRUE;
        break;
    case "No":
        $WantsToPlay=FALSE;
        break;
}
$IsPlaying=$_POST['IsPlaying']=="Yes";
$Court1=$_POST['court1'];
$BookingTime1=$_POST['time1'];
$Court2=$_POST['court2'];
$BookingTime2=$_POST['time2'];

require_once('ConnectDB.php');
$conn = ConnectDB();

$result=$conn->query("SELECT WantsToPlay, IsPlaying FROM FixtureParticipants
WHERE Fixtureid=$Fixtureid AND Userid=$Userid;");
$row = $result->fetch_assoc();
if ($WantsToPlay!=$row['WantsToPlay'] || $IsPlaying!=$row['IsPlaying']) {
    $result=$conn->query("UPDATE FixtureParticipants
    SET WantsToPlay=$WantsToPlay, IsPlaying=$IsPlaying
    WHERE Fixtureid=$Fixtureid AND Userid=$Userid;");
}
/*
$sql="SELECT FirstName, LastName FROM Users WHERE Userid = $Userid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$UserName=$row['FirstName']." ".$row['LastName'];

if (!empty($Court1)){
    $sql="INSERT INTO CourtBookings (Fixtureid, Userid, CourtNumber, BookingTime)
    VALUES ($Fixtureid, $Bookerid, $Court1, '$BookingTime1');";
    $result = $conn->query($sql);
}
if (!empty($Court2)){
    $sql="INSERT INTO CourtBookings (Fixtureid, Userid, CourtNumber, BookingTime)
    VALUES ($Fixtureid, $Bookerid, $Court2, '$BookingTime2');";
    $result = $conn->query($sql);
}
*/

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

<p><b>Update fixture participant</b></p> 
<?php
echo "WantsToPlay: $WantsToPlay  IsPlaying: $IsPlaying<br>\n";
?>

<br>
<a class="pure-button pure-button-primary" href="Fixture.php?Fixtureid=<?=$Fixtureid?>">Done</a>

</body>
</html>