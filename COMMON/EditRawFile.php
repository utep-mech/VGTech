<?php
if(!$admin) return; 
if(($_POST['send']['Save']=='IgnoreEdit')) {echo '<iframe height=400 width=100% src="'.$_SERVER['HTTP_REFERER'].'"></iframe>';  return; }

if(!isset($_POST['send']['LoadPHP'])) {echo 'Edit not allowed'; return; } else {$file = "$COMMON/".$_POST['send']['LoadPHP']; }

if($_POST['send']['Save']=='EditRaw') { $BACKUPDIR="$HOME/BACKUP"; if (!is_dir($BACKUPDIR)) { mkdir($BACKUPDIR, 0777, true); }
  //echo "$COMMON/".$_POST['send']['LoadPHP'] . " $BACKUPDIR/$file".'_'.date("Y-m-d_h:i"); 
  copy("$COMMON/".$_POST['send']['LoadPHP'], "$BACKUPDIR/".basename($_POST['send']['LoadPHP']).'_'.date("Y-m-d_h_i")); 
  if(file_put_contents("$file", $_POST['send']['val'])) { 
    echo ht('Backup:','yellow',1,"$BACKUPDIR"). $_POST['send']['LoadPHP'].'_'.date("Y-m-d_h_i");
  } else echo ht('Error occured','red',1); 
  //echo '<textarea rows=10 cols=100 id=TAEditRaw>'.htmlspecialchars($_POST['send']['val']).'</textarea>';
  exit(', Saved on '.date("F jS, Y h:i:s")); 
}
if(!file_exists($file)) {
  echo "File '$file' does not exists"; return; 
}
$contents = file_get_contents("$file");
echo '<br/><textarea rows=10 cols=100 id=TAEditRaw>'.htmlspecialchars($contents).'</textarea>';
$LoadPHP=$_POST['send']['LoadPHP']; 
echo "<br/><button onclick=\"dimag({'outputid':'EditRawMsg','LoadPHP':'$LoadPHP','Save':'EditRaw','GetValID':'TAEditRaw'})\">Save</button>";
echo "<button onclick=\"dimag({'outputid':'bottom','LoadPHP':'$LoadPHP','Save':'IgnoreEdit'})\">Load</button>";
echo "<span id=EditRawMsg></span>";

exit(", Editing File ".$file);

//function ht($s,$c='yellow',$flag=1,$t='') {if($flag) return "<span title=$t style='background-color:$c;'>$s</span>"; else return $s; }


$file="$DATA/Menu.json"; if (!is_dir($DATA)) { mkdir($DATA, 0777, true); }
if(!file_exists($file)) { 
 $contents = '{"COURSES":{"Name":"COURSES","Allowed":"Admin"},"Info":{"Name":"Info","Allowed":"All"}}'; 
 file_put_contents("$file", $contents); 
} else $contents = file_get_contents("$file"); 

if(isset($_POST['send']['Edit'])) echo "<br/><textarea id=MenuTAID>$contents</textarea><br/>";
if(isset($_POST['send']['Save'])) {file_put_contents("$file", $_POST['send']['val']); echo "<p/>Saved successfully";}

echo "<button onclick=\"dimag({'outputid':'middle','LoadPHP':'Menu.php','Edit':'TA'})\">Edit</button>";


?> 
