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

<?php
// Add the next fixture to the specified series
$Seriesid=$_GET["Seriesid"];

echo "<form class=\"pure-form pure-form-aligned\" action=\"Series.php?Seriesid=$Seriesid\" method=\"post\">\n"; 
echo "<fieldset>\n";
echo "<legend>Add new fixture</legend>\n";

$servername = "localhost";
$username = "tennisapp";
$password = "Tennis=LT28";
$dbname = "Tennis";
$conn = new mysqli($servername, $username, $password, $dbname);

// Get basic series data...
$sql="SELECT Seriesid, SeriesName, SeriesWeekday, SeriesTime
FROM FixtureSeries
WHERE Seriesid=$Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$Name=$row["SeriesName"];
$Weekday=$row["SeriesWeekday"];
$Time=substr($row["SeriesTime"],0,5);

// Calculate the date of the next fixture
$DayName=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
$Day=$DayName[$Weekday];
$d=strtotime("next ".$Day);
$FixtureDate=date("y-m-d",$d);

// Insert next fixture
$sql="INSERT INTO Fixtures (Seriesid, FixtureDate, FixtureTime)
VALUES ('$Seriesid', '$FixtureDate', '$Time');";

if ($conn->query($sql) === TRUE) {
    echo "Adding fixture on $FixtureDate at $Time<br>\n";
  } else {
    echo "Error: " . $sql . "<br>" . $conn->error, "<br>\n";
  }

$conn->close();
?>

<br>
<button type="submit" class="pure-button pure-button-primary">Done</button>

</fieldset>
</form>

</body>
</html>