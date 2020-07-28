<?php 

session_start();

if(!isset($_SESSION["logged_in"])) {
  $_SESSION['message']= "<b>You must login first</b>";
  header("location: error.php");    // session_destroy()
}

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

</head>
<body>

<script type="text/javascript">
$(document).ready(function(){

  // Trigger random function
  $("#play").click(function(){
    $.post("treasures.class.php",
           { 
            fn: "play"
           },
           function(data) 
           {
              loadItems(data);  // distribute data on a page
           }
           ,
           "json"
         );
  });
    
  // Win money explicitly ("Win Money" button)
  $("#win_money").click(function(){
    $.post("treasures.class.php",
            { 
              fn: "win_money"
            }, 
            function(data) 
            {
              loadItems(data);
            },
            "json"
          ); 
  });
 
  // Win points explicitly ("Win Points" button)
  $("#win_points").click(function(){
    $.post("treasures.class.php",
            {
              fn: "win_points"
            },
            function(data) 
            {
              loadItems(data);
            },
            "json"
         );
  });

  // Win items explicitly ("Win Items" button)
  $("#win_items").click(function(){
    $.post("treasures.class.php",
            { 
              fn: "win_items"
            },
            function(data) 
            {
             loadItems(data);
            },
            "json"
          );

  });

  // Debug response
  $("#get_items").click(function(){
    $.post("treasures.class.php",
            { 
              fn: "get_items" 
            },
            function(data) 
            {
               alert(data) 
            }
         );
  });

  // Close button
  $("#close").click(function(){
     $("#empModal").modal("hide");
  });

  // Return response in pop-up window
  function response_in_modal(response) {
     $(".modal-title").html(response);
     $("#empModal").modal("show");
  }

  // Update the page with treasures ("Load Items" button)
  $("#load_items").click(function(){
    getItems();
  });
    
  // Get page items
  function getItems(){
    $.post("treasures.class.php",
            { 
              fn: "get_items" 
            },
            function(data) 
            {
              loadItems(data);
            },
            "json"
          );
  }

  // Distribute response data on a page
  function loadItems(data) {

    // Write response to the console
    $.each(data,function(i,v){ console.log(i + " " + v); });

    if(data.win == "items") { // Handle items
                        
      // Prompt for sending or canceling
      $("#item_win").html(data.response);
      $("#promptModal").modal("show");

      // Send button click
      $("#send_item").click(function(){
        // respond in another modal
        $("#promptModal").modal("hide");
        response_in_modal("Item sent...");
        // post request to the post office here...
      });

      // Item canceled, remove item from the won items
      $("#cancel_item").unbind().click(function(){
        $("#promptModal").modal("hide");
        response_in_modal("Item canceled...");
          $.post("treasures.class.php",
            { fn: "cancel_item"},
            function(data) { 
              //alert(data);
              $("#items").text(data);
            },"json");
      });
    }
    else if(data.response) {  // regular modal
      response_in_modal(data.response);
    }

    // "Treasures" fields

    // Money 
    $("#money").text(data.money + "$");
    $("#money").attr("value",data.money); // setting attribute here
                                          // to 

    // Points
    $("#points").text(data.points);

    // Items
    if(!data.items) {
      data.items = "";  // otherwise NULL is displayed
    }
    $("#items").text(data.items);
    $("#available_items").text(data.available_items);

    // Display real amount in the convert field
    $("#money_convert").val(data.money);
    showHint(data.money);   // calculate points

  }

  // Run when page is loaded
  getItems();


  // Convert money 
  $("#convert").click(function(){
    var money = $("#money_convert").val();    // get money value
    var points = $("#points_converted").html(); // get points
    $.post("treasures.class.php", 
            { 
              fn: "convert_money",
              money: money,
              points: points
            }
          );
         
    getItems();
  });


  // Log Out
  $("#log_out").click(function(){
    window.location="/logout.php";
  });

});
</script>

<?php ; # var_dump($_SESSION); ?>
<div class="container">

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

  <!-- Convert money on the fly -->
  <div>
    <h1><u>Convert Money</u></h1>
    <label for="money_convert">Money:</label>
    <input type="text" id="money_convert" size="4" name="money" max="0" value="0" onkeyup="showHint(this.value)">
    <span id="points_convert"><label for="points_convert">Points:</label></span>
    <span id="points_converted"> </span>
    <button id="convert">Convert</button>
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
