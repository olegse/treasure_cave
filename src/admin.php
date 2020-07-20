<?php
session_start();
require_once("secret.php");

$_SESSION['ratio'] = RATIO;

?>

<!DOCTYPE HTML5>
<html>
 <head>
  <!-- Title -->
  <title>admin</title>

  <!-- CSS -->
  <link href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' rel='stylesheet' type='text/css'>
  <!-- Script -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js' type='text/javascript'></script>
 </head>
 <body >
   <!-- Modal -->
   <div class="modal fade" id="empModal" role="dialog">
    <div class="modal-dialog">

     <!-- Modal content-->
     <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">User Info</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="send">Send</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <!-- onclick -->
      </div>
     <!-- modal content -->
     </div>
    <!-- modal dialog -->
    </div>
   <!-- modal fade -->
   </div>

   <br/>
   <table border='1' style='border-collapse: collapse;'>
    <tr>
     <th>ID</th>
     <th>User</th>
     <th>&nbsp;</th>
    </tr>
    <?php
      require_once('db.php');
      $conn = new db();
      $users = $conn->query("SELECT id,username FROM users");
      while($row = $users->fetch_assoc())
      {
        echo "<tr>";
        echo "  <td id='user_id'>$row[id]</td>";
        echo "  <td>$row[username]</td>";
        echo "  <td><button class='delete_user'>Delete</button></td>";
        echo "  <td><button class='reset_items' value='$row[id]' >Reset</button></td>";
        echo "<tr>";
      }
      echo "  <td><button id='create_user'>Create user</button></td>";
    ?>
   </table>
  
  <script>
    $(document).ready(function(){

      function response_in_modal(response) {
          $('.modal-body').html(response);
          $('#empModal').modal('show');
      };


      // Events
      $('#create_user').click(function(){
         $.post("users.php",
            { fn: "create_user"},
            function(data) {
               response_in_modal(data);
            });
       });
        
      $('.reset_items').click(function(){
        var id = $(this).val();
        //alert(data);
        $.post("users.php",
                { fn: "reset_items",
                  user_id: id },
                function(data) {
                  $('.modal-body').html(data);
                  $('#empModal').modal('show');
                  //location.reload();
             });
      });

      $('.delete_user').click(function(){
        var id = $("#user_id").text();
        $.post("users.php",
                { fn: "delete_user",
                  user_id: id },
                function(data) {
                  $('.modal-body').html(data);
                  $('#empModal').modal('show');
                  location.reload();
             });
      });

     $('#ok').click(function(){
        $('.modal-body').html("Sent!");
        $('#empModal').modal('show');
     });   

     $('#clear_session').click(function(){
      $.post("users.php",{ fn: "clear_session" }, function(data){response_in_modal(data);})});
  });
  </script>
  <form action="create_db.php" method="GET" target="_self" />
    <label for="create_db">
      <button id="create_db" type="submit" value="Create">Create database</button>
    </label>
  </form>
  <form action="register.php" method="POST" target="_blank" />
    <label for="user">User</label>
      <input type="text" id="user" name="user">
    <label for="password">Password</label>
      <input type="text" id="password" name="password">
    <input type="submit" value="Create">
  </form>
  <button id="reload" onclick="location.reload()">Reload</button>

  <script>
  function showHint() {
  var amount = document.getElementById("money").value;
  var ratio  = document.getElementById("ratio").value;

  if (amount.length == 0) {
    document.getElementById("points").innerHTML = "";
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("points").innerHTML = this.responseText;
      }
    };
    xmlhttp.open("GET", "convert.php?ratio="  + ratio
                                 + "&amount=" + amount,
                                                true);
    xmlhttp.send();
  }
}
</script>
    

<form action="">
  <label for="money">Money:</label>
  <input type="text" id="money" name="money" value="0" onkeyup="showHint()">
  <label for="points">Points:</label>
  <span id="points"> </span> 
  </br>
  <label for="ratio">Ratio:</label>
  <input type="text" id="ratio" name="ratio" value="<?php echo $_SESSION['ratio']; ?>" onkeyup="showHint()">
</form>

</body>
</html> 
 </body>
</html>
