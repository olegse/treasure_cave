<?php

require("db.php");

$conn = new db();
$user = $_POST['user']; 
$conn->send_money("$user");
