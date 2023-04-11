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

$Seriesid=$_POST["Seriesid"];

echo "<form class=\"pure-form pure-form-aligned\" action=\"Series.php?Seriesid=$Seriesid\" method=\"post\">\n";
echo "<fieldset>\n<legend>Remove people</legend>\n"; 

foreach($_POST as $x => $x_value) {
    if ($x!="Seriesid") {
        $Userid=$x_value;
        $sql="SELECT  FirstName, LastName FROM Users WHERE Userid = $Userid";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $FirstName=$row['FirstName'];
        $LastName=$row['LastName'];
        echo "<p>Removing $FirstName $LastName</p>\n";
        $sql="DELETE FROM SeriesCandidates WHERE Seriesid=$Seriesid AND Userid=$Userid;";
        $result = $conn->query($sql);
    }
}

echo "<br>\n";
?>

<button type="submit" class="pure-button pure-button-primary">Done</button>

</fieldset>
</form>

</body>
</html>