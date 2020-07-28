<?php
session_start();
require("db.php");

$db = new db();

$_SESSION['user'] = $_POST['user'];
$_SESSION['pass'] = $_POST['password'];

if($db->new_user($_SESSION['user'],$_SESSION['pass'])) { 
  echo "<p>User was created. Redirecting to a play room...</p>";
  echo "<script type='text/javascript'>setTimeout(function(){window.location='login.php';},3000);</script>";
}
