<?php

require_once("$COMMON/common-old.php"); 
require_once("$COMMON/Include.php"); 
require_once("$COMMON/CLASSES/Questions.php");
require_once("$COMMON/CLASSES/Assessments.php");





$fname = pathinfo($PostData['RefFile'], PATHINFO_FILENAME); 
$DIR = dirname($PostData['RefFile']); $maxQ = 20;  
$outputid2 = $PostData['id']; $outputid = $PostData['outputid']; 
$AFile = "$DIR/".$PostData['LoadA']; 
$ADir = dirname($AFile);  
$QDir = "$DATA/Questions"; if(!is_dir($QDir)) mkdir($QDir,0777,true);  
$LayoutDir = "$HOME/Layout"; 
$Aid = $fname; if(!isset($Aid)) $Aid = "Others"; 
//\IO\pa($PostData); 
$uwDir = "$uDATA/Assessments/$Aid"; //echo "$DATA : $uDATA : $uwDir : $ADir"; 
if(!is_dir($uwDir)) {if(mkdir($uwDir,0777,true)) {echo "created init dir";} else echo "Error $uwDir!"; } 

$O = array('QDir'=>$QDir, 'uwDir'=>$uwDir, 'TEMP'=>"/tmp", 'LoadPHP'=>$LoadPHP, 'outputid'=>$outputid, 'admin'=>$admin); 


if(isset($PostData['EditQFile'])) { $Q = new \Q\Q($PostData['EditQFile'],$O); echo $Q->Question_EditQ(); return; }
if(isset($PostData['DelFile'])) { 
    $f=$PostData['DelFile']; unlink($f); 
    unlink(dirname($f)."/Soln-".pathinfo($f, PATHINFO_FILENAME).".json"); echo "deleted $f";  
    return; 
}
if(isset($PostData['LoadQFile'])) { $f=$PostData['LoadQFile'];
  if(!file_exists($f) ) { $fid = pathinfo($f, PATHINFO_FILENAME); $dn = pathinfo($f, PATHINFO_DIRNAME); 
     if(isset($PostData['Copy'])) { $df = $PostData['Copy']; $ext = pathinfo($df, PATHINFO_EXTENSION); 
	copy($df, "$dn/$fid.$ext");  
	echo "<br />Copied <br/> $df <br/>to<br/> $dn/$fid.$ext "; 
     } else { 
      if($admin) {
       echo "<br />$f does not exits!<br/> Copy default "; 
       echo "<button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP','Copy':'$LayoutDir/Q.html', 'LoadQFile':'$f'});\">html</button>";
       echo "<button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP','Copy':'$LayoutDir/Q.xml', 'LoadQFile':'$f'});\">xml</button>";
       echo "<button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP','Copy':'$LayoutDir/Q.json', 'LoadQFile':'$f'});\">json</button>";
      } else echo "Error! file doesn't exits";  
     } 
   return; 
  }
  $fn = basename($f); $ufile="$uwDir/$fn"; 
  //if(file_exists($f)) { if(!file_exists($ufile)) copy($f,$ufile); } else $ufile=$f; 
  if(file_exists($f)) { \Q\COPYVK($f,$ufile);  } else $ufile=$f;
  
  $Q = new \Q\Q($ufile,$O); echo $Q->Read_Q2(); 

  if($admin && !isset($PostData['Submit']) ) {
    if(pathinfo($f, PATHINFO_EXTENSION)=='html') {
      echo "<button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP','EditFile':'$f'});\">Edit</button>";
    } else echo "<button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP','EditQFile':'$f'});\">Edit</button>";
    echo "<button onclick=\"dimag({'outputid':'$outputid', 'EditFile':'$f','Editor':'textarea'});\">EditRaw</button>";
    echo "<button onclick=\"dimag({'outputid':'msg-$outputid', 'LoadPHP':'$LoadPHP', 'DelFile':'$uwDir/$fn'});\">Reset</button>";
    echo "<div id='msg-$outputid'><div>"; 
  }
  return; 
}
if(isset($PostData['ListAll']))  { 
  $ALoad = new \Assessments\A($PostData['AFile'],$O); $ALoad->maxQ= $maxQ;  $ALoad->QDir= $PostData['ListAll']; 
  echo $ALoad->ListAll(); 
  return;
}

if(isset($PostData['AddQ']))  { 
  $ALoad = new \Assessments\A($PostData['AFile'],$O); $ALoad->maxQ= $maxQ;  
  if(!($PostData['AddQ']=='New')) $ALoad->id = $PostData['AddQ']; 
  echo $ALoad->AddQ(); if($PostData['AddQ']=='New') echo $ALoad->ListQ(); 
  unset($ALoad);
  return;
}

if(isset($PostData['DelQ']))  { 
  $ALoad = new \Assessments\A($PostData['AFile'],$O); $ALoad->id = $PostData['DelQ']; 
  echo $ALoad->DelQ(); unset($ALoad);
  return;
}

if($admin) echo "<button onclick=\"dimag({'outputid':'$outputid2', 'EditFile':'$AFile'});\">Edit</button><p/>";
$ALoad = new \Assessments\A($AFile,$O); $ALoad->outputid = $outputid2;  echo $ALoad->ListQ(); unset($ALoad);


if(isset($PostData['flag'])) return; 
if($admin) {
  echo "<p /><button onclick=\"dimag({'outputid':'$outputid2', 'LoadPHP':'$LoadPHP', 'AddQ':'New', 'AFile':'$AFile', 'flag':1});\">AddQ</button>";
  echo "<button onclick=\"dimag({'outputid':'$outputid2', 'LoadPHP':'$LoadPHP', 'ListAll':'$QDir', 'AFile':'$AFile', 'flag':1});\">List All</button>";
}


?>
