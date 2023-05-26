<?php
// Lists the specified fixture series, showing any default invitees and fixtures
// Includes a menu of commands to manage a series 
$Seriesid=$_GET["Seriesid"];

$DayName=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");

require_once('ConnectDB.php');
$conn = ConnectDB();

// Retrieve basic series data...
$sql="SELECT FirstName, LastName, SeriesName, SeriesWeekday, SeriesTime 
FROM Users, FixtureSeries WHERE Seriesid=$Seriesid AND Users.Userid=FixtureSeries.SeriesOwner;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$SeriesName=$row['SeriesName'];
$Day=$DayName[$row['SeriesWeekday']];
$Time=substr($row['SeriesTime'],0,5);
$OwnerName=$row['FirstName']." ".$row['LastName'];

// Get default fixture attendees...
$sql="SELECT Users.Userid, FirstName, LastName FROM Users, SeriesCandidates
WHERE Seriesid=$Seriesid AND Users.Userid=SeriesCandidates.Userid
ORDER BY FirstName, LastName;";
$result = $conn->query($sql);
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $ParticipantList[$row['Userid']]=$row['FirstName']." ".$row['LastName'];
    }

// Get most recent fixtures for this series...
$sql="SELECT Fixtureid, FixtureDate, FixtureTime FROM Fixtures 
WHERE Seriesid=$Seriesid ORDER BY FixtureDate DESC LIMIT 5;";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $Fixtureid=$row['Fixtureid'];
    $FixtureDate=$row["FixtureDate"];
    $d=strtotime($FixtureDate);
    $dstr=date("l jS \of F Y",$d);
    $FixtureList[$Fixtureid]['dstr']=$dstr;
    $FixtureList[$Fixtureid]['time']=substr($row["FixtureTime"],0,5);
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
<a href="ListSeries.php" class="pure-menu-heading pure-menu-link">Series</a>
<ul class="pure-menu-list">
<li class="pure-menu-item">
<a href="AddFixture.php?Seriesid=<?=$Seriesid?>" class="pure-menu-link">Add fixture</a>
</li>
<li class="pure-menu-item">
<a href="AddSeriesCandidates.php?Seriesid=<?=$Seriesid?>" class="pure-menu-link">Add people</a>
</li>
<li class="pure-menu-item">
<a href="RemSeriesCandidates.php?Seriesid=<?=$Seriesid?>" class="pure-menu-link">Remove people</a>
</li>
<li class="pure-menu-item">
<a href="EditSeries.php?Seriesid=<?=$Seriesid?>" class="pure-menu-link">Edit series data</a>
</li>
<li class="pure-menu-item">
<a href="RemSeries.php?Seriesid=<?=$Seriesid?>" class="pure-menu-link">Remove series</a>
</li>
</ul>
</div>

<h2><?=$SeriesName?></h2>
<p>Series owner: <?=$OwnerName?></p> 
<p>New fixture defaults to <?=$Day?> at <?=$Time?></p>

<p><b>Default fixture invitees:</b></p>
<?php
if (isset($ParticipantList)) {
    echo "<ol>\n";
    foreach ($ParticipantList as $x => $x_value) {
        echo "<li>$x_value</li>\n";
        }
    echo "</ol>\n";
}
else {
    echo "<p><b>No fixture invitees</b></p>\n";
}
?>

<p><b>Fixtures (most recent first):</b></p>
<table class="pure-table"><thead><tr><th>Date</th><th>Time</th></tr></thead><tbody>
<?php
if (isset($FixtureList)) {
    foreach ($FixtureList as $x => $x_value) {
        echo "<tr><td><a href=\"Fixture.php?Fixtureid=$x\">{$x_value['dstr']}</a></td>
        <td>{$x_value['time']}</td></tr>\n";
    }
}
?>

</tbody></table>

<br>

</body>
</html>