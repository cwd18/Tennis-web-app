<?php
// Add the next fixture to the specified series
$Seriesid=$_GET['Seriesid'];

$DayName=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");

require_once('ConnectDB.php');
$conn = ConnectDB();

// Get basic series data...
$sql="SELECT Seriesid, SeriesOwner, SeriesWeekday, SeriesTime
FROM FixtureSeries WHERE Seriesid=$Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$FixtureOwner=$row['SeriesOwner'];
$Weekday=$row['SeriesWeekday'];
$Time=substr($row['SeriesTime'],0,5);

// Calculate the date of the next fixture
$Day=$DayName[$Weekday];
$d0=strtotime("+6 Days");
$d=strtotime("next ".$Day,$d0);
$FixtureDate=date("y-m-d",$d);
$d=strtotime($FixtureDate);
$dstr=date("l jS \of F Y",$d);

// Insert next fixture
$sql="INSERT INTO Fixtures (Seriesid, FixtureOwner, FixtureDate, FixtureTime)
VALUES ('$Seriesid', '$FixtureOwner', '$FixtureDate', '$Time');";
$result=$conn->query($sql);
$Fixtureid=$conn->insert_id;

// Initialise participants from series candidates
$sql="INSERT INTO FixtureParticipants (Fixtureid, Userid)
SELECT '$Fixtureid', Userid FROM SeriesCandidates WHERE Seriesid=$Seriesid;";
$result = $conn->query($sql);

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

<form class="pure-form pure-form-aligned" action="Fixture.php?Fixtureid=<?=$Fixtureid?>" method="post"> 
<fieldset>
<legend>Add new fixture</legend>
<p>Added fixture <?=$Fixtureid?> on <?=$dstr?> at <?=$Time?></p>
<br><br>
<button type="submit" class="pure-button pure-button-primary">Done</button>

</fieldset>
</form>

</body>
</html>