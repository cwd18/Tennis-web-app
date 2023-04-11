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

<div class="pure-menu pure-menu-horizontal">
    <a href="ListSeries.php" class="pure-menu-heading pure-menu-link">Series</a>
    <ul class="pure-menu-list">

<?php
$Seriesid=$_GET["Seriesid"];

echo "<li class=\"pure-menu-item\"><a href=\"AddSeriesCandidates.php?Seriesid=$Seriesid\" class=\"pure-menu-link\">Add people</a></li>\n";
echo "<li class=\"pure-menu-item\"><a href=\"RemSeriesCandidates.php?Seriesid=$Seriesid\" class=\"pure-menu-link\">Remove people</a></li>\n";
echo "<li class=\"pure-menu-item\"><a href=\"RemSeries.php?Seriesid=$Seriesid\" class=\"pure-menu-link\">Remove series</a></li>\n";
echo "</ul>\n</div>\n";

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
echo "<h2>",$Name,"</h2>\n";
echo "<p>Fixture is normally on ",$Day," at ",$Time,"</p>\n";

echo "<p><b>Default fixture invitees:</b></p>\n";

$sql="SELECT FirstName, LastName, EmailAddress 
FROM Users, SeriesCandidates
WHERE Seriesid=$Seriesid AND Users.Userid=SeriesCandidates.Userid
ORDER BY LastName;";

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