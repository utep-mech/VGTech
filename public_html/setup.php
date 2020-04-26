<?php

$HOME="/var/www/DLF";
$COMMON="$HOME/COMMON";
$DATA="/var/www/DATA";

$monitor = 1; 
 error_reporting(0);
 
 list($RegisterNewUserEnabled) = array(1);
 $DefaultUsersStr='{ 
     "vkumar":{"UserID":"vkumar","Password":"$1$p4SqwFEe$q8zATOjXI7nfBwxmlwU6//","LastName":"Kumar","FirstName":"V.","Privilege":"SuperAdmin","Encrypted":"1"},
     "sunil":{"UserID":"sunil","Password":"Chandi#123","LastName":"Kumar","FirstName":"S.","Privilege":"Admin","Encrypted":"0"},
     "guestone":{"UserID":"guestone","Password":"Chandi#123","LastName":"One","FirstName":"Guest","Encrypted":"0"}
  }'; 

?>

