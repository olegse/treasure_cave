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
          { fn: "play"},
            function(data) {

              loadItems(data);
            }
            ,"json");
  });
    
  // Win money explicitly ("Win Money" button)
  $("#win_money").click(function(){
    $.post("treasures.class.php",{ fn: "win_money"}, 
      function(data) {
         //alert(data); });
        loadItems(data);
      }, "json"); 
  });
 
  // Win points explicitly ("Win Points" button)
  $("#win_points").click(function(){
    $.post("treasures.class.php",{ fn: "win_points"},
        function(data) {
          //alert(data); 
          loadItems(data);
        }, "json");
  });

  // Win items explicitly ("Win Items" button)
  $("#win_items").click(function(){
    $.post("treasures.class.php",{ fn: "win_items"},
         function(data) {
           //alert(data); });
           loadItems(data);
        },"json");

  });

  // Debug
  $("#get_items").click(function(){
    $.post("treasures.class.php",{ 
            fn: "get_items" },
            function(data) {
             alert(data) });
  });

  // Update the page with the treasures ("Load Items" button)
  $("#load_items").click(function(){
    getItems();
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

  // Get page items
  function getItems(){
    $.post("treasures.class.php",{ 
            fn: "get_items" },
            function(data) {
             //alert(data) });
              loadItems(data);
            }, "json");
  }

  // Distribute response data on a page
  function loadItems(data) {

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
    $("#money").text(data.money + "$");
    $("#money").attr("value",data.money);

    // Display real amount in the convert field
    $("#money_convert").val(data.money);
    showHint(data.money);

    $("#points").text(data.points);
    if(!data.items) {
      data.items = "";  // otherwise NULL is displayed
    }
    $("#items").text(data.items);
    $("#available_items").text(data.available_items);
  }

  // Run when page is loaded
  getItems();

  //$("#available_items").text("iPhone,Nokia,4K TV,Trip on the Boat,Toy Soldier,Puppy,Skydiving,Spy Drone");

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
        $("#points_converted").html("");
        return;
      } 
      var max_money = $("#money").attr("value");
      if(parseInt(money) > parseInt(max_money)) {
        $("#money_convert").val(max_money);
      }
      else {
        $.get("convert.php",{amount: money}, 
            function(response){
              $("#points_converted").html(response)
              });
      }
    }

    $("#convert").click(function(){
      var money = $("#money_convert").val();
      var points = $("#points_converted").html();
      $.post("treasures.class.php", 
              { fn: "convert_money",
                money: money, points: points},
              function(data) { alert(data); } );
      getItems();
    });
  </script>

  <div class="buttons">
    <button class="action_button" id="play" >Play!!!</button></br>
    <button class="action_button" id="win_money">Win Money</button></br>
    <button class="action_button" id="win_items">Win Items</button></br>
    <button class="action_button" id="win_points">Win Points</button></br>
    <button class="action_button" id="get_items">Get Items (in clear)</button></br>
    <button class="action_button" id="load_items">Load Items</button></br>
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
