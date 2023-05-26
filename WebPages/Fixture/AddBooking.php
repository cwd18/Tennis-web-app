<?php
// Add one or more bookings
$Fixtureid=$_GET['Fixtureid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

// Get Fixture data
$sql="SELECT Fixtures.Seriesid, SeriesName, FirstName, LastName, FixtureDate, FixtureTime
FROM Fixtures, Users, FixtureSeries
WHERE Fixtureid=$Fixtureid 
AND Fixtures.FixtureOwner=Users.Userid AND Fixtures.Seriesid=FixtureSeries.Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$Seriesid=$row['Seriesid'];
$SeriesName=$row['SeriesName'];
$OwnerName=$row['FirstName']." ".$row['LastName'];
$FixtureDate=$row['FixtureDate'];
$FixtureTime=substr($row['FixtureTime'],0,5);
$d=strtotime($FixtureDate);
$dstr=date("l jS \of F Y",$d);

$BookingBase=$FixtureTime;
$BookingRange=2;
if ($BookingBase=='08:30') {
    $BookingBase='07:30';
    $BookingRange=3;
}
for ($n=0; $n<$BookingRange; $n++) {
    $t=strtotime($BookingBase)+$n*3600;
    $tstr[$n]=date("H:i",$t);
}
$tselect[0]=$tstr[0];
$tselect[1]=$tstr[1];
if ($BookingRange==3) {
    $tselect[0]=$tstr[1];
    $tselect[1]=$tstr[2];
}


$sql="SELECT Users.Userid, FirstName, LastName FROM Users, FixtureParticipants
WHERE Fixtureid=$Fixtureid AND Users.Userid=FixtureParticipants.Userid
ORDER BY LastName;";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $ParticipantList[$row['Userid']]=$row['FirstName']." ".$row['LastName'];
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

<h2><?=$dstr?> at <?=$FixtureTime?></h2>
<p>Fixture owner: <?=$OwnerName?></p> 

<form class="pure-form pure-form-stacked" action="AddBooking1.php" method="post">
<fieldset>
<legend>Add booking(s)</legend>
<input type="hidden" name="Fixtureid" value="<?=$Fixtureid?>">



<div class="pure-u-1 pure-u-md-1-1">
<label for="booker">Booker</label>
<select name="booker" id="booker">
<?php
foreach($ParticipantList as $id => $name) {
    echo "<option value=\"$id\">$name</option>\n";
}
?>
</select>

<div class="pure-g">
<div class="pure-u-1-2">
<label for="court1">Court number</label>
<input type="number" id="court1" name="court1" min="1" max="23">
</div>

<div class="pure-u-2-2">
<label for="time1">Court time</label>
<select name="time1" id="time1">
<?php
for ($n=0; $n<$BookingRange; $n++) {
    echo "<option>$tstr[$n]</option>\n";
}
?>
</select>
</div>

<div class="pure-u-1-2">
<input type="number" id="court2" name="court2" min="1" max="23">
</div>

<div class="pure-u-2-2">
<select name="time2" id="time2">
<?php
for ($n=0; $n<$BookingRange; $n++) {
    echo "<option>$tstr[$n]</option>\n";
}
?>
</select>
</div>

</div>

<br>

<script>
document.getElementById("time1").value="<?=$tselect[0]?>"
document.getElementById("time2").value="<?=$tselect[1]?>"

</script>

<button type="submit" class="pure-button pure-button-primary">Add</button>
</fieldset>
</form>

<br>
<a class="pure-button" href="Fixture.php?Fixtureid=<?=$Fixtureid?>">Cancel</a>

</fieldset>
</form>

</body>
</html>