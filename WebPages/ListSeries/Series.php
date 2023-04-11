<!DOCTYPE html>
<!-- Series view ccat -->
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
$Seriesid=$_GET["Seriesid"];
echo "<a href=\"AddSeriesCandidates.php?Seriesid={$Seriesid}\">Add people  </a>\n";
echo "<a href=\"RemSeriesCandidates.php?Seriesid={$Seriesid}\">Remove people </a>\n";

$DayName=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
$servername = "localhost";
$username = "tennisapp";
$password = "Tennis=LT28";
$dbname = "Tennis";
$conn = new mysqli($servername, $username, $password, $dbname);

$sql="SELECT Seriesid, SeriesName, SeriesWeekday, SeriesTime
FROM FixtureSeries
WHERE Seriesid={$Seriesid}";

$result = $conn->query($sql);

$row = $result->fetch_assoc();
$Name=$row["SeriesName"];
$Day=$DayName[$row["SeriesWeekday"]];
$Time=substr($row["SeriesTime"],0,5);
echo "<h1>",$Name,"</h1>\n";
echo "<p>Fixture is normally on ",$Day," at ",$Time,"</p>\n";

echo "<h2>Default fixture invitees:</h2>\n";

$sql="SELECT FirstName, LastName, EmailAddress 
FROM Users, SeriesCandidates
WHERE Seriesid=$Seriesid AND Users.Userid=SeriesCandidates.Userid
ORDER BY LastName";

$result = $conn->query($sql);

echo '<table class="pure-table"><thead><tr><th>Name</th><th>Email</th></tr></thead><tbody>',"\n";

while ($row = $result->fetch_assoc()) {
    $EmailAddress=str_replace("@","<wbr>@",$row["EmailAddress"]);
    $EmailAddress=str_replace("-","&#8209",$EmailAddress);
    echo "<tr><td>{$row["FirstName"]} {$row["LastName"]}</td><td>{$EmailAddress}</td></tr>\n";
}

$conn->close();
?>

</tbody>
</table>

</body>
</html>