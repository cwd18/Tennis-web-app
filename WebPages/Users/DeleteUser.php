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

<form class="pure-form pure-form-aligned" action="DeleteUser1.php" method="post"> 
<fieldset>
<legend>Delete User</legend>        
        
<?php
$servername = "localhost";
$username = "tennisapp";
$password = "Tennis=LT28";
$dbname = "Tennis";
$conn = new mysqli($servername, $username, $password, $dbname);

$sql="SELECT Userid, FirstName, LastName 
FROM Users
ORDER BY LastName;";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $Userid=(string)($row["Userid"]);
    $Name="{$row["FirstName"]} {$row["LastName"]}";
    echo "<div class=\"pure-control-group\">";
    echo "<label class=\"pure-radio\">\n";
    echo "<input type=\"radio\" name=\"UseridToDelete\" value=\"{$Userid}\">\n";
    echo "{$Name}</label></div>\n";
  }

$conn->close();
?>

<button type="submit" class="pure-button pure-button-primary">Delete</button>

</fieldset>
</form>

</body>
</html>