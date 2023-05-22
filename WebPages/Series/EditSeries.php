<?php
// Edit basic series data
$Seriesid=$_GET["Seriesid"];
require_once('ConnectDB.php');
$conn = ConnectDB();
$sql="SELECT Seriesid, SeriesName, SeriesWeekday, SeriesTime FROM FixtureSeries WHERE Seriesid=$Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$SeriesName=$row['SeriesName'];
$SeriesWeekday=$row['SeriesWeekday'];
$Time=substr($row['SeriesTime'],0,5);
$conn->close();
?>

<!DOCTYPE html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.custom-restricted-width {display: inline-block;}
.pure-form-aligned .pure-control-group label {text-align: left;}
* {margin-left: 2px;}
</style>
</head>
<body>

<form class="pure-form pure-form-stacked" action="UpdateSeries.php" method="post">
<fieldset>
<legend>Edit series <?php echo $Seriesid;?></legend>
<input type="hidden" name="Seriesid" value="<?php echo $Seriesid;?>">
<label for="sname">Series name</label>
<input type="text" name="sname" id="sname" value="<?php echo $SeriesName;?>">
<label for="day">Day</label>
<select name="day" id="day">
<option value="0">Monday</option>
<option value="1">Tuesday</option>
<option value="2">Wednesday</option>
<option value="3">Thursday</option>
<option value="4">Friday</option>
<option value="5">Saturday</option>
<option value="6">Sunday</option>
</select>

<label for="time">Start time</label>
<select name="time" id="time">
<option>07:30</option>
<option>08:30</option>
<option>09:30</option>
<option>10:30</option>
<option>11:30</option>
<option>12:30</option>
<option>13:30</option>
<option>14:30</option>
<option>15:30</option>
<option>16:30</option>
<option>17:30</option>
<option>18:30</option>
<option>19:30</option>
</select>
<br>

<script>
document.getElementById("day").value="<?php echo $SeriesWeekday;?>"
document.getElementById("time").value="<?php echo $Time;?>"
</script>

<button type="submit" class="pure-button pure-button-primary">Update</button>
</fieldset>
</form>

<br>
<a class="pure-button" href="Series.php?Seriesid=<?php echo $Seriesid;?>">Cancel</a>

</fieldset>
</form>

</body>
</html>