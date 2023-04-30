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
// Remove the specified fixture by Fixtureid
// The SQL DELETE FROM will fail if the fixture is referenced by any participants or bookings
// Pressing the "Done" button hyperlinks to Series.php

$Fixtureid=$_GET['Fixtureid']; // The Fixtureid to delete

require_once('ConnectDB.php');
$conn = ConnectDB();

$sql="SELECT  Seriesid, FixtureDate FROM Fixtures WHERE Fixtureid = $Fixtureid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$Seriesid=$row['Seriesid'];
$FixtureDate=$row['FixtureDate'];
$d=strtotime($FixtureDate);
$dstr=date("l jS \of F Y",$d);
$sql="DELETE FROM Fixtures WHERE Fixtureid=$Fixtureid;";
try {
$result = $conn->query($sql);
if ($result === TRUE) {
    echo "<p>Removed the fixture on $dstr</p>\n";
  } else {
    echo "<p>Couldn't delete the fixture on $dstr: " , $conn->error, "</p>\n";
  }
} catch (Exception $e) {
    echo "<p>Couldn't delete the fixture on $dstr:<br>" , $e->getMessage(), "</p>\n";
}
  
echo "<br>\n";
echo "<a class=\"pure-button pure-button-primary\" href=\"Series.php?Seriesid=$Seriesid\">Done</a>\n";
?>

</body>
</html>