<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Log in</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <style>
    body, html {
      height: 100%;
    }
  </style>
</head>
<body>

<div class="container">
    <div class="jumbotron text-center">
      <h1>TREASURE CAVE</h2>
    </div>
    <form class="form-horizontal" method="POST" autocomplete="off" action="/login.php" target="_self">
      <div class="form-group">
        <label class="control-label col-sm-2" for="user">User:</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="user" required placeholder="Username" name="user">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-2" for="password">Password:</label>
        <div class="col-sm-10">          
          <input type="password" class="form-control" required id="password" placeholder="Password" name="pass">
        </div>
      </div>
      <div class="form-group">        
        <div class="col-sm-offset-2 col-sm-10">
          <button type="submit" class="btn btn-default" name="login">Submit</button>
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-2" for="sign_up" style="margin-top: 20px;">
            <a href="/sign_up.html" id="sign_up">Sign Up</a>
        </label>
     </div>
    </form>
  </div>

</body>
</html>
