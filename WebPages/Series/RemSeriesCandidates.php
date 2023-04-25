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

<form class="pure-form pure-form-aligned" action="RemSeriesCandidates1.php" method="post"> 
<fieldset>
   
        
<?php
require_once('ConnectDB.php');
$conn = ConnectDB();

$Seriesid=$_GET["Seriesid"];

$sql="SELECT Users.Userid, FirstName, LastName 
FROM Users, SeriesCandidates
WHERE Seriesid=$Seriesid AND Users.Userid=SeriesCandidates.Userid
ORDER BY LastName;";

$result = $conn->query($sql);

echo "<legend>Select users to remove from series</legend>\n";
echo "<input type=\"hidden\" name=\"Seriesid\" value=\"$Seriesid\">\n";

$n=1;
while ($row = $result->fetch_assoc()) {
    $Userid=(string)($row["Userid"]);
    $Name="{$row["FirstName"]} {$row["LastName"]}";
    echo "<div class=\"pure-control-group\">";
    echo "<label class=\"pure-checkbox\">\n";
    echo "<input type=\"checkbox\" name=\"User_$n\" value=\"$Userid\">\n";
    echo "$Name</label></div>\n";
    $n++;
  }


$conn->close();
?>

<button type="submit" class="pure-button pure-button-primary">Remove</button>

</fieldset>
</form>

</body>
</html>