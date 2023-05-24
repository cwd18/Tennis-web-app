<?php
// Update fixture series from passed parameters
$Seriesid=$_POST['Seriesid'];
$NewSeriesOwner=$_POST['owner'];
$NewSeriesName=$_POST['sname'];
$NewSeriesWeekday=$_POST['day'];
$NewSeriesTime=$_POST['time'];

require_once('ConnectDB.php');
$conn = ConnectDB();
$sql="SELECT SeriesOwner, SeriesName, SeriesWeekday, SeriesTime FROM FixtureSeries
WHERE Seriesid=$Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$SeriesOwner=$row['SeriesOwner'];
$SeriesName=$row['SeriesName'];
$SeriesWeekday=$row['SeriesWeekday'];
$SeriesTime=substr($row['SeriesTime'],0,5);
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

<p><b>Update fixture series</b></p> 

<?php
// Update if any changes
if ($NewSeriesOwner!=$SeriesOwner or
    $NewSeriesName!=$SeriesName or 
    $NewSeriesWeekday!=$SeriesWeekday or 
    $NewSeriesTime!=$SeriesTime) {
    $sql="UPDATE FixtureSeries 
    SET SeriesOwner='$NewSeriesOwner', SeriesName='$NewSeriesName', 
    SeriesWeekday='$NewSeriesWeekday', SeriesTime='$NewSeriesTime'
    WHERE Seriesid=$Seriesid;";
    $result=$conn->query($sql);
    if ($NewSeriesOwner!=$SeriesOwner) {echo "<p>Owner ID $SeriesOwner -> $NewSeriesOwner</p>\n";}
    if ($NewSeriesName!=$SeriesName) {echo "<p>$SeriesName -> $NewSeriesName</p>\n";}
    if ($NewSeriesWeekday!=$SeriesWeekday) {echo "<p>$SeriesWeekday -> $NewSeriesWeekday</p>\n";}
    if ($NewSeriesTime!=$SeriesTime) {echo "<p>$SeriesTime -> $NewSeriesTime</p>\n";}
} else {
    echo "<p>No changes made</p>\n";
}
$conn->close();
?>

<a class="pure-button pure-button-primary" href="Series.php?Seriesid=<?=$Seriesid?>">Done</a>

</body>
</html>