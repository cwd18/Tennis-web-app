<?php
// Add specified candidates to series
$Seriesid=$_POST["Seriesid"];
require_once('ConnectDB.php');
$conn = ConnectDB();
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

<form class="pure-form pure-form-aligned" action="Series.php?Seriesid=<?php echo $Seriesid;?>" method="post">
<fieldset>
<legend>Add people</legend>

<?php
if (count($_POST)>1) {
foreach($_POST as $x => $x_value) {
    if ($x!="Seriesid") {
        $Userid=$x_value;
        $sql="SELECT  FirstName, LastName FROM Users WHERE Userid = $Userid";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $FirstName=$row['FirstName'];
        $LastName=$row['LastName'];
        echo "<p>Adding $FirstName $LastName</p>\n";
        $sql="INSERT INTO SeriesCandidates (Seriesid, Userid) VALUES ($Seriesid, $Userid);";
        $result = $conn->query($sql);
    }
}
} else {
    echo "<p>No user selected</p>\n";
}
$conn->close();
?>

<br>
<button type="submit" class="pure-button pure-button-primary">Done</button>

</fieldset>
</form>

</body>
</html>