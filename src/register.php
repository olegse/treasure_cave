<?php
session_start();
require("db.php");

$db = new db();   # or here db layout was created

if($db->new_user($_POST['user'],$_POST['password'])) { 
# when redirecting $_POST will be already empty
  $_SESSION['user'] = $_POST['user'];      
  $_SESSION['pass'] = $_POST['password'];
  $_SESSION['logged_in'] = true;
  $_SESSION['ratio'] = RATIO;   # store ratio

  echo "<p>User was created. Redirecting to a play room...</p>";
  echo "<script type='text/javascript'>setTimeout(function(){window.location='room.php';},3000);</script>";
}
else {
  echo "<p>User $_POST[user]  already exist.</p>";
  # sepearate page 
  echo "<p></p>";
  echo "<a href='/index.php'>Back to login</a>";
  echo "</br>";
  echo "<a href='/sign_up.html'>Register</a>";
}
