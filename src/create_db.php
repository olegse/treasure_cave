<?php

require('db.php');
$db = new db();
$db->create_db();

echo "Database was recreated";
echo "<script type='text/javascript'>setTimeout(function(){window.location='/admin.php';},3000);</script>";
