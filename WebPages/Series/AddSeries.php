<?php
// Add a new fixture series
require_once('ConnectDB.php');
$conn = ConnectDB();

$sql="SELECT Userid, FirstName, LastName FROM Users ORDER BY FirstName;";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $OwnerList[$row['Userid']]=$row['FirstName']." ".$row['LastName'];
}
$conn->close();
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

<form class="pure-form pure-form-stacked" action="AddSeries1.php" method="post">
    <fieldset>
        <legend>Add Series</legend>        
        <label for="owner">Fixture Owner</label>
        <select name="owner" id="owner">
        <?php
        foreach($OwnerList as $id => $name) {
            echo "<option value=\"$id\">$name</option>\n";
        }
        ?>
        </select>
        <label for="day">Day</label>
        <select name="day" id="day">
            <option value="0">Monday</option>
            <option value="1">Tuesday</option>
            <option value="2">Wednesday</option>
            <option value="3">Thursday</option>
            <option value="4">Friday</option>
            <option value="5">Saturday</option>
            <option value="6">Sunday</option>
            </select>
        <label for="time">Start time</label>
        <select name="time" id="time">
            <option>07:30</option>
            <option>08:30</option>
            <option>09:30</option>
            <option>10:30</option>
            <option>11:30</option>
            <option>12:30</option>
            <option>13:30</option>
            <option>14:30</option>
            <option>15:30</option>
            <option>16:30</option>
            <option>17:30</option>
            <option>18:30</option>
            <option>19:30</option>
        </select>
        <button type="submit" class="pure-button pure-button-primary">Add</button>
    </fieldset>
    </form>

<br>
<a class="pure-button" href="ListSeries.php">Cancel</a>

</body>
</html>