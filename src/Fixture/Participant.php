<?php
// Display/edit specified fixture participant data 
$Fixtureid=$_GET['Fixtureid'];
$Userid=$_GET['Userid'];

require_once('ConnectDB.php');
$conn = ConnectDB();

// Get Fixture data
$sql="SELECT Fixtures.Seriesid, FirstName, LastName, FixtureDate, FixtureTime
FROM Fixtures, Users, FixtureSeries
WHERE Fixtureid=$Fixtureid 
AND Fixtures.FixtureOwner=Users.Userid AND Fixtures.Seriesid=FixtureSeries.Seriesid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$Seriesid=$row['Seriesid'];
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

// Get participant data
$sql="SELECT Users.Userid, FirstName, LastName, WantsToPlay, IsPlaying FROM Users, FixtureParticipants
WHERE Fixtureid=$Fixtureid AND FixtureParticipants.Userid=$Userid 
AND Users.Userid=FixtureParticipants.Userid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$UserName=$row['FirstName']." ".$row['LastName'];
$IsPlaying=$row['IsPlaying']?"Yes":"No";
switch ($row['WantsToPlay']) {
    case null:
        $WantsToPlay="Unknown";
        break;
    case TRUE:
        $WantsToPlay="Yes";
        break;
    case FALSE:
        $WantsToPlay="No";
        break;
}

// Get user bookings
$sql="SELECT CourtNumber, BookingTime FROM CourtBookings
WHERE Fixtureid=$Fixtureid AND Userid=$Userid
ORDER BY BookingTime;";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $Booking[substr($row['BookingTime'],0,5)]=$row['CourtNumber'];
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>


<h2><?=$UserName?></h2> 
<p>For <?=$dstr?> at <?=$FixtureTime?></p>

<form class="pure-form pure-form-stacked" action="UpdateParticipant.php" method="post">
<fieldset>
<legend>Player Settings</legend>
<input type="hidden" name="Fixtureid" value="<?=$Fixtureid?>">
<input type="hidden" name="Userid" value="<?=$Userid?>">

<div class="pure-g">

<div class="pure-u-1-2">
<label for="WantsToPlay">Wants to play</label>
<select name="WantsToPlay" id="WantsToPlay">
    <option>Unknown</option>
    <option>Yes</option>
    <option>No</option>
</select>
</div>

<div class="pure-u-1-2">
<label for="IsPlaying">Is playing</label>
<select name="IsPlaying" id="IsPlaying">
    <option>Yes</option>
    <option>No</option>
</select>
</div>

</div>

<div class="pure-g">

<div class="pure-u-1-3">
<label for="court1">Court</label>
<input type="number" id="court1" name="court1" min="1" max="26">
</div>

<div class="pure-u-1-3">
<label for="time1">Time</label>
<select name="time1" id="time1">
<?php
for ($n=0; $n<$BookingRange; $n++) {
    echo "<option>$tstr[$n]</option>\n";
}
?>
</select>
</div>

<div class="pure-u-1-3">
<label for="delete1">Delete</label>
<input type="checkbox" id="delete1" name="delete1">
</div>

</div>

<div class="pure-g">

<div class="pure-u-1-3">
<input type="number" id="court2" name="court2" min="1" max="26">
</div>

<div class="pure-u-1-3">
<select name="time2" id="time2">
<?php
for ($n=0; $n<$BookingRange; $n++) {
    echo "<option>$tstr[$n]</option>\n";
}
?>
</select>
</div>

<div class="pure-u-1-3">
<input type="checkbox" id="delete2" name="delete2">
</div>

</div>

<br>
<button type="submit" class="pure-button pure-button-primary">Update</button>

<script>
document.getElementById("WantsToPlay").value="<?=$WantsToPlay?>"
document.getElementById("IsPlaying").value="<?=$IsPlaying?>"
document.getElementById("time1").value="<?=$tselect[0]?>"
document.getElementById("time2").value="<?=$tselect[1]?>"
<?php
if (isset($Booking)) {
    $First=TRUE;
    foreach ($Booking as $Time => $Court) {
        if ($First) {
            echo "document.getElementById(\"time1\").value=\"$Time\"\n";
            echo "document.getElementById(\"court1\").value=\"$Court\"\n";
        } else {
            echo "document.getElementById(\"time2\").value=\"$Time\"\n";
            echo "document.getElementById(\"court2\").value=\"$Court\"\n";
        }
        $First=FALSE;
    }   
}
?>
</script>

</fieldset>
</form>

<br>
<a class="pure-button" href="Fixture.php?Fixtureid=<?=$Fixtureid?>">Cancel</a>

</body>
</html>