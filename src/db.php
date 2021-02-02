<?php
// Database functions.

require("secret.php");    // constant definition

class db { 
  public $conn;
  public $debug = false;
  public $host = "mariadb";     // ?
  public $user = MYSQL_USER;
  public $pass = MYSQL_PASSWORD;
  public $database = MYSQL_DATABASE;
  public $table_users = T_USERS;
  public $table_treasures = T_TREASURES;
  
  // connect to database on initialization
  function __construct() {
    if(!$this->conn = new mysqli(
                       $this->host,
                       $this->user,
                       $this->pass)
      )
      die($mysqli->connect_error);

    // select default database
    $this->use_database(); 
    return $this->conn;
  }

  // select default database for the queries (use mysql_database).
  // ensure that database exists. will create database on the first run.
  function use_database() {
    // can be empty if wasn't created with docker-compose
    if($this->query("show schemas like '$this->database'")
                                                   ->num_rows)
    {
      if($this->query("show tables in $this->database")
                                                   ->num_rows)
      { # tables are not in place; database was created by docker-compose
        $this->query("use $this->database");
      }
      else
      $this->create_db();         # init database layout
    }                                              
    else
      $this->create_db();         # init database layout
  }

  // queries wrapper. allows to dump successful and failed queries.
  // returns mysqli result object (https://www.php.net/manual/en/class.mysqli-result.php)
  function query($query) {
    $this->debug=0;
    $query = preg_replace( '/\s+/', ' ', $query );

    $result = $this->conn->query($query) or
      die( "failed on: '$query'</br>" . $this->conn->error );

    if ($this->debug)
      echo "'$query'   success</br>";

    return $result;
  }

  // create database and table structure.
  // tables:
  //  users(id,username,password)  users
  //  treasures(id,user,money,points,items) user treasures
  //  user_items(id,items)  items users can win
  //
  // when new user is added to a system  an entry in 'user_items'
  // table is created with the full set of items which can be won and
  // id that is a primary key of the user. each time item won it is
  // removed from the 'user_items' table and added to the 'treasures' table
  // of the user. in this way we track won and available items. once all the
  // items are won there is nothing more available.
  // 
  // admin user is created by default and intended for evaluated
  // operations.

  function create_db() { 
    $this->query("drop database if exists $this->database");
    $this->query("create schema $this->database");
    $this->query("use $this->database");
    $this->query("create table ".T_USERS." (
                  id int not null auto_increment,
                  username varchar(50) not null unique,
                  password varchar(255) not null,
                  primary key(id))"
                );
    $this->query("create table ".T_TREASURES." ( 
                  id int not null auto_increment,
                  user varchar(50) not null unique,
                  money smallint default 0,
                  points smallint default 0,
                  items blob default null,
                  primary key(id))"
                );
    $this->query("create table ".T_USER_ITEMS."  (
                  id int not null auto_increment,
                  money smallint default 0,
                  items blob default null,
                  primary key(id))"
                );

    # add admin user. indendent to access admin page (not
    # really implemented), should be with specific flag I think
    #$user = ADMIN_USER;
    #$password = password_hash(ADMIN_PASS,PASSWORD_DEFAULT); 
    #$this->query("insert into $this->table_users   
              #(username,password)                 
              #values ('$user','$password')");                           
  }

  // return true if no user matches, false otherwise.
  private function user_exists($user) {
    $result = $this->query("select * from users where username = '$user'");
    if($result->num_rows > 0)
      return true;
    else
      return false;
  }

  // Add new user 
  function new_user($user,$password) {

    // Check if user already exist
    if($this->user_exists($user)) {
      return false;
    }

    // Prepare user data for storing in database 
    $user = strtolower($user);  // username in lowercase
    $password = password_hash($password,PASSWORD_DEFAULT);    // hash the password
    $this->query("insert into ".T_USERS."
                  (username,password)
                  values ('$user','$password')");

    // Here we create new entry for the user for the items that 
    // he can win. Money and items are empty.
    $this->query("insert into ".T_TREASURES." (user) values ('$user')");   // no, use constant for table name

    // Create pool of available items for the new user in T_USERS table. Items will be removed from
    // here and added to T_USER_TREASURES. 

    // Initialize default values for available items and money
    $items= serialize(ITEMS);    # serialize items to store in database blob
    $money= MONEY_GAME_MAX;      # maximum amount of money user can win

    $this->query("insert into ".T_TREASURES." (items,money) 
                    values('$items','$money')  ");

    #$_session["user"] = $user;
    #$_session["user_id"]= $this->query("select last_insert_id()",true)->fetch_row()[0];
    #$_session["logged_in"]= true;
    return true;
  }

  // remove user and update database entries
  function delete_user($user_id) {
    $this->query("delete from ".T_USERS." where id = '$user_id'");
    $this->query("delete from ".T_USER_ITEMS." where id = '$user_id'");
    $this->query("delete from ". T_TREASURES." where id = '$user_id'");

    // reset autoincrement value
    $this->query("alter table ".T_USERS." auto_increment  $user_id");
    $this->query("alter table ".T_USER_ITEMS." auto_increment  $user_id");
    $this->query("alter table ".T_TREASURES."  auto_increment  $user_id");
  }

  // from admin.php, reset user treasures
  function reset_items($user_id) {
    
    $items= serialize($this->items);    # store in database blob
    $this->debug=false;
    $this->query("update ".T_USER_ITEMS." set items = '$items',money = ".money_game_max."  where id = '$user_id'");
    $this->query("update ".T_TREASURES." set money = 0, points = 0, items = null where id = '$user_id'");
  }

  // returnes users money
  private function load_money($user) {
    $this->debug=false;
    $money = $this->query("select money ".
                         "from ".T_TREASURES." ".
                         "where user = '$user'");
    return $money->fetch_row()[0];
  }

  // send money to user account
  public function send_money($user) {

    if(!$this->user_exists($user)) {
      echo "user '$user' doesn't exist</br>";
      return false;
    }

    $money = $this->load_money($user);
    if($money < money_per_pack) { // last money
       $money_to_send = $money;
       $money = 0;    // money finished :(
    }
    else
       $money_to_send = money_per_pack;
     
    // generated by curl-to-php: http://incarnate.github.io/curl-to-php/
    $ch = curl_init();

    curl_setopt($ch, curlopt_url, bank_url);    
    curl_setopt($ch, curlopt_returntransfer, 1);
    curl_setopt($ch, curlopt_postfields, "user=$user&money=$money_to_send");
    curl_setopt($ch, curlopt_post, 1);

    $headers = array();
    $headers[] = 'content-type: application/x-www-form-urlencoded';
    curl_setopt($ch, curlopt_httpheader, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'error:' . curl_error($ch);
    }
    else {    // update database on successful send
      if($money != 0)
         $money -= $money_to_send;   // substrcact sent money
      $this->query("update ".T_TREASURES." set money = '$money' where user = '$user'");
    }
    curl_close ($ch);
    
    echo "$result";
  }

  // report connection state (true/false)
  function conn_state() {
    return @$this->conn->ping();
  }

  // close connection
  function conn_close() {
    $this->conn->close();
  }
  
  // get client info
  function client_info() {
    return $this->conn->client_info;
  }
}
