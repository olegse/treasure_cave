<?php

session_start();
require("db.php");

$db   = new db();
$user = $_POST['user'];
$pass = $_POST['pass'];

$result = 
  $db->query("SELECT id,password 
<<<<<<< HEAD
              FROM ".T_USERS." ".
             "WHERE  username = '$user'");
=======
              FROM   users
              WHERE  username = '$user'");
>>>>>>> cc509e2a5bcd6afe8855f10cbb6323da560bc039

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
    $_SESSION['ratio'] = _RATIO;

    header("location: room.php");
  }
  else {
    echo "<p>Password is not correct</p>";
    echo "<a href='/index.php'>Login</a>";
    echo "<a href='/register.php'>Login</a>";
  }
}
