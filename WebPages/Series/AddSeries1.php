<?php
// Add a new fixture series from passed parameters
$owner=$_POST['owner'];
$day=$_POST['day'];
$time=$_POST['time'];
require_once('ConnectDB.php');
$conn = ConnectDB();
$sql="INSERT INTO FixtureSeries (SeriesOwner, SeriesWeekday, SeriesTime)
VALUES ('$owner', $day, '$time');";
$result=$conn->query($sql);
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

<form class="pure-form pure-form-aligned" action="ListSeries.php" method="post"> 
<fieldset>
<legend>Add new series</legend> 

<?php
if ($result === TRUE) {
    echo "Added $sname <br>\n";
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