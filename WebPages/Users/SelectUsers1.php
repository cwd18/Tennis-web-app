<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/grids-responsive-min.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php
foreach($_POST as $x => $x_value) {
    echo "Key=" . $x . ", Value=" . $x_value;
    echo "<br>\n";
}
?>

</body>
</html>