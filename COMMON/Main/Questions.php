<?php 
require_once("$COMMON/common-old.php"); 
require_once("$COMMON/Include.php"); 
require_once("$COMMON/CLASSES/Questions.php"); 

if(!isset($Aid)) $Aid = "Others"; 
$uwDir = "$uDATA/Assessments/$Aid"; if(!is_dir($uwDir)) mkdir($uwDir, 0777, true); 
$QDir = "$DATA/Questions";

$debug = $_POST['send']['debug'];  
$O = array('uwDir'=>$uwDir, 'QDir' => $QDir, 'LoadPHP'=>$LoadPHP, 'admin'=>$admin, 'outputid'=>$outputid);  

//--------------------
if(isset($PostData['LoadQFile'])) { $Q = new \Q\Q($PostData['LoadQFile'],$O); echo $Q->Read_Q2(); return; }
//--------------------

if($_POST['attr']['dataType']=='json' && $debug ) $jstr = json_encode($_POST); 
if($_POST['attr']['dataType']=='html' && $debug ) {print_r($_POST); return; }

if(isset($PostData['QSave'])) {
  $f = $PostData['QSave']; 
  FileIORead($f, $A, $root, "Read");
  $desc = $PostData['val']; 
  $A["Q"]["Description"]["@value"]=$desc; ; 
  FileIO($f, $A, $root, "Write");
  echo "Wrote $f"; 
  return; 
}
foreach($_POST['send']['outputid'] as $k => $v) { 
   $f = \IO\id2file("$DATA/Questions/$v"); 
   $ufile = "$uwDir/".basename($f); 
   $fid = pathinfo($f, PATHINFO_FILENAME); 
   if(!file_exists($ufile)) copy($f,$ufile);
   $Q = new \Q\Q($ufile,$O); 
   $output[$k] = "<script> \$('#B-$fid').css('background-color','#ff0'); </script>". $Q->Read_Q2(); unset($Q);
   //$Q = new \Q\Q($f,$O); $output[$k] = $Q->Read_Q2(); unset($Q); 
   //$Q = new \Q\Q("$DATA/Questions/$v",$O); $output[$k] = $Q->Read_Q2(); unset($Q); 
   // $Q = new \Q\Q("$DATA/Questions/$v",$O); $output[$k] = $Q->Q2html(); unset($Q); 
   //if($_POST['send']['debug']) $output[$k] = json_encode($_POST); 
}

echo json_encode($output);

?>
