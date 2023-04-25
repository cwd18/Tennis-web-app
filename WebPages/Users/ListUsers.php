<!DOCTYPE html>
<!-- List all users -->
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
        <li class="pure-menu-item">
            <a href="DeleteUser.php" class="pure-menu-link">Remove person</a>
        </li>
    </ul>
</div>

<table class="pure-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>

<?php
require_once('ConnectDB.php');
$conn = ConnectDB();

$sql="SELECT FirstName, LastName, EmailAddress 
FROM Users
ORDER BY LastName;";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $EmailAddress=str_replace("@","<wbr>@",$row["EmailAddress"]);
    $EmailAddress=str_replace("-","&#8209",$EmailAddress);
    echo "<tr><td>{$row["FirstName"]} {$row["LastName"]}</td><td>{$EmailAddress}</td></tr>\n";
}

$conn->close();
?>

</tbody>
</table>

</body>
</html>