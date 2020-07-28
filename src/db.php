<?php
// Database functions.

require("secret.php");    // constant definition

class db { 
  public $conn;
  public $debug = false;
  public $host = "mariadb";
  public $user = "root";
  public $pass = "root";
  public $database = DATABASE;
  public $table_users = T_USERS;
  public $table_treasures = T_TREASURES;
  public $items = array('iPhone', 'Nokia', '4K TV', 'Trip on the Boat',
                      'Toy Soldier', 'Puppy', 'Skydiving', 'Spy Drone');
                          

  // Connect to database on initialization
  function __construct() {
    if(!$this->conn = new mysqli(
                       $this->host,
                       $this->user,
                       $this->pass)
      )
      die($mysqli->connect_error);

    $this->use_database(); 
    return $this->conn;
  }

  // Use database will eliminate additional table schema name
  // specification in the next queries. Ensure that database exists,
  // will craete database on the first run.
  function use_database() {
    $result = $this->query("SHOW SCHEMAS LIKE '$this->database'");
    if($result->num_rows == 0)
      $this->create_db();
    $this->query("USE $this->database");
  }

  // Queries wrapper. Allows to dump successful and failed queries.
  // Returns MySQLi Result Object (https://www.php.net/manual/en/class.mysqli-result.php)
  function query($query) {
    $query = preg_replace( '/\s+/', ' ', $query );

    $result = $this->conn->query($query) or
      die( "Failed on: '$query'</br>" . $this->conn->error );

    if ($this->debug)
      echo "'$query'   SUCCESS</br>";

    return $result;
  }

  // Create database and table structure.
  // Tables:
  //  users(id,username,password)  users
  //  treasures(id,user,money,points,items) user treasures
  //  user_items(id,items)  items users can win
  //
  // When new user is added to a system  an entry in 'user_items'
  // table is created with the full set of items which can be won and
  // id that is a primary key of the user. Each time item won it is
  // removed from the 'user_items' table and added to the 'treasures' table
  // of the user. In this way we track won and available items. Once all the
  // items are won there is nothing more available.
  // 
  // Admin user is created by default

  function create_db() { 
    $this->query("DROP DATABASE IF EXISTS $this->database");
    $this->query("CREATE SCHEMA $this->database");
    $this->query("USE $this->database");
    $this->query("CREATE TABLE ".T_USERS." (
                  id INT NOT NULL AUTO_INCREMENT,
                  username VARCHAR(50) NOT NULL UNIQUE,
                  password VARCHAR(255) NOT NULL,
                  PRIMARY KEY(id))"
                );
    $this->query("CREATE TABLE ".T_TREASURES." ( 
                  id INT NOT NULL AUTO_INCREMENT,
                  user VARCHAR(50) NOT NULL UNIQUE,
                  money SMALLINT DEFAULT 0,
                  points SMALLINT DEFAULT 0,
                  items BLOB DEFAULT NULL,
                  PRIMARY KEY(id))"
                );
    $this->query("CREATE TABLE ".T_USER_ITEMS."  (
                  id INT NOT NULL AUTO_INCREMENT,
                  money SMALLINT DEFAULT 0,
                  items BLOB DEFAULT NULL,
                  PRIMARY KEY(id))"
                );

    $this->new_user(ADMIN_USER,ADMIN_PASS);   // admin (default) user
    return true;
  }
                  
  // Return true if no user matches, false otherwise
  private function user_exists($user) {
    $result = $this->query("SELECT * FROM users WHERE username = '$user'");
    if($result->num_rows > 0)
      return true;
    else
      return false;
  }

  // Create an entry in "treasures" and "user_items' tables for the
  // new user (initialize)
  function init_user($user) {
    $this->query("INSERT INTO $this->table_treasures (user) VALUES ('$user')");

    $items= serialize($this->items);    # store in database blob
    $this->query("INSERT INTO user_items (items,money) 
                  VALUES('$items',".MONEY_GAME_MAX.")");

    #$_SESSION["user"] = $user;
    #$_SESSION["user_id"]= $this->query("SELECT LAST_INSERT_ID()",true)->fetch_row()[0];
    #$_SESSION["logged_in"]= true;
  }

  // Admin Part

  // Add new user to 'users'.
  function new_user($user,$password) {
    var_dump(func_get_args());
    if($this->user_exists($user)) {
      echo "User '$user' already exists</br>";
      return false;
    }

    $user = strtolower($user);
    $password = password_hash($password,PASSWORD_DEFAULT);
    $this->query("INSERT INTO $this->table_users
                  (username,password)
                  VALUES ('$user','$password')");

    // Init user tables
    $this->init_user($user);
    return true;
  }

  // Remove user and update database entries
  function delete_user($user_id) {
    $this->query("DELETE FROM ".T_USERS." WHERE id = '$user_id'");
    $this->query("DELETE FROM ".T_USER_ITEMS." WHERE id = '$user_id'");
    $this->query("DELETE FROM ". T_TREASURES." WHERE id = '$user_id'");

    // reset autoincrement value
    $this->query("ALTER TABLE ".T_USERS." AUTO_INCREMENT  $user_id");
    $this->query("ALTER TABLE ".T_USER_ITEMS." AUTO_INCREMENT  $user_id");
    $this->query("ALTER TABLE ".T_TREASURES." AUTO_INCREMENT  $user_id");
  }

  // From admin.php, reset user treasures
  function reset_items($user_id) {
    
    $items= serialize($this->items);    # store in database blob
    $this->debug=false;
    $this->query("UPDATE ".T_USER_ITEMS." SET items = '$items',money = ".MONEY_GAME_MAX."  WHERE id = '$user_id'");
    $this->query("UPDATE ".T_TREASURES." SET money = 0, points = 0, items = NULL WHERE id = '$user_id'");
  }

  // Returnes users money
  private function load_money($user) {
    $this->debug=false;
    $money = $this->query("SELECT money ".
                         "FROM ".T_TREASURES." ".
                         "WHERE user = '$user'");
    return $money->fetch_row()[0];
  }

  // Send money to user account
  public function send_money($user) {

    if(!$this->user_exists($user)) {
      echo "User '$user' doesn't exist</br>";
      return false;
    }

    $money = $this->load_money($user);
    if($money < MONEY_PER_PACK) { // last money
       $money_to_send = $money;
       $money = 0;    // money finished :(
    }
    else
       $money_to_send = MONEY_PER_PACK;
     
    // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, BANK_URL);    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "user=$user&money=$money_to_send");
    curl_setopt($ch, CURLOPT_POST, 1);

    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    else {    // update database on successful send
      if($money != 0)
         $money -= $money_to_send;   // substrcact sent money
      $this->query("UPDATE ".T_TREASURES." SET money = '$money' WHERE user = '$user'");
    }
    curl_close ($ch);
    
    echo "$result";
  }

  // Report connection state (true/false)
  function conn_state() {
    return @$this->conn->ping();
  }

  // Close connection
  function conn_close() {
    $this->conn->close();
  }
  
  // Get client info
  function client_info() {
    return $this->conn->client_info;
  }
}
