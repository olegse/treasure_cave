<?php

session_start();
require("db.php");

$db   = new db();     // here db layout is triggered first

# Variables in SESSION will be set if user was registering (register.php),
$user =  $_POST['user'];
$pass =  $_POST['pass'];

$result = 
  $db->query("SELECT id,password 
              FROM ".T_USERS." ".
             "WHERE  username = '$user'");

if($result->num_rows == 0)  {
  echo "<p>User was not found</p>";
  echo "<a href='/index.php'>Back to login</a>";
  echo "</br>";
  echo "<a href='/sign_up.html'>Register</a>";
}
else {
  $user_data = $result->fetch_assoc();

  if(password_verify($pass,$user_data['password'])) {

    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['logged_in'] = true;
    $_SESSION['ratio'] = RATIO;   # store ratio

    # continue to the play room
    echo "<script type='text/javascript'>setTimeout(function(){window.location='room.php';},3);</script>";
  }
  else {
    echo "<p>Password is not correct</p>";
    echo "<a href='/index.php'>Back to Login</a>";
  }
}
