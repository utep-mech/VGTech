<?php
if($_POST['send']['Type']=='ListFiles') {pa(scandir("$HOME/COMMON/Playground")); return; }

$idtmp = uniqid(); $idmain = $_POST['send']['outputid']; $LoadPHP= $_POST['send']['LoadPHP']; 
echo "<button onclick=\"dimag({'outputid':'$idtmp','LoadPHP':'$LoadPHP','Type':'ListFiles'});\">List</button>"; 
echo "<button onclick=\"dimag({'outputid':'$idtmp','LoadPHP':'Playground/Section.php','Type':'InitialLoad'});\">Section</button>"; 


echo "<span id=$idtmp></span>"; 

?>
