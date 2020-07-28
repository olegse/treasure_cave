<?php
session_start();
require_once('db.php');
#print_r($_POST);

$conn = new db();
if(isset($_POST['fn'])) {
  switch($_POST['fn']) {
    case 'create_user':
      $conn->new_user(ADMIN_USER,ADMIN_PASS);
        echo "Admin user was created";
        break;

    case 'reset_items':
      $conn->reset_items($_POST['user_id']);
        echo "User items were reloaded";
        break;
    case 'delete_user':
      $conn->delete_user($_POST['user_id']);
        break;

    case 'clear_session':
      session_destroy();
      echo "Session variables were destroyed.";
        break;
  }
}
