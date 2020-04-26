<?php
if(!$admin) return;  
$file="$DATA/Menu.json"; if (!is_dir($DATA)) { mkdir($DATA, 0777, true); }
$idtmp = uniqid(); 
if(!file_exists($file)) {  $contents = '{"COURSES":{"Name":"COURSES","Allowed":"Admin"},"Info":{"Name":"Info","Allowed":"All"}}'; 
 file_put_contents("$file", $contents); 
} else $contents = file_get_contents("$file"); 

$savebuttondisplay='none'; 
if(isset($_POST['send']['Edit'])) { $savebuttondisplay='inline'; echo "<br/><textarea id=MenuTAID>$contents</textarea><br/>";}
if(isset($_POST['send']['Save'])) {file_put_contents("$file", $_POST['send']['val']); echo "<p/>Saved successfully"; return;}

echo "<button onclick=\"dimag({'outputid':'middle','LoadPHP':'Menu.php','Edit':'TA'});\">Edit</button>";
echo "<button id='Menu-SaveB' style='display:$savebuttondisplay' onclick=\"dimag({'outputid':'$idtmp','LoadPHP':'Menu.php','Save':'TAid','GetValID':'MenuTAID'})\">Save</button>";
echo "<div id=$idtmp></div>"; 
?> 
