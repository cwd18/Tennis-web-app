<?php
function ConnectDB()
{
    $servername = "localhost";
    $username = "tennisapp";
    $password = "Tennis=LT28";
    $dbname = "Tennis";
    $conn = new mysqli($servername, $username, $password, $dbname);
    return $conn;
}
?>