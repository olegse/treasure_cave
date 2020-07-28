<?php
session_start();
require("db.php");

#var_dump($_POST);
$db = new db();

$_SESSION['user'] = $_POST['user'];
$_SESSION['pass'] = $_POST['password'];

var_dump($_SESSION);
if($db->new_user($_SESSION['user'],$_SESSION['pass'])) { 
  echo "<p>User was created. Redirecting to a play room...</p>";
  echo "<script type='text/javascript'>setTimeout(function(){window.location='login.php';},3000);</script>";
}
