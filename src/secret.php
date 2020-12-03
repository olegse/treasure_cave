<?php
define("MYSQL_HOST","mariadb");     # container name, see docker-compose.yml
define("MYSQL_USER","admin");
define("MYSQL_DATABASE","treasure_cave"); 
define("MYSQL_USER", "admin");
define("MYSQL_PASSWORD","admin");
define("ADMIN_USER","admin");         # application admin user
define("ADMIN_PASS","admin");
define("T_USERS","users");            // "T" for table
define("T_USER_ITEMS","user_items"); 
define("T_TREASURES","treasures"); 
define("MONEY_GAME_MAX",1000);
define("MONEY_MAX",100);
define("POINT_MAX",200);
define("BANK_URL","http://nginx/raiffeisenbank.php");
define("MONEY_PER_PACK",50);
define("RATIO",3);
