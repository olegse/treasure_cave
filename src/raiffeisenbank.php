<?php


//var_dump($_POST);
if(isset($_POST['user']) && 
    isset($_POST['money'])) 

  echo "'$_POST[money]' were transferred to the '$_POST[user]'</br>";
