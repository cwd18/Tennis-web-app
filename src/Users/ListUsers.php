<?php
// List users with a link to edits
require_once('ConnectDB.php');
$conn = ConnectDB();
$sql="SELECT Userid, FirstName, LastName FROM Users ORDER BY LastName;";
$result = $conn->query($sql);
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

<div class="pure-menu pure-menu-horizontal">
    <a href="Home.html" class="pure-menu-heading pure-menu-link">Actions</a>
    <ul class="pure-menu-list">
        <li class="pure-menu-item">
            <a href="AddUser.html" class="pure-menu-link">Add person</a>
        </li>
    </ul>
</div>

<?php
while ($row = $result->fetch_assoc()) {
    $Userid=$row['Userid'];
    $FirstName=$row['FirstName'];
    $LastName=$row['LastName'];
    echo "<p><a href=\"EditUser.php?Userid=$Userid\">$FirstName $LastName</a></p>\n";
}
$conn->close();
?>

</body>
</html>