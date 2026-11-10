<?php
$host="localhost";
$database="database_dims";
$username="root";
$password="";
$object=new mysqli($host, $username, $password, $database);
if ($object->connect_error){
    die();
}
?>