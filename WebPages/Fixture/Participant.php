<?php
// Display/edit specified fixture participant data 
$Fixtureid=$_GET['Fixtureid'];
$Userid=$_GET['Userid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

// Get Fixture data
$sql="SELECT Fixtures.Seriesid, FirstName, LastName, FixtureDate, FixtureTime
FROM Fixtures, Users, FixtureSeries
WHERE Fixtureid=$Fixtureid 
AND Fixtures.FixtureOwner=Users.Userid AND Fixtures.Seriesid=FixtureSeries.Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$Seriesid=$row['Seriesid'];
$OwnerName=$row['FirstName']." ".$row['LastName'];
$FixtureDate=$row['FixtureDate'];
$FixtureTime=substr($row['FixtureTime'],0,5);
$d=strtotime($FixtureDate);
$dstr=date("l jS \of F Y",$d);

// Get participant data
$sql="SELECT Users.Userid, FirstName, LastName, WantsToPlay, IsPlaying FROM Users, FixtureParticipants
WHERE Fixtureid=$Fixtureid AND FixtureParticipants.Userid=$Userid 
AND Users.Userid=FixtureParticipants.Userid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$UserName=$row['FirstName']." ".$row['LastName'];
$IsPlaying=$row['IsPlaying']?"Yes":"No";
switch ($row['WantsToPlay']) {
    case null:
        $WantsToPlay="Unknown";
        break;
    case TRUE:
        $WantsToPlay="Yes";
        break;
    case FALSE:
        $WantsToPlay="No";
        break;
}

// Get user bookings
$sql="SELECT CourtNumber, BookingTime FROM CourtBookings
WHERE Fixtureid=$Fixtureid AND Userid=$Userid
ORDER BY BookingTime;";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $Booking[substr($row['BookingTime'],0,5)]=$row['CourtNumber'];
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.custom-restricted {
    height: 100px;
    width: 160px;
    border: 1px solid gray;
    border-radius: 4px;
    }
* {margin-left: 2px;}
</style>
</head>
<body>

<h2><?=$UserName?></h2> 
<p>For <?=$dstr?> at <?=$FixtureTime?></p>
<p>Wants to play: <?=$WantsToPlay?></p>
<p>Is playing: <?=$IsPlaying?></p>
<?php
if (isset($Booking)) {
    echo "Courts booked:<br>\n";
    foreach ($Booking as $Time => $Court) {
        echo "$Time: $Court<br>\n";
    }   
} else {
    echo "No courts booked<br>\n";
}
?>

<br>
<a class="pure-button pure-button-primary" href="Fixture.php?Fixtureid=<?=$Fixtureid?>">Done</a>

</body>
</html>