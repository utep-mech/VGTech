<?php
require_once("$COMMON/CLASSES/IO.php");
require_once("$COMMON/CLASSES/ParameterLists.php");
require_once("$COMMON/CLASSES/Defaults.php");

//pa(scandir("https://drive.google.com/drive/folders/0ByJT8SRs_jQ0WVpLV2h2RUNObEU/")); 
//echo "test"; return; 


if(isset($_POST['send']['Save'])) { \IO\SavePOST(); return; }
//------------------------------------
$DIR="$DATA/RESEARCH";  if(!is_dir($DIR)) mkdir("$DIR",0777); 
$LoadPHP = $_POST['send']['LoadPHP'];  $outputid = $_POST['send']['outputid'];
$f_Layout="$DIR/Research_Layout.xml";  $f_css="$DIR/Research.css"; $fmain="$DIR/Research_main.json";

$fmain="$DIR/Research_main.xml"; //\IO\SaveRaw($fmain,\Defaults\DefaultXML());

// \IO\SaveRaw($f,\Defaults\DefaultXML()); \IO\SaveRaw("$DIR/s.json",\Defaults\DefaultJSON()); \IO\SaveRaw($fh,\Defaults\DefaultHTML());
$O=array('LoadPHP'=>$LoadPHP, 'toggle'=>'-', 'outputid'=>$outputid,'admin'=>$admin); 
 
 if(isset($_POST['send']['EditFile'])) { $u = new \IO\IO($_POST['send']['EditFile'], $O); $u->Edit(); return; }
 if(isset($_POST['send']['LoadFile'])) {
   if(!\IO\CheckPOST('Level','Level0')) {$u = new \IO\IO("$DIR/".$_POST['send']['LoadFile'], $O); $u->html(); return; }
 }
 
 //------------------------------------
 $PL = new \ParameterLists\ParameterLists($fmain); $PL->LoadPHP=$LoadPHP; 
 if(isset($_POST['send']['Level'])) { 
   echo $PL->ArrayToHTML('All'); 
 } else { 
   echo $PL->ArrayToHTML('Level0');
 }

if(!isset($_POST['send']['Level'])) {
   if($admin) echo "<br/>Edit:
    <button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'EditFile':'$fmain'});\">Main</button>
    <button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'EditFile':'$f_Layout'});\">Layout</button>
    <button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'EditFile':'$f_css'});\">CSS</button>
   "; 
   $css=str_replace(array("\n", "\t", "\r"), '', file_get_contents("$f_css") ); 
   $Layout=str_replace(array("\n", "\t", "\r"), '', file_get_contents("$f_Layout") ); 
   echo "<script> 
        if($('#cssResearch').length == 0) {
             \$('head').append('<style id=cssResearch>$css</style>'); 
             \$('table').replaceWith('$Layout'); 
             dimag({'outputid':'tableL', 'LoadPHP':'$LoadPHP', 'LoadFile':'$DIR/$fmain', 'Level':'Level0'});
             dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'LoadFile':'Intro.html', 'Level':'Level1'});
        }
    </script>"; 
}
return; 
$u = new \IO\IO($f_Layout, $O); $u->Edit();
$v = new \IO\IO($f_css="$DIR/Research.css", $O); $v->Edit();
$w = new \IO\IO("$DIR/Research_main.json", $O); $w->Edit();

//echo "$COMMON $DATA $LoadPHP"; 
echo '<br/>.............'; return;

//--------------------------------------------------------

$MainFile= "$DIR/Research.json";   $ThisID="RESEARCH-ID-MAIN";
$default='{"Projects":{"Name":"Projects"},"Meetings":{"Name":"Meetings"},"Agencies":{"Name":"Agencies"}}'; 

if(!file_exists($MainFile)) { file_put_contents($MainFile, $default);  echo "Created '$MainFile' file."; }
$Menu = json_decode(file_get_contents($MainFile), true); 
foreach($Menu as $k=>$v) {$n=$v['Name']; $PHP=basename($LoadPHP,'.php')."/$k.php"; 
   echo "<button onclick=\"dimag({'outputid':'$ThisID','LoadPHP':'$PHP', 'DIR':'$DIR'})\">$n</button>";
}
echo "<span id='$ThisID'></span>"; 

?>
 
