<?php
session_start();

require_once('db.php');

$ratio = $_SESSION['ratio'];
if(isset($_GET['ratio']))    // override default ratio
  $ratio  = $_GET['ratio'];

$amount = $_GET['amount'];

if(!(is_numeric($amount) && is_numeric($ratio)) )
  echo "Error";
else {
    echo $amount * $ratio;
}
