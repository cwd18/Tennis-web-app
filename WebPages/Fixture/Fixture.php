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

// Get court bookings into grid with columns (court, booking time, bookers)
$Grid[0][0]="Court";
$sql="SELECT DISTINCT BookingTime FROM CourtBookings WHERE Fixtureid=$Fixtureid;";
$result = $conn->query($sql);
$c=1;
while ($row = $result->fetch_assoc()) {
    $Grid[0][$c]=substr($row['BookingTime'],0,5);
    $c++;
}
$BookersColumn=$c;
$Grid[0][$BookersColumn]="Bookers";

$sql="SELECT DISTINCT CourtNumber FROM CourtBookings WHERE Fixtureid=$Fixtureid;";
$result = $conn->query($sql);
$r=1;
while ($row = $result->fetch_assoc()) {
    $Grid[$r][0]=$row['CourtNumber'];
    for ($c=1;$c<=$BookersColumn;$c++) {$Grid[$r][$c]="-";}
    $r++;
}
$GridRows=$r;

$sql="SELECT FirstName, LastName, CourtNumber, BookingTime FROM Users, CourtBookings
WHERE Fixtureid=$Fixtureid and Users.Userid=CourtBookings.Userid
ORDER BY CourtNumber, BookingTime;";
$result = $conn->query($sql);
$n=0;
while ($row = $result->fetch_assoc()) {
    $Name=$row['FirstName']." ".$row['LastName'];
    for ($r=1;$Grid[$r][0]!=$row['CourtNumber'];$r++) {} // match grid row
    for ($c=1;$Grid[0][$c]!=substr($row['BookingTime'],0,5);$c++) {} // match grid column
    $Grid[$r][$c]=$row['CourtNumber'];
    if ($Grid[$r][$BookersColumn]=="-") {
        $Grid[$r][$BookersColumn]=$Name;
    } else if ($Grid[$r][$BookersColumn]!=$Name) {
        $Grid[$r][$BookersColumn]=$Grid[$r][$BookersColumn].", ".$Name;
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
if ($BookersColumn>1) {
    echo "<table><tr>\n";
    for ($c=1;$c<=$BookersColumn;$c++) {echo "<th>{$Grid[0][$c]}</th>\n";}
    echo "</tr>\n";
    for ($r=1;$r<$GridRows;$r++) {
        echo "<tr>\n";
        for ($c=1;$c<=$BookersColumn;$c++) {echo "<td>{$Grid[$r][$c]}</td>\n";}
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "<p>No bookings yet</p>\n";
}
?>

</body>
</html>