<?php


$FirstTime = 1; 
if(isset($_POST['opt']['bodymaindata']['FirstTime'])) $FirstTime = $_POST['opt']['bodymaindata']['FirstTime']; 

$subdir="Main"; if(isset($BodyData['subdir'])) $subdir = $BodyData['subdir']; 

$DIR="$DATA/$subdir"; 
if(isset($_POST['send']['ParamterList'])) { $DIR = $DIR."/".$_POST['send']['ParamterList']; }


//----Initial setup (Load Layout, CSS) ------
if($FirstTime) {
  \Setup\LoadCSS("$DIR/CSS.css"); // \Setup\Layout("$DIR/Layout.xml"); 
  echo " <script> 
	  var bd = \$('#bodymain').data(); bd.FirstTime = 0; 
             dimag({'outputid':'tableL', 'LoadPHP':'$LoadPHP', 'LoadFile':'Main.xml', 'Level':'Level0','ParamterList':'.'});
      </script>
    ";
  
 return;
}
//-------------------------------------------
if(!is_dir($DIR)) mkdir("$DIR",0777); 
$O=array('LoadPHP'=>$LoadPHP, 'toggle'=>'-', 'outputid'=>$outputid, 'admin'=>$admin,'DIR'=>$DIR); 

 if(isset($_POST['send']['LoadFile']) && !\IO\CheckPOST('Level','Level0') ) {  
   $f=$_POST['send']['LoadFile']; if(!file_exists($f)) $f="$DIR/".$_POST['send']['LoadFile'];
   $u = new \IO\IO($f, $O); if($u->fext=='xml') $u->xml(); else  $u->html(); 
   return; 
 }
 
 
 //------------------------------------
//-------------tmp------------
//if(isset($_POST['send']['Editxml']) ) {  $xml = new \IO\IO("$DIR/Main.xml", $O); $xml->xml(); return; }
//echo "<p/>Edit: <button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'Editxml':'$DIR/Main.xml'});\">xml</button>";
//-------------tmp------------

$O=array('editor'=>'textarea'); 
$PL = new \ParameterLists\ParameterLists("$DIR/Main.xml"); 

$PL->LoadPHP=$LoadPHP; //$PL->menuid='middle';
\Setup\LoadCSS("$DIR/CSS.css"); 
if(!isset($_POST['send']['Level'])) { echo $PL->ArrayToHTML('Level0'); 
} else {
  echo $PL->ArrayToHTML('All');
  if($admin) echo "<p/>Edit: <button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'EditFile':'$DIR/Main.xml'});\">Main</button>";
  if($group == 'superadmin' && $admin) {
   echo "<button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'EditFile':'$DIR/Layout.xml'});\">Layout</button>";
   echo "<button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'EditFile':'$DIR/CSS.css'});\">CSS</button>";
  }
}

?>
