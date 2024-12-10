<?php
include('config.php');

$base_url = "http://localhost/duckiemon2/";
$conn = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database']);
$conn->set_charset("utf8");

date_default_timezone_set("Europe/Amsterdam");

?>