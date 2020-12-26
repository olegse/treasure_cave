<?php 

session_start();

if(!isset($_SESSION["logged_in"])) {
  $_SESSION['message']= "<b>You must login first</b>";
  header("location: error.php");    // session_destroy()
}

var_dump($_SESSION);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Room</title>
  <meta charset="utf-8">

  <!-- CSS -->
  <link href='/custom.css' rel="stylesheet" type="text/css">
  <link href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' rel='stylesheet' type='text/css'>
  <!-- Script -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js' type='text/javascript'></script>
  <script src="room.js" type="text/javascript"></script>

</head>
<body>

<?php ; # var_dump($_SESSION); ?>
<div class="container">

  <!-- Greeting and prices -->
  <div>
    <h1><u><?php echo ucfirst($_SESSION['user']); ?> Treasures</u></h1> </br>
  </div>

  <div id="one">
    <span class="treasure">Money: </span>
    <span class="amount" id="money" value="0"></td>
  </div>
  <div id="two">
    <span class="treasure">Points: </span>
    <span class="amount" id="points" ></td>
  </div>
  <div id="one">
    <span class="treasure">Items: </span>
    <span id="items" ></span>
  </div>
  <div id="two">
    <span class="treasure">Available Items: </span>
    <span id="available_items" ></td>
  </div>

  <!-- Convert money window -->
  <div>
    <h1><u>Convert Money</u></h1>
    <label for="money_convert">Money:</label>
    <input type="text" id="money_convert" size="4" name="money" max="0" value="0" onkeyup="showHint(this.value)">
    <span id="points_convert"><label for="points_convert">Points:</label></span>
    <span id="points_converted"> </span>
    <button d="convert">Convert</button>
  </div>

  <script type="text/javascript">
    
    function showHint(money) {
      if (money.length == 0) {
        $("#points_converted").html("");    // converted points
        return;
      } 

      // Maximum amount of available money
      var max_money = $("#money").attr("value"); // html includes '$'

      // If requested money is higher than maximum money avaiable
      if(parseInt(money) > parseInt(max_money)) {
        // reset amount
        $("#money_convert").val(max_money);
      }
      else {
        $.get("convert.php",
              { 
                amount: money
              }, 
              function(response){   // display converted points here
                $("#points_converted").html(response)
              });
      }
    }
  </script>


  <!-- Buttons -->
  <div class="buttons">
    <button class="action_button" id="play" >Play!!!</button></br>
    <button class="action_button" id="win_money">Win Money</button></br>
    <button class="action_button" id="win_items">Win Items</button></br>
    <button class="action_button" id="win_points">Win Points</button></br>
    <button class="action_button" id="get_items">Get Items (in clear)</button></br>
    <button class="action_button" id="load_items">Load Items</button></br>
    <button class="action_button" id="log_out">Log Out</br>
  </div>
</div>

<!-- Modal for win messages (money and points) -->
<div class="modal fade" id="empModal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
     <div class="modal-header">
       <h4 class="modal-title">Modal Title</h4>
     </div>
     <div class="modal-footer">
       <button type="button" class="btn btn-default" id="close">Close</button>
     </div>
    </div>
  </div>
</div>

<!-- Modal for the items -->
<div class="modal fade" id="promptModal" role="dialog-1">
  <div class="modal-dialog">
    <div class="modal-content">
     <div class="modal-header">
       <h4 class="modal-title" id="item_win">Modal Title</h4>
     </div>
     <div class="modal-footer">
       <button type="button" class="btn btn-default" id="send_item">Send</button>
       <button type="button" class="btn btn-default" id="cancel_item">Cancel</button>
     </div>
    </div>
  </div>
</div>
</body>
</html> 
