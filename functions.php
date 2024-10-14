<?php
function connectDB($host, $user, $pass, $dbName, $port){
    $db = pg_connect("host=$host port=$port user=$user dbname=$dbName password=$pass");
    pg_set_client_encoding($db, "utf8");
    if(!$db){
        die(mysqli_connect_error());
    }
    return $db;
}