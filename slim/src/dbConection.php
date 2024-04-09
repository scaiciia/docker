<?php

//CONEXION A DB

function getConnection() {
    $dbhost = "db";
    $dbname = "seminariophp";
    $dbuser = "seminariophp";
    $dbpass = "seminariophp";
    $connection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $connection;
}