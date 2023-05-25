<?php
// Add court bookings to a fixture
$Fixtureid=$_POST['Fixtureid'];
$Bookerid=$_POST['booker'];
$Court1=$_POST['court1'];
$BookingTime1=$_POST['time1'];
$Court2=$_POST['court2'];
$BookingTime2=$_POST['time2'];


require_once('ConnectDB.php');
$conn = ConnectDB();
$sql="SELECT  FirstName, LastName FROM Users WHERE Userid = $Bookerid;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$BookerName=$row['FirstName']." ".$row['LastName'];

if (!empty($Court1)){
    $sql="INSERT INTO CourtBookings (Fixtureid, Userid, CourtNumber, BookingTime)
    VALUES ($Fixtureid, $Bookerid, $Court1, '$BookingTime1');";
    $result = $conn->query($sql);
}
if (!empty($Court2)){
    $sql="INSERT INTO CourtBookings (Fixtureid, Userid, CourtNumber, BookingTime)
    VALUES ($Fixtureid, $Bookerid, $Court2, '$BookingTime2');";
    $result = $conn->query($sql);
}

$conn->close();
?>

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

<form class="pure-form pure-form-aligned" action="Fixture.php?Fixtureid=<?=$Fixtureid?>" method="post">
<fieldset>
<legend>Add bookings by <?=$BookerName?> to fixture</legend>

<?php
if (!empty($Court1)){
    echo "<p>Court $Court1 at $BookingTime1</p>\n";
}
if (!empty($Court2)){
    echo "<p>Court $Court2 at $BookingTime2</p>\n";
}
?>

<br>
<button type="submit" class="pure-button pure-button-primary">Done</button>

</fieldset>
</form>

</body>
</html>