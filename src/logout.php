<?php
session_start();

echo "User $_SESSION[user] logged out. Redirecting to login page...";
session_destroy(); 

var_dump($_SESSION);
if(!$_SESSION) echo "Session variables emptied\n";
?>

<script type="text/javascript">setTimeout(function(){window.location="/index.php";}, 2000);</script>
