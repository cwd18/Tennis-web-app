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

<a href="Home.html">Home  </a>
<a href="AddUser.html">Add person  </a>
<a href="DeleteUser.php">Remove person </a>
<br><br>

<table class="pure-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>

<?php
$servername = "localhost";
$username = "tennisapp";
$password = "Tennis=LT28";
$dbname = "Tennis";
$conn = new mysqli($servername, $username, $password, $dbname);

$sql="SELECT FirstName, LastName, EmailAddress 
FROM Users
ORDER BY LastName";

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