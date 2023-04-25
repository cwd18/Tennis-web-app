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

<form class="pure-form pure-form-aligned" action="AddSeriesCandidates1.php" method="post"> 
<fieldset>
   
        
<?php
// Select one or more candidates to be added to the specified series
$Seriesid=$_GET["Seriesid"];

require_once('ConnectDB.php');
$conn = ConnectDB();

// Retrieve list of possible candidates to add, which excludes existing candidates
$sql="SELECT Userid, FirstName, LastName 
FROM Users
WHERE Users.Userid NOT IN (SELECT Userid FROM SeriesCandidates WHERE Seriesid=$Seriesid)
ORDER BY LastName;";
$result = $conn->query($sql);

// Create checkbox for each possible candidate
echo "<legend>Select users to add to series</legend>\n";
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

<button type="submit" class="pure-button pure-button-primary">Select</button>

</fieldset>
</form>

</body>
</html>