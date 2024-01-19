<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Testing</h2>


<?php
echo "Hello PDO world!<br>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=Tennis", 'tennisapp', 'Tennis=LT28'); 
    echo "It worked!<br>\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>

</body>
</html>