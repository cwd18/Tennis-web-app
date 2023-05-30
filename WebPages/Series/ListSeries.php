<?php
// List all the series
$DayName=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
require_once('ConnectDB.php');
$conn = ConnectDB();
$sql="SELECT Seriesid, SeriesWeekday, SeriesTime FROM FixtureSeries;";
$result = $conn->query($sql);
$n=0;
while ($row = $result->fetch_assoc()) {
    $Day=$DayName[$row["SeriesWeekday"]];
    $Time=substr($row["SeriesTime"],0,5);
    $Series[$row["Seriesid"]]=$Day." at ".$Time;
}
$SeriesCount=$n;
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

<div class="pure-menu pure-menu-horizontal">
    <a href="Home.html" class="pure-menu-heading pure-menu-link">Series List</a>
    <ul class="pure-menu-list">
        <li class="pure-menu-item">
            <a href="AddSeries.php" class="pure-menu-link">Add Series</a>
        </li>
    </ul>
</div>

<?php
foreach ($Series as $x => $x_value) {
    echo "<p><a href=\"Series.php?Seriesid=$x\">$x_value</a></p>\n";
}
?>

</body>
</html>