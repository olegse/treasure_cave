$( document ).ready(function(){
  getItems();
});

// Play the game 
$( document ).on('click','#play',function(){
  $.post("treasures.class.php", { fn: "play" }, function(data) { loadItems(data); } , "json");
});
        
// Win money explicitly ("Win Money" button)
$( document ).on('click','#win_money',function(){
  console.log("Executing win_money() explicitly");
  $.post("treasures.class.php", { fn: "win_money" }, function(data) { loadItems(data); }, "json"); 
});
     
// Win points explicitly ("Win Points" button)
$( document ).on('click','#win_points',function(){
  $.post("treasures.class.php", { fn: "win_points" }, function(data) { loadItems(data); }, "json");
});

// Win items explicitly ("Win Items" button)
$( document ).on('click','#win_items',function(){
  $.post("treasures.class.php", { fn: "win_items" }, function(data) { loadItems(data); }, "json");
});
        
// Debugging response in alert window
//$("#get_items").click(function(){
$( document ).on('click','#get_items',function(){
  $.post("treasures.class.php", {
          fn: "get_items" }, 
          function(data) {
            alert(data);      // can not be displayed by json_encode()
            console.log(data);  // but nicely displayed here 
          }, "json");
});

// Close button
$( document ).on('click','#close',function(){
   $("#empModal").modal("hide");
});

// Return response in pop-up window
function response_in_modal(response) {
 $(".modal-title").html(response);
 $("#empModal").modal("show");
}

// Update the page with treasures ("Load Items" button)
$( document ).on('click','#load_items',function(){
  getItems();
});

// Get page items
function getItems(){
  $.post("treasures.class.php", {
          fn: "get_items" },
          function(data) {
              loadItems(data);    // works with json_encode()
          },
          "json");
}

// Distribute response data on a page
function loadItems(data) {

  // Write response to the console. Note that it iterates json
  console.log(data);
  $.each(data,function(i,v){ console.log(i + ": " + v); });

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
                                        // to user money 

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

// Convert money 
$( document ).on('click','#convert',function(){
  var money = $("#money_convert").val();    // get money value
  var points = $("#points_converted").html(); // get points
  $.post("treasures.class.php", 
          { 
            fn: "convert_money",
            money: money,
            points: points
          }
        );
       
  getItems();     // Update page
});

// "Log Out" button
$( document ).on('click','#log_out',function(){
  window.location="/logout.php";
});
