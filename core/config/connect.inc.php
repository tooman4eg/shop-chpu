<?php
//database connection settings
define('DBMS', 'mysql');                      // database system  
define('DB_HOST', 'localhost');       // database host    


define('DB_USER', 'root');   // username         
define('DB_PASS', '');   // password         
define('DB_NAME', 'shop');       // database name    
define('DB_PRFX', 'ikej_');     // database prefix  


// include table name file
include('core/config/tables.inc.php');
define('ALTERNATEPHP', '1');
?>
