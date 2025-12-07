<?php
$localhost = 'localhost';
$user = 'root';
$pass = '';
$database = 'bluewaves';

$conn = new mysqli($localhost, $user, $pass, $database);

if ($conn->connect_error) { 
    die(''. $conn->connect_error);
}


?>