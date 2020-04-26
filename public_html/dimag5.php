<?php
$BodyData = $_POST['opt']['bodymaindata']; $PostData= $_POST['send']; 
$logged = $BodyData['logged'];

if(isset($_POST['opt']['bodymaindata']['folder'])) $Folder=$_POST['opt']['bodymaindata']['folder']; else $Folder='.'; 

if(!file_exists("setup.php")) {
  echo "setup_default.php to setup.php"; return;
} else require_once("setup.php"); //Load the setup
if(isset($BodyData['folder'])) $DATA = $DATA . '/' . $BodyData['folder']; 

if(file_exists("$HOME/setup.json")) $setup=json_decode((file_get_contents("$HOME/setup.json")),true);
$DATA = isset($setup['DATA']) ? $setup['DATA'] : $DATA; 
$urldb = isset($setup['urldb']) ? $setup['urldb'] : 'mongodb://localhost:27017';

$uHomeDir = "$DATA/USERDATA"; 
$admin=0; $priv="guest"; $group='guest'; $userid='guest'; 
if($logged) { 
  $userid=$_POST['opt']['bodymaindata']['userid']; $group = $BodyData['group']; 
  if(in_array($group,array('admin','superadmin'))) $admin=1; 
  if(!(isset($_POST['opt']['bodymaindata']['student']) && $_POST['opt']['bodymaindata']['student']))  $admin=0; 
  $uMonDir = "$uHomeDir/Monitor/$userid"; if(!is_dir($uMonDir)) mkdir($uMonDir,0777,true); 
  if($monitor) { $monData[time()]=$_POST['opt']; $file = "$uMonDir/".date('Y-m-d').'.json'; 
     $str = json_encode($monData); 
     if(file_exists($file)) file_put_contents($file, ",\n $str ", FILE_APPEND); 
     else file_put_contents($file, "$str", FILE_APPEND); 
  }
}
$uDATA = "$uHomeDir/$userid"; if(!is_dir($uDATA) && mkdir($uDATA,0777,true)) echo "Dir $uDATA created!"; 
//------------DB------
$url= ($urldb=='VGTech')?"mongodb://test:test#1234@56.155.555.555:27017/admin": 'mongodb://localhost:27017';

//------------------
require_once("$COMMON/CLASSES/IO.php");
require_once("$COMMON/Main/Login.php"); 
require_once("$COMMON/CLASSES/Setup.php");
//require_once("$COMMON/CLASSES/IOdb.php");
require_once("$COMMON/CLASSES/ParameterLists.php");
require_once("$COMMON/CLASSES/Defaults.php");
//------------------------------------
if(isset($_POST['send']['Save'])) { \IO\SavePOST(); return; }
if(isset($_POST['send']['UpdateJSON'])) { \IO\UpdateJSON($_POST['send']['UpdateJSON']); return; }
if(isset($_POST['send']['EditFile'])) { $u = new \IO\IO($_POST['send']['EditFile'], $O); $u->Edit(); return; } 
//------------------------------------
if(isset($_POST['send']['PostedValue']) && $_POST['send']['PostedValue'] ) { \IO\pa($_POST); return; }

$LoadPHP = $_POST['send']['LoadPHP'];  $outputid = $_POST['send']['outputid'];

if(isset($_POST['send']['LoadPHP'])) { 
   $f="$COMMON/Main/".$_POST['send']['LoadPHP']; $PathInfo= pathinfo($f); $ext=$PathInfo['extension']; 
   if(!file_exists($f)) {$u = new \IO\IO($f); $u->Edit(); unset($u); return; }
   if(isset($_POST['send']['Edit']) && $_POST['send']['Edit']) { $u = new \IO\IO($f); $u->Edit(); unset($u); return; }
   if(in_array($ext,array('php','PHP'))) {require_once("$f"); return; }
  return; 
}

//------------------------------------
/*
$DIR="$DATA/SOFTWARES";  if(!is_dir($DIR)) mkdir("$DIR",0777); 
$O=array('LoadPHP'=>$LoadPHP, 'toggle'=>'-', 'outputid'=>$outputid,'admin'=>$admin,'DIR'=>$DIR); 
$Setup = new \IO\Setup($O);
*/

$O=array('outputid'=>$outputid,'admin'=>$admin); 
if(isset($_POST['send']['editor'])) $O['editor']=$_POST['send']['editor'];

 if(isset($_POST['send']['LoadFile']) && !\IO\CheckPOST('Level','Level0') ) {  
   $f=$_POST['send']['LoadFile']; if(!file_exists($f)) $f="$DIR/".$_POST['send']['LoadFile'];
   $u = new \IO\IO($f, $O); if($u->fext=='xml') $u->xml(); else  $u->html(); 
   return; 
 }
 
 //------------------------------------


?>
 
