<?php
// Lists the specified fixture, showing any invitees, participant, and court bookings
// Includes a menu of commands to manage a fixture 
$Fixtureid=$_GET['Fixtureid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

// Get Fixture data
$sql="SELECT Fixtures.Seriesid, SeriesName, FirstName, LastName, FixtureDate, FixtureTime
FROM Fixtures, Users, FixtureSeries
WHERE Fixtureid=$Fixtureid 
AND Fixtures.FixtureOwner=Users.Userid AND Fixtures.Seriesid=FixtureSeries.Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$Seriesid=$row['Seriesid'];
$SeriesName=$row['SeriesName'];
$OwnerName=$row['FirstName']." ".$row['LastName'];
$FixtureDate=$row['FixtureDate'];
$FixtureTime=substr($row['FixtureTime'],0,5);
$d=strtotime($FixtureDate);
$dstr=date("l jS \of F Y",$d);

// Get participants...
$sql="SELECT Users.Userid, FirstName, LastName FROM Users, FixtureParticipants
WHERE Fixtureid=$Fixtureid AND Users.Userid=FixtureParticipants.Userid
ORDER BY FirstName, LastName;";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $ParticipantList[$row['Userid']]=$row['FirstName']." ".$row['LastName'];
    }

// Get court bookings
$sql="SELECT FirstName, LastName, CourtNumber, BookingTime FROM Users, CourtBookings
WHERE Fixtureid=$Fixtureid and Users.Userid=CourtBookings.Userid;";
$result = $conn->query($sql);
$n=0;
while ($row = $result->fetch_assoc()) {
    $Bookings[$n]['Name']=$row['FirstName']." ".$row['LastName'];
    $Bookings[$n]['Court']=$row['CourtNumber'];
    $Bookings[$n]['Time']=substr($row['BookingTime'],0,5);
    $n++;
}
$conn->close();
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

<div class="pure-menu pure-menu-scrollable custom-restricted">
<a href="Series.php?Seriesid=<?=$Seriesid?>" class="pure-menu-heading pure-menu-link">Fixture</a>
<ul class="pure-menu-list">
<li class="pure-menu-item">
<a href="AddBooking.php?Fixtureid=<?=$Fixtureid?>" class="pure-menu-link">Add booking</a>
</li>
<li class="pure-menu-item">
<a href="AddFixturePerson.php?Fixtureid=<?=$Fixtureid?>" class="pure-menu-link">Add people</a>
</li>
<li class="pure-menu-item">
<a href="RemFixturePerson.php?Fixtureid=<?=$Fixtureid?>" class="pure-menu-link">Remove people</a>
</li>
<li class="pure-menu-item">
<a href="EditFixture.php?Fixtureid=<?=$Fixtureid?>" class="pure-menu-link">Edit fixture data</a>
</li>
<li class="pure-menu-item">
<a href="RemFixture.php?Fixtureid=<?=$Fixtureid?>" class="pure-menu-link">Remove fixture</a>
</li>
</ul>
</div>

<h2><?=$SeriesName?></h2>
<p>On <?=$dstr?> at <?=$FixtureTime?></p>
<p>Fixture owner: <?=$OwnerName?></p> 


<b>Fixture participants:</b>
<ol>
<?php
// List participants...
if (isset($ParticipantList)) {
    foreach ($ParticipantList as $x => $x_value) {
        echo "<li>$x_value</li>\n";
    }
}
?>
</ol>

<p><b>Fixture bookings:</b></p>
<?php
// List bookings
if (isset($Bookings)) {
    foreach ($Bookings as $value) {
        echo "<p>{$value['Name']}: court {$value['Court']} at {$value['Time']}\n";
    }
}
?>

</body>
</html>