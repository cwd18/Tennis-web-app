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

<?php
// Lists the specified fixture series, showing any default invitees and fixtures
// Includes a menu of commands to manage a series 
$Seriesid=$_GET["Seriesid"];

// Menu of commands...
echo "<li class=\"pure-menu-item\">
<a href=\"AddFixture.php?Seriesid=$Seriesid\" class=\"pure-menu-link\">Add fixture</a></li>\n";
echo "<li class=\"pure-menu-item\">
<a href=\"AddSeriesCandidates.php?Seriesid=$Seriesid\" class=\"pure-menu-link\">Add people</a></li>\n";
echo "<li class=\"pure-menu-item\">
<a href=\"RemSeriesCandidates.php?Seriesid=$Seriesid\" class=\"pure-menu-link\">Remove people</a></li>\n";
echo "<li class=\"pure-menu-item\">
<a href=\"RemSeries.php?Seriesid=$Seriesid\" class=\"pure-menu-link\">Remove series</a></li>\n";
echo "</ul>\n</div>\n";

$DayName=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");

require_once('ConnectDB.php');
$conn = ConnectDB();

// Display basic series data...
$sql="SELECT Seriesid, SeriesName, SeriesWeekday, SeriesTime
FROM FixtureSeries
WHERE Seriesid=$Seriesid;";

$result = $conn->query($sql);

$row = $result->fetch_assoc();
$Name=$row["SeriesName"];
$Day=$DayName[$row["SeriesWeekday"]];
$Time=substr($row["SeriesTime"],0,5);
echo "<h2>",$Name,"</h2>\n";
echo "<p>Fixture is normally on ",$Day," at ",$Time,"</p>\n";


// List default attendees...
$sql="SELECT FirstName, LastName, EmailAddress 
FROM Users, SeriesCandidates
WHERE Seriesid=$Seriesid AND Users.Userid=SeriesCandidates.Userid
ORDER BY LastName;";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "<p><b>Default fixture invitees:</b></p>\n";
    echo '<table class="pure-table"><thead><tr><th>Name</th><th>Email</th></tr></thead><tbody>',"\n";

    while ($row = $result->fetch_assoc()) {
        $EmailAddress=str_replace("@","<wbr>@",$row["EmailAddress"]);
        $EmailAddress=str_replace("-","&#8209",$EmailAddress);
        echo "<tr><td>{$row["FirstName"]} {$row["LastName"]}</td><td>{$EmailAddress}</td></tr>\n";
    }
    echo "</tbody></table>\n";
}
else {
    echo "<p><b>No fixture invitees</b></p>\n";
}

// List fixtures for this series...
$sql="SELECT Fixtureid, FixtureDate, FixtureTime FROM Fixtures 
WHERE Seriesid=$Seriesid ORDER BY FixtureDate DESC;";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "<p><b>Fixtures (most recent first):</b></p>\n";
    echo '<table class="pure-table"><thead><tr><th>Date</th><th>Time</th></tr></thead><tbody>',"\n";

    while ($row = $result->fetch_assoc()) {
        $Fixtureid=$row["Fixtureid"];
        $FixtureDate=$row["FixtureDate"];
        $d=strtotime($FixtureDate);
        $dstr=date("l jS \of F Y",$d);
        $FixtureTime=substr($row["FixtureTime"],0,5);
        echo "<tr><td><a href=\"Fixture.php?Fixtureid=$Fixtureid\">$dstr</a></td>
        <td>$FixtureTime</td></tr>\n";
    }
    echo "</tbody></table>\n";
}
else {
    echo "<p><b>No fixtures</b></p>\n";
}


$conn->close();
?>

</body>
</html>