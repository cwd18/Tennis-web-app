<!DOCTYPE html>
<!-- List all users from Users Table-->
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
            <a href="AddSeries.html" class="pure-menu-link">Add Series</a>
        </li>
    </ul>
</div>

<table class="pure-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Day</th>
            <th>Time</th>
        </tr>
    </thead>
<tbody>

<?php
$DayName=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
$servername = "localhost";
$username = "tennisapp";
$password = "Tennis=LT28";
$dbname = "Tennis";
$conn = new mysqli($servername, $username, $password, $dbname);

$sql="SELECT Seriesid, SeriesName, SeriesWeekday, SeriesTime 
FROM FixtureSeries";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $Name="<a href=\"Series.php?Seriesid={$row["Seriesid"]}\">{$row["SeriesName"]}</a>";
    $Day=$DayName[$row["SeriesWeekday"]];
    $Time=substr($row["SeriesTime"],0,5);
    echo "<tr><td>{$Name}</td><td>{$Day}</td><td>{$Time}</td></tr>\n";
}

$conn->close();
?>

</tbody>
</table>

</body>
</html>