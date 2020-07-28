<?php
session_start();

echo "User $_SESSION[user] logged out. Redirecting...";
session_destroy(); 

?>

<script type="text/javascript">setTimeout(function(){window.location="/index.php";}, 2000);</script>
