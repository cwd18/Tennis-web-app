<?php
// Edit basic fixture data
$Fixtureid=$_GET['Fixtureid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

// Get Fixture data
$sql="SELECT Fixtures.Seriesid, SeriesName, FixtureOwner, FirstName, LastName, FixtureDate, FixtureTime
FROM Fixtures, Users, FixtureSeries
WHERE Fixtureid=$Fixtureid 
AND Fixtures.FixtureOwner=Users.Userid AND Fixtures.Seriesid=FixtureSeries.Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$SeriesName=$row['SeriesName'];
$FixtureOwner=$row['FixtureOwner'];
$OwnerName=$row['FirstName']." ".$row['LastName'];
$FixtureDate=$row['FixtureDate'];
$FixtureTime=substr($row['FixtureTime'],0,5);
$d=strtotime($FixtureDate);
$dstr=date("l jS \of F Y",$d);

$sql="SELECT Userid, FirstName, LastName FROM Users;";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $OwnerList[$row['Userid']]=$row['FirstName']." ".$row['LastName'];
}
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

<form class="pure-form pure-form-stacked" action="UpdateFixture.php" method="post">
<fieldset>
<legend>Edit fixture <?=$Fixtureid?> from <?=$SeriesName?></legend>
<input type="hidden" name="Fixtureid" value="<?=$Fixtureid?>">

<label for="owner">Fixture Owner</label>
<select name="owner" id="owner">
<?php
foreach($OwnerList as $id => $name) {
    echo "<option value=\"$id\">$name</option>\n";
}
?>
</select>

<label for="day">Date</label>
<input type="date" name="date" id="date" value=<?=$FixtureDate?>>

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
document.getElementById("owner").value="<?=$FixtureOwner?>"
document.getElementById("time").value="<?=$FixtureTime?>"
</script>

<button type="submit" class="pure-button pure-button-primary">Update</button>
</fieldset>
</form>

<br>
<a class="pure-button" href="Fixture.php?Fixtureid=<?=$Fixtureid?>">Cancel</a>

</fieldset>
</form>

</body>
</html>