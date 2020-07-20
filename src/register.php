<?php
session_start();
require("db.php");

#var_dump($_POST);
$db = new db();

$user = $_POST['user'];
$password = $_POST['password'];
if($db->new_user($user,$password))
  echo "<p>User was created</p>";
echo "<a href='/index.php'>Back</a>";
echo "<script type='text/javascript'>";
echo "setTimeout(function(){window.location='/index.php';}, 7000) </script>";
