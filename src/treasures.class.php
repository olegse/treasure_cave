<?php
session_start();

require_once("db.php");

class Treasures {
  private $conn;
  private $user;
  private $treasures;
  private $items_empty = false;
  public  $wins = array('win_money','win_points', 'win_items');

  function __construct($user) {
    $this->user = $_SESSION['user'];
    $this->user_id = $_SESSION['user_id'];    // maybe not the right place
    $this->conn = new db();

    // Initialize array of user treasures and additional variables needed to
    // provide a response
    $this->init_user_treasures();
  }

  // Inintialize treasures array. The resulting variables set:
  //    money
  //    points
  //    items             items that user already wone; unserialized 
  //                      on each read, thus can be returned to the page
  //    available_items   available items from the "user_treasures" table
  //    available_money   available money from the "user_treasures" table
  //    win               one of the money, points or items
  //    response          response message
  private function init_user_treasures() {
    $this->treasures = 
        $this->conn->query("
            SELECT money,points,items
            FROM   treasures
            WHERE  user = '$this->user'"
            );
    $this->treasures = $this->treasures->fetch_assoc();
    $this->treasures['items'] = unserialize($this->treasures['items']);
    $this->get_available_items();   // append available items to response
  }
    
  // Returns array of avaible items
  public function get_available_items() {

    $this->conn->debug = false; // debug queries
    $items= $this->conn->query("SELECT items,money ".
                               "FROM ".T_USER_ITEMS.
                              " WHERE id = '$this->user_id'");

    if($items->num_rows != 0) {

      $items = $items->fetch_assoc();
      $this->treasures['available_items'] = unserialize($items['items']);
      if(count($this->treasures['available_items']) == 0) 
      {
        $this->items_empty = true;          // D
        $this->treasures['available_items'] = "You won all the items!!!";
      }
      // also here, how to deliver response properly?
      $this->treasures['available_money'] = $items['money'];
    }
  }
    
  // Convert user money to points
  public function convert_money($money,$points) {
    $money = $this->treasures['money'] - $money;
    $points = $this->treasures['points'] + $points;
    $this->conn->debug=true;  // debug
    $this->conn->query("UPDATE ".T_TREASURES." ".
                       "SET   money  = $money,
                              points = $points 
                        WHERE user  ='$this->user'"
                      );
    echo json_encode($this->treasures);
  }
    
  // Return json for user treasures
  public function get_items() {
    echo json_encode($this->treasures);
  }

  // User won money
  public function win_money() {

    $this->treasures['win']= 'money';
    // No money available
    if($this->treasures['available_money'] == 0) {
      $this->treasures['response'] = "You won all the money!!!";
      echo json_encode($this->treasures);
      return;
    }

    // Not enough money to full fill the MONEY_MAX range
    if(MONEY_MAX > $this->treasures['available_money']) 
      $win = rand(0,$this->treasures['available_money']);
    else
      $win = rand(0,MONEY_MAX);

    if($win == 0)
      $return = "Nothing this time, try again ;) !!!";
    else
      $response = "You >>>>>>> won <b>$win $</b> <<<<<<<<< </br>";

    // Update money in database
    $this->treasures['money'] += $win;

    $this->conn->query("UPDATE ".T_TREASURES." ".
                       "SET   money = ".$this->treasures['money']." ".
                       "WHERE user  ='$this->user'"
                      );

    // Update available money in database
    $this->treasures['available_money'] -= $win;

    $this->conn->query("UPDATE ".T_USER_ITEMS." ".
                       "SET   money = ".$this->treasures['available_money']." ".
                       "WHERE id  ='$this->user_id'"
                      );

    $this->treasures['response']=$response;
    echo json_encode($this->treasures);
  }

  // User won points
  public function win_points() {
    
    $this->treasures['win']= 'points';

    $win = rand(0,POINT_MAX);
    $this->treasures['points'] += $win;

    $response = "You won >>>>> $win <<<< points!!!</br>";

    $this->conn->query("UPDATE ".T_TREASURES." ".
                       "SET   points = ".$this->treasures['points']." ".
                       "WHERE user  ='$this->user'"
                      );
    $this->treasures['response']=$response;
    $this->treasures['win']= "points";
    echo json_encode($this->treasures);
  }

  // Remove last element
  public function cancel_item() {
    array_pop($this->treasures['items']);
    echo json_encode($this->treasures['items']);
    $this->treasures['items'] = serialize($this->treasures['items']);
    $this->conn->query("UPDATE ".T_TREASURES." ".
                       "SET items = '".$this->treasures['items']."'
                       WHERE user = '$this->user'");
  }

  // Get random item from the 'user_items' and add it
  // to user 'treasures'
  public function win_items() {

    $this->treasures['win']= 'items';
    if($this->items_empty) {   // no items available
      $this->treasures['response'] = "You won all the items!!!";
      echo json_encode($this->treasures);
      return;
    }

    $available_items = $this->treasures['available_items'];

    if($this->treasures['items'] == '')
        $user_items = array();
    else
    $user_items = $this->treasures['items'];

    // Get random item
    $item_num = rand(0,count($available_items)-1);
    $item = $available_items[$item_num];

    $response .= "You won >>>>>>> <b>".$item."</b> <<<<<<<!!!";
  
    // Pop item from the available_items and push it to user_items
    $user_items = array_merge($user_items,array_splice($available_items,$item_num,1));

    // Return user items in clear list so they can be displayed,
    $this->treasures['items'] = $user_items;

    // but store them in the blob
    $user_items= serialize($user_items);
    $this->conn->query("UPDATE ".T_TREASURES." ".
                       "SET items = '$user_items'
                        WHERE user = '$this->user'"
                      );

    // Display (new) available items in clear 
    $this->treasures['available_items'] = $available_items;

    $available_items= serialize($available_items);

    //Put remained items to the database back
    $this->conn->query("UPDATE user_items ".
                       "SET items = '$available_items'
                        WHERE id = '$this->user_id'"
                      );

    $this->treasures['response']=$response;
    $this->treasures['item_num']=$item_num;
    $this->treasures['win'] = "items";

    echo json_encode($this->treasures);
  }

  public function play() { 
    call_user_func(array(__NAMESPACE__.'\Treasures',
                            $this->wins[rand(0,2)]));
  } 
}

$game = new Treasures($user);

if(isset($_POST['fn'])) {
  switch($_POST['fn'])  {
    case "get_items":
         $game->get_items();
          break;
    case "win_money":
         $game->win_money();
          break;
    case "win_items":
        $game->win_items();
          break;
    case "win_points":
        $game->win_points();
          break;
    case "cancel_item":
        //echo "In cancel_item()</br>";
        $game->cancel_item();
        break;
    case "convert_money":
        $money = $_POST['money'];
        $points = $_POST['points'];
        $game->convert_money($money,$points);
        break;
    case "play":
         $game->play();
  }
}
