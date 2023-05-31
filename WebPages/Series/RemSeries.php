<?php
// Remove the specified fixture series 
// The SQL DELETE FROM will fail if the series is referenced by any invitees or fixtures
$DayName=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");

$Seriesid=$_GET['Seriesid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

$sql="SELECT  SeriesWeekday, SeriesTime FROM FixtureSeries WHERE Seriesid = $Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$Day=$DayName[$row['SeriesWeekday']];
$Time=substr($row['SeriesTime'],0,5);
$SeriesName=$Day." at ".$Time;

$sql="DELETE FROM FixtureSeries WHERE Seriesid=$Seriesid;";
$result = $conn->query($sql);
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

<form class="pure-form pure-form-aligned" action="ListSeries.php" method="post">
<fieldset>
<legend>Remove fixture series</legend>

<?php
if ($result === TRUE) {
    echo "<p>Removed the fixture series \"$SeriesName\"</p>\n";
  } else {
    echo "<p>Couldn't delete the fixture series \"$SeriesName\": " , $conn->error, "</p>\n";
  }
$conn->close();
?>

<br>
<button type="submit" class="pure-button pure-button-primary">Done</button>

</fieldset>
</form>

</body>
</html>