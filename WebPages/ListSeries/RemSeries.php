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

<?php

$servername = "localhost";
$username = "tennisapp";
$password = "Tennis=LT28";
$dbname = "Tennis";
$conn = new mysqli($servername, $username, $password, $dbname);

$Seriesid=$_GET['Seriesid'];

echo "<form class=\"pure-form pure-form-aligned\" action=\"ListSeries.php\" method=\"post\">\n";
echo "<fieldset>\n<legend>Remove fixture series</legend>\n"; 

$sql="SELECT  SeriesName FROM FixtureSeries WHERE Seriesid = $Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$SeriesName=$row['SeriesName'];
echo "<p>Removing $SeriesName</p>\n";
$sql="DELETE FROM FixtureSeries WHERE Seriesid=$Seriesid;";
$result = $conn->query($sql);

echo "<br>\n";
?>

<button type="submit" class="pure-button pure-button-primary">Done</button>

</fieldset>
</form>

</body>
</html>