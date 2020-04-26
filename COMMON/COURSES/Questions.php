<?php
include("$HOME/COMMON/common-old.php");  $PData = $_POST['send']; 

global $CKEditorRemoveButtons;  
$CKEditorRemoveButtons="'Save,NewPage,Print,Templates,Language,Image,Flash,Uploadcare,gg,About'"; 


$outputid = $PData['outputid']; $idtmp = 'inside-Questions';  
$QInfo=$PData['Question'];  $LoadPHP = $PData['LoadPHP'];  $CourseID= $PData['COURSE'];  
$Aid =  $PData['AID']; $Qid = $QInfo['id']; 
$iattempt = 0; 

$ADir = "$DATA/COURSES/$CourseID/ASSESSMENT/$Aid"; $AInfo=json_decode(file_get_contents("$ADir/AInfo.json"), true); 
$sADir = "$DATA/COURSES/$CourseID/STUDENTS/$uid/$Aid"; if(!is_dir($sADir)) mkdir($sADir,0777, true); 
$QDatabase = "$DATA/COURSES/$CourseID/Questions/English"; if(!is_dir($QDatabase )) mkdir($QDatabase,0777, true); 
if(!file_exists($fileA)) $QDir = $QDatabase;  else $QDir = $ADir; //echo "Using $QDir<p/>"; 

$O = array('TEMP'=>"$TEMP", 'Qid'=>$Qid, 'Aid'=>$Aid, 'Submit'=>1, 'disabled'=>0, 'LoadPHP'=>$LoadPHP, 'CourseID'=>$CourseID, 'outputid'=>$outputid, 'idtmp'=>$idtmp, 'ADir'=>$ADir); 
if($PData['AInfo']['submit']==0) $O['disabled']=1; 
if(isset( $PData['Attempt']) ) $O['Attempt']=$PData['Attempt']; else $O['Attempt']=0; 

if($PData['Submit']=='SubmitQ'){ CheckAnswers($sADir,$Qid, $QInfo, $O, $AInfo);  return;  }
if($PData['Submit']=='SeeSolution'){ SeeSolution("$sADir/$Qid.xml", $O); return;  }
if(isset($PData['EditQ']) ){ Question_EditQ("$Qid.xml", "$QDir", $TEMP, $O); return;  }
if($PData['flag'] == 'AddQ2A'){ Questions_QAddtoAssessment($PData, $O) ;  return; }
if($PData['flag'] == 'DelfromA'){ Questions_QDelFromAssessment($PData, $O) ;  return; }
if($PData['flag'] == 'Duplicate'){ Questions_QDuplicatetoAssessment($PData, $O) ;  return; }

DisplayScript(); 

//---------------------------List Questions in Database/Assessment................
if(in_array($PData['flag'], array('LoadAll' ,'LoadAllDatabase')) ) { Questions_ListAllQ($PData, $ADir, $QDatabase, $O); return; }

if(!file_exists("$sADir/$Qid.xml") || $PData['reset']=='1' || $PData['TryAgain']=='1' )  {  $fileA = "$ADir/$Qid.xml"; $fileD = "$QDatabase/$Qid.xml"; 
    $sAInfo=json_decode(file_get_contents("$sADir/AInfo.json"), true); unset($sAInfo['Questions'][$Qid]); file_put_contents("$sADir/AInfo.json", json_encode($sAInfo) ); 
    if(!file_exists($fileD)) { if(file_exists($fileA)) {copy($fileA, $fileD); echo "Copied $fileA to $fileD"; } else {file_put_contents($fileD,Questions_DefaultQ($Qid, $uid)); echo "created $fileD<p/>"; } }
   Copy_Q2StudentDir("$Qid.xml", $QDir, $sADir, $TEMP, $O); 
}
echo Read_Q("$Qid.xml", $sADir, $TEMP, $O, $_POST['send']['AInfo']); 
echo "<script>MathJax.Hub.Queue(['Typeset',MathJax.Hub]); </script>"; 

if($admin)  { 
  echo "
      <button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'reset':1});\">Reset</button>
      <button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'EditQ':'Edit'});\">Edit</button>";  

} 

echo "<span id='$idtmp'></span>"; 

//--------------------------
function Questions_QDelFromAssessment($PData, $O = "")  { $AInfo = json_decode(file_get_contents($PData['ADir'].'/AInfo.json'),true);  $Qid=$PData['Qid']; 
 $Qstmp = $AInfo['Questions']; unset($AInfo['Questions']); $ktmp = 0; 
 foreach($Qstmp as $k=>$v) {if(!($v == $Qid)) $AInfo['Questions'][$ktmp]=$v;  $ktmp++; }
 file_put_contents($PData['ADir'].'/AInfo.json', json_encode($AInfo)); 
}
//--------------------------
function Questions_QAddtoAssessment($PData, $O = "")  { $AInfo = json_decode(file_get_contents($PData['ADir'].'/AInfo.json'),true);  $Qid=$PData['Qid']; 
 if(!in_array($Qid, $AInfo['Questions']) ) $AInfo['Questions'][] = $Qid; 
 file_put_contents($PData['ADir'].'/AInfo.json', json_encode($AInfo)); 
}
//--------------------------
function Questions_QDuplicatetoAssessment($PData, $O = "")  { $AInfo = json_decode(file_get_contents($PData['ADir'].'/AInfo.json'),true);  
 $Qid=$PData['Qid']; $QDatabase = $PData['QDatabase']; $newQid = uniqid();  echo "copy $QDatabase/$Qid.xml $QDatabase/$newQid.xml"; 
 copy("$QDatabase/$Qid.xml", "$QDatabase/$newQid.xml"); $AInfo['Questions'][] = $newQid; 
 file_put_contents($PData['ADir'].'/AInfo.json', json_encode($AInfo)); 
}
//---------------------------List Questions in Database/Assessment................
function Questions_ListAllQ($PData, $ADir, $QDatabase, $O = "")  { $strAll = ""; 
  $LoadPHP = $O['LoadPHP']; $outputid= $O['outputid'];  $TEMP= $O['TEMP']; $CourseID= $PData['COURSE']; 
 $AInfo = json_decode(file_get_contents("$ADir/AInfo.json"),true); $AName = $AInfo['Name']; 
 if(in_array($PData['flag'], array('LoadAll' ,'LoadAllDatabase')) ) { $O['Submit']=0; $O['PHP_Q']=1; $O['checked']=1;
  if($PData['flag']=='LoadAll') $qfiles = $PData['Questions']; else $qfiles = glob("$QDatabase/?????????????.xml");  
   
  foreach($qfiles as $k=>$v) { $fileA = "$ADir/".basename($v); $fileD = "$QDatabase/".basename($v);  $Qid = basename($v,'.xml'); $QIndex = sprintf("%2d",$k+1); 
   $strM = ""; 
    if(in_array($Qid, $AInfo['Questions'])  && $PData['flag']=='LoadAllDatabase' ) $QName = htext("Q$QIndex"); else $QName = "Q$QIndex"; //Highlight Qs in Assessments
    if (file_exists($fileA) && (sha1_file($fileA) == sha1_file($fileD)) && ($PData['flag']=='LoadAllDatabase') ) { 
       if(filemtime($fileA)>filemtime($fileD) ) $newerfilein='Assessment'; else $newerfile ='Database'; 
       $strM .= htext("File differs, Newer file in $newerfile"); //"FileA created on".date ("F d Y H:i:s.", filemtime($fileA)) . "FileD:".date ("F d Y H:i:s.", filemtime($fileD)); 
   }
   if ($PData['flag']=='LoadAllDatabase' ) { $O['DisplayChoices']='toggle'; 
     if(!in_array($Qid, $AInfo['Questions'])) $strM  .= "Add to <button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'ADir':'$ADir','QDatabase':'$QDatabase', 'Qid':'$Qid', 'flag':'AddQ2A'});\">$AName</button>";  

   // $strM  .= " | Overwrite Assessment";     $strM  .= "Database"; 
      FileIORead("$fileD", $QQ, $root, 'ReadQ');    
      foreach(explode(',',$QQ['Q'][0]['@attributes']['Ch']) as $ik=>$iv) {if(!isset($Keywords["$iv"])) $Keywords["$iv"] = 1; }
      if(isset($PData['FilterBy'])) {  if(!in_array($PData['FilterBy'], explode(',',$QQ['Q'][0]['@attributes']['Ch']) ) ) continue; }

   } 
   if ($PData['flag']=='LoadAll') { 
     $strM  .= "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'ADir':'$ADir','QDatabase':'$QDatabase', 'Qid':'$Qid', 'flag':'DelfromA'});\">Delete </button>";
     $strM  .= "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'ADir':'$ADir','QDatabase':'$QDatabase', 'Qid':'$Qid', 'flag':'Duplicate'});\">Duplicate</button>";
   }
    $strAll .= "$strM<br/>"; 
    if(!file_exists($fileD)) { $strAll .="$QName"; } else  $strAll .=  togglePHP(Read_Q(basename($v), $QDatabase, $TEMP, $O, $PData['AInfo'])."<hr/>", uniqid(),'-','str', "$QName"); 
 }

  if ($PData['flag']=='LoadAllDatabase' ) {   $filterS = "Filter By:"; $ADirtmp = basename($ADir); 
     foreach($Keywords as $ik=>$iv) { $filterS .= "<button class='$k' onclick=\"dimag({'outputid':'$outputid','LoadPHP':'COURSES/Questions.php', 'COURSE':'$CourseID', 'AID':'$ADirtmp', 'flag':'LoadAllDatabase', 'FilterBy':'$ik'}); \">$ik</button> ";  }
  }
  echo "<br/>$filterS <hr/> $strAll"; 
  return;
 }
}
//--------------------------------------------
function Copy_Q2StudentDir($file, $ADir, $sADir, $TEMP='/tmp', $O = "")  {  $Attempt = $O['Attempt']; 
  FileIORead("$ADir/$file", $Q, $root, 'ReadQ'); $Q=$Q['Q'][0]; //echo "$ADir/$file";  pa($Q); 
  if ($Q['@attributes']['PHP'] == 1)  { $Q = PHP_Q($Q, $TEMP); } 
  if ($Q['@attributes']['Random'] == 1 && $Q['@attributes']['Type'] != 1)    shuffle($Q['A']);
  if($Attempt>0)  FileIORead("$sADir/$file", $QQ, $root, 'ReadQ');
 $QQ['Q'][$Attempt]=$Q;
 foreach (array('PHPXML', 'Creator', 'Modifier', 'GradingCriteria') as $k=>$v) unset($QQ['Q'][$Attempt][$v]); 
  // FileIO("", $QQ, $root, "DisplayA2XML"); 
  FileIO("$sADir/$file", $QQ, $root, 'Write'); 
}
//--------------------------
function Question_EditQ($file, $ADir, $TEMP, $O) { global $CKEditorRemoveButtons;  
   $AddAttr = array("Points"=>10, 'Type'=>0); 
   $qfile = "$ADir/$file"; $ignoreKeys = array('UID', 'GQID'); $strE = "";  $class = 'EditQClass'; 
    
   $LoadPHP = $O['LoadPHP']; $CourseID= $O['CourseID']; $outputid= $O['outputid']; $idtmp= $O['idtmp'];  $Qid = $O['Qid']; $Aid = $O['Aid']; 
   FileIORead("$qfile", $QQ, $root, 'ReadQ'); $Q=$QQ['Q'][0]; 
   foreach($AddAttr as $k=>$v) { if(!isset($Q['@attributes'][$k])) $Q['@attributes'][$k] = $v;  }
   $attr = $Q['@attributes']; 
   if($_POST['send']['EditQ']=='Save') {$QData = $_POST['send']['QData']; 
      foreach($Q['@attributes'] as $k=>$v) { if(isset($QData["$class-$k"])) { $Q['@attributes'][$k]=$_POST['send']['QData']["$class-$k"];  }} 
      foreach(array("Description", "Solution") as $i=>$k) if(isset($QData["$class-$k"])) $Q[$k]['@value'] = $QData["$class-$k"]; 
      if(isset($QData["$class-PHPXML"])) $Q['PHPXML'] = $QData["$class-PHPXML"]; 
      foreach($Q['A'] as $k=>$v) {  $Q['A'][$k]['@attributes'] ['status'] = $QData["$class-Astatus-$k"]; $Q['A'][$k]['@value'] = $QData["$class-Avalue-$k"];       }
      $Q['Modifier']['@attributes'] = array('uid'=>$GLOBALS['uid'],'time'=>time()); 
      //pa($Q);
     $QQ['Q'][0] = $Q; FileIO("$qfile", $QQ, $root, 'Write');
  }


  foreach($Q['@attributes'] as $k=>$v) {$size=1; if($k=='Ch') $size=20; if(!in_array($k,$ignoreKeys)) { $strE .= " | $k<input id='$class-$k' size=$size class=$class value='$v'></input>"; }}
  $desc = $Q['Description']['@value']; $soln = $Q['Solution']['@value']; $PHPXML = $Q['PHPXML']; 
  $strE = togglePHP("$strE <br/><textarea id='$class-Description' class=$class>$desc</textarea><script>CKEDITOR.replace('$class-Description',{removeButtons:$CKEditorRemoveButtons});</script>", uniqid(),'-','str');
  $strA = ""; 
  foreach($Q['A'] as $k=>$v) {  $valA = $v['@value']; $status=$v['@attributes'] ['status']; if($status) $checkedA = 'checked'; else $checkedA = ""; 
       $strA .= " | <input id='$class-Astatus-$k' class=$class value=$status type='checkbox' $checkedA />"; 
        $strA .= togglePHP("<textarea id='$class-Avalue-$k' class=$class ondblclick=\"CKEDITOR.replace('$class-Avalue-$k',{removeButtons:$CKEditorRemoveButtons});\" cols=30 rows=1>$valA</textarea>",  'ChoicesA'.uniqid(),'-','str',''); 
   }
  $strE .=  togglePHP("$strA<br/>", uniqid(),'-','str', 'Choices'); 
  $strE .= togglePHP("<br/><textarea id='$class-Solution' class=$class>$soln</textarea><script>CKEDITOR.replace('$class-Solution',{removeButtons:$CKEditorRemoveButtons});</script>", uniqid(),'+','str', 'Solution');
  if($attr['PHP']==1) { echo "<textarea style='display:none' id=$Qid-defaultPHP>".Questions_DefaultPHP()."</textarea>"; 
      $defaultPHP = "<button onclick=\"document.getElementById('$class-PHPXML').value=document.getElementById('$Qid-defaultPHP').value; \">DefaultPHP</button>"; 
      $strE .= togglePHP("<br/><textarea id='$class-PHPXML' class=$class cols=100 rows=20>$PHPXML</textarea><br/>$defaultPHP", uniqid(),'+','str', 'PHPXML');
  }
   
   echo "<span>$strE</span>"; 
    echo "<button onclick=\"Questions_Edit_Save({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'EditQ':'Save','QData':'$class'});\">Save</button>";  

   //pa($Q); 
}
//--------------------------
function Read_Q($file, $D="", $TEMP='/tmp', $O = "", $AInfo="") { $Attempt = $O['Attempt']; $status=$AInfo['status'];  //echo "$D/$file"; pa($O); 
    FileIORead("$D/$file", $QQ, $root, 'ReadQ');   $nAttempt = sizeof($QQ['Q']);  if($nAttempt>1 && !isset($_POST['send']['Attempt']) ) $Attempt=$nAttempt - 1;  
//pa($QQ); 
    $SeenSolution=0; $TryAgain=1; if(isset($QQ['Q'][0]['@attributes']['SeenSolution'])) { $O['disabled']=1; $SeenSolution=1; $TryAgain=0;  $solcolor='#0099ee';}
    $Q=$QQ['Q'][$Attempt]; if ($O['PHP_Q'] == 1)  { $Q = PHP_Q($Q, $TEMP); }
    if(isset($Q['AO'])) {$O['checked']=1; $O['disabled']=1;  }
    $str  = ''; 
    $desc = $Q['Description']['@value'];
    $Type = $Q['@attributes']['Type'];    

    $Time = time();
    $LoadPHP = $O['LoadPHP']; $CourseID= $O['CourseID']; $outputid= $O['outputid']; $idtmp= $O['idtmp'];  $Qid = $O['Qid']; $Aid = $O['Aid']; 
    if ($O['disabled'])         $disabled = 'disabled';
    if ($O['Submit']){ echo "<script>Fun_TrackTime('sTime');</script>"; 
       $strS = "<br/><input class='MC-$Qid' type=button value=Submit onclick=\"QSubmit({id:'$Qid',Aid:'$Aid', Type:'$Type', LoadPHP:'$LoadPHP',CourseID:'$CourseID',outputid:'$outputid', 'Attempt':'$Attempt'})\" $disabled></input>$solved";  
     }
       if($nAttempt>0) { $stmp = ""; 
         for($iattempt = 0; $iattempt <$nAttempt; $iattempt++)  { $atr=$QQ['Q'][$iattempt]['@attributes'];  $color = "";  $hcolor = ""; 
           if($Attempt == $iattempt) {$hcolor  = 'yellow'; $sattempt = sprintf("<b>%s</b>", $iattempt +1); } else            $sattempt = (1+$iattempt); 
           if(isset($atr['Score'])) { if($atr['Score']) {$color='#00ff00'; $TryAgain=0; } else $color='#DDA0DD'; } 
           if($status==2) $color=""; 
           $stmp .= "<button style='background-color:$color' onclick=\"Questions_Load_Attempt({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Attempt':$iattempt});\">$sattempt </button>";  
         }
         $strS .= " | Attempts $stmp | "; 
      }
   if ($O['disabled'] && $TryAgain)     { 
       if($nAttempt<$AInfo['maxattempts']) $strS .= "<button onclick=\"Questions_Load_Attempt({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Attempt':$nAttempt, 'TryAgain':1});\">Try again</button>";  
     }
     if($nAttempt >= $AInfo['showsolution'] && !($AInfo['showsolution'] < 0) && isset($AInfo['showsolution']) ) { 
        $strS .= "<button style='background-color:$solcolor' onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Submit':'SeeSolution'}); \" >Solution </button>";  
     }
    if($nAttempt >= $AInfo['showanswers'] && !($AInfo['showanswers'] < 0) && isset($AInfo['showanswers']) ) { $O['ShowAnswers'] = 1; $O['checked'] = 1; }
    if ($Type == 1) {
        if ($O['checked'] || $O['disabled']) { $value = $Q['A'][0]['@value'];      if($O['ShowAnswers']) $valueAns = $Q['AO'][0]['@value'];   }
        if ($O['Submit']) {
            return "$desc<p/><input class='MC-$Qid' type=text id='FillIn-$Qid' value=$value $disabled>$valueAns</input>$strS $solved $debugstr";
        } else { return "$desc<p/>$strS "; }
    }
    $strA = ""; 
    foreach ($Q['A'] as $j => $w) {
        $checked = '';  if ($O['checked']) {if ($w['@attributes']['status']) $checked = 'checked';    }
        $val = $w['@value'];
        if ($O['ShowAnswers'] && $Q['AO'][$j]['@attributes']['status']) $color = 'green';  else  $color = 'none';
        $strA .= "<tr><td><span style='background-color: $color;'><input type=checkbox class='MC-$Qid' value=$j $checked $disabled></input></span></td><td>$val</td></tr>";
    }
   $str .= "<table border=1>$strA</table>";  
   $subStr = "<p/><u><b>Choices</b></u>$str $strS $debugstr"; 
   if($O['DisplayChoices']=='toggle') $subStr = togglePHP("$subStr", uniqid(),'+','str', '');
    return "$desc $subStr";

}
function PHP_Q($Q, $TEMP) {  $phpxml  = $Q['PHPXML']; $tmpfile = "$TEMP/" . uniqid() . ".php";
    file_put_contents("$tmpfile", '<?php ' . $phpxml . ' ?>');  include("$tmpfile");   unlink("$tmpfile");
    return $Q;
}

//-------------------------[
function Practice($CD,$Aid,$sid,$O='') { 
  $ADir="$CD/$Aid";  if($Aid=='Database') $ADir="$CD/Questions/English"; 
   $SDir="$CD/STUDENTS/$sid/Practice"; if(!is_dir($SDir)) mkdir($SDir, 0777, true); 
   $str1=""; $str2="";  $nC=0; $nT=0; 
   foreach(glob("$SDir/?????????????.xml") as $k=>$v ) {FileIORead($v, $Q, $root, "ReadQ"); $Q=$Q['Q'][0]; $nT++; 
        if($Q['@attributes']['Score']) {$tt= "<span style='background:#0f0;'>Q$k</span>"; $nC++; } else { $tt= "Q$k"; }
       $pinfo=pathinfo($v); $SolvedQ[]= $pinfo['filename'];  $pC=round(100*$nC/$nT); 
       $desc=Display_Q($Q,array('disabled'=>1,'debug'=>0,'checked'=>1, 'fileid'=>$fileid)); 
       $str1 .=   "<button onclick=\"Show({id:'Desc-$k',id2:1,type:'#'})\">$tt</button>"; $str2 .="<div id='Desc-$k' style='display:none;'>($tt) $desc<hr/></div>"; 
    }; 
     echo "$str1 $str2 ($nC/$nT, $pC%)"; 
    foreach(glob("$ADir/?????????????.xml") as $k=>$v ) {$pinfo=pathinfo($v); if(in_array($pinfo['filename'], $SolvedQ, false)) continue; $AllQ[]= $pinfo['filename'];}; 
    shuffle($AllQ); $fileid=$AllQ[0];  $qfile="$ADir/$fileid.xml"; 
   // $qfile='/home/www/DATA-TEST/COURSES/EA2/Questions/English/52f8ab68b55d9.xml';
   //  $qfile='/home/www/DATA-TEST/COURSES/EA2/Questions/English/52795f9e15b76.xml'; 
   $PHPTxt=''; 
   FileIORead($qfile, $Q, $root, "ReadQ");  $Q=$Q['Q'][0]; if($Q['@attributes']['PHP']==1) {$Q=PHP_Q($Q); if($admin) $PHPTxt='PHP';}  
   if($Q['@attributes']['Random']==1 && $Q['@attributes']['Type']!=1 ) shuffle($Q['A']); 
   
   $tmpfile="$SDir/tmpfile.xml"; $QQ['Q'][0]=$Q;  FileIO($tmpfile, $QQ, $root, "Write"); 
   
   echo "<p/>$PHPTxt".Display_Q($Q,array('disabled'=>0,'debug'=>$GLOBALS['debug'],'checked'=>0, 'fileid'=>$fileid, 'Submit'=>1));  
   return; 
}
//--------------------
function SeeSolution($file, $O='') { 
   FileIORead($file, $Q, $root, "ReadQ");  $n=sizeof($Q['Q']);  
   if(!isset($Q['Q'][0]['@attributes']['SeenSolution'])) { $Q['Q'][0]['@attributes']['SeenSolution']=$n; FileIO($file, $Q, $root, "Write"); }
  echo "<hr/>Solution seen after $n attempt: <br/>".$Q['Q'][0]['Solution']['@value']. '<span>'; 
  echo "<script>MathJax.Hub.Queue(['Typeset',MathJax.Hub]); </script>"; 
}
//--------------------
function CreateStudentAInfo($sADir,  $qid, $attempt=0, $Score=0) {
  if(file_exists("$sADir/AInfo.json")) $sAInfo=json_decode(file_get_contents("$sADir/AInfo.json"),true); 
  if(!file_exists("$sADir/AInfo.json"))    { 
    foreach(glob("$sADir/?????????????.xml") as $k=>$v) { FileIORead($v, $Q, $root, "ReadQ"); $qid=basename($v,'.xml'); 
          foreach($Q['Q'] as $kk=>$vv) { $attr=$vv['@attributes'];  if(isset($attr['Score']))   $sAInfo['Questions'][$qid]['Score'][$kk]=$attr['Score']; }
     }
  }
  if($attempt=="0" && isset($sAInfo['Questions'][$qid]['Score'])) { unset($sAInfo['Questions'][$qid]['Score']); }
  $sAInfo['Questions'][$qid]['Score'][$attempt]=$Score; 
  file_put_contents("$sADir/AInfo.json", json_encode($sAInfo)); 
  //echo "$qid, $attempt". json_encode($sAInfo); 
}
//--------------------
function CheckAnswers($sADir,$qid,$QInfo, $O='', $AInfo='') { $qfile="$sADir/$qid.xml";   $Attempt = $O['Attempt']; //echo $qfile; pa($O); 
   $LoadPHP = $O['LoadPHP']; $CourseID= $O['CourseID']; $outputid= $O['outputid']; $idtmp= $O['idtmp'];  $Qid = $O['Qid']; $Aid = $O['Aid']; 
   FileIORead($qfile, $QQ, $root, "ReadQ"); $Q=$QQ['Q'][$Attempt];   
   if(isset($Q['@attributes']['Score'])) {exit('Error: you have already submitted the answer'); return; }; 
   $SubmittedValue = $_POST['send']['Question']; 
   if(!isset($Q['AO'])) { $Q['AO']=$Q['A']; }
    if($SubmittedValue['Type']==1) { 
      $vu=$SubmittedValue['values']; $v1= $Q['AO'][0]['@value']; $v2= $Q['AO'][1]['@value']; $Q['A'][0]['@value']=$vu; $dv1=$v1-$vu; $dv2=$v2-$vu;  if(abs($vu)<1e-6) $vu=1e-6; 
      if(abs($dv1/$vu) <0.03 || abs($dv2/$vu) <0.03) $ans=1; else $ans=0; 
   } else {
     foreach($Q['A'] as $k=>$v) { if(in_array($k,$_POST['send']['Question']['values']))  $Q['A'][$k]['@attributes']['status']=1; else $Q['A'][$k]['@attributes']['status']=0;}  
     $ans=1; foreach($Q['A'] as $k=>$v) { if($Q['A'][$k]['@attributes']['status'] != $Q['AO'][$k]['@attributes']['status']) $ans=0; } 
   }
   if($ans) $Q['@attributes']['Score']=10; else $Q['@attributes']['Score']=0; 
   $Q['@attributes']['sTime']=$QInfo['Time']['sTime']; $Q['@attributes']['eTime']=$QInfo['Time']['eTime'];  
  $QQ['Q'][$Attempt]=$Q;  FileIO($qfile, $QQ, $root, "Write"); //Write Student answers
       //FileIO("", $QQ, $root, "DisplayA2XML"); 
   CreateStudentAInfo($sADir,  $qid, $Attempt, $Q['@attributes']['Score']); 
  //$QQ['Q'][0]['Attempts']['x1']=array('Description'=>$Q['Description']);
   if($ans) echo htext('Correct, keep it up',1,'#00ff00'); else echo htext('Incorrect, good luck next time',1,'#DDA0DD'); 

   if(!$ans) { $nAttempt = sizeof($QQ['Q']);  echo "<p/>"; 
     if($nAttempt>=$AInfo['ShowSolution'] && !($AInfo['ShowSolution'] <0 ) ) echo "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Attempt':$nAttempt, 'Submit':'SeeSolution'}); \" >Solution </button>"; 
    if($nAttempt<$AInfo['MaxAttempts']) echo "<button onclick=\"Questions_Load_Attempt({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Attempt':$nAttempt, 'TryAgain':1});\">Try again</button>";  
   }
   return $ans; 
}
//-------------------
function SubmitAnswers($CD, $sid, $flag='') {$Time=time(); $sTime=$_POST['Info']['Time']; 
   $sADir="$CD/STUDENTS/$sid/".$_POST['Info']['Aid']; $file = $_POST['Info']['id'].'.xml'; 
   $qfile = "$sADir/$file"; 
   if($_POST['Info']['Type']=='reset') { $tmpid = uniqid(); mkdir("$sADir/reset_$tmpid",0777,true); 
       foreach(glob("$sADir/*.xml") as $i=>$v) { 
           $fn=basename($v); rename("$v","$sADir/reset_$tmpid/$fn"); 
	   echo "$fn moved to $sADir/reset_$tmpid/$fn";  
       }
       return; 
   }
   FileIORead("$qfile", $Q, $root, "ReadQ");  $Q=$Q['Q'][0];   if(!isset($Q['AO']))  $Q['AO']=$Q['A'];
   if($_POST['Info']['Type']==1) { 
      $vu=$_POST['Info']['values']; $v1= $Q['AO'][0]['@value']; $v2= $Q['AO'][1]['@value']; $Q['A'][0]['@value']=$vu; $dv1=$v1-$vu; $dv2=$v2-$vu;  
      if(abs($vu)<1e-6) $vu=1e-6; 
      if(abs($dv1/$vu) <0.03 || abs($dv2/$vu) <0.03) $ans=1; else $ans=0; 
   } else {
     $ans=1; 
     foreach($Q['A'] as $k=>$v) { $Q['A'][$k]['@attributes']['status'] =0; if(in_array($k,$_POST['Info']['values'])) $Q['A'][$k]['@attributes']['status'] =1; 
	if($Q['A'][$k]['@attributes']['status'] != $Q['AO'][$k]['@attributes']['status']) $ans=0;
     } 
   } 
   if($ans) $Q['@attributes']['Score']=10; else $Q['@attributes']['Score']=0; 
   $Q['@attributes']['sTime']=$_POST['Info']['sTime']; $Q['@attributes']['eTime']=$_POST['Info']['eTime'];
   $QQ['Q'][0]=$Q;  FileIO($qfile, $QQ, $root, "Write");

  $file="$sADir/QList.xml"; 
  if(!file_exists($file)) {
 	$sQList['Q'][0]['@attributes']=$Q['@attributes']; 
  } else { 
	FileIORead($file, $sQList, $root, "ReadQ"); $n=sizeof($sQList['Q']); 
        foreach($sQList['Q'] as  $k =>$v) $sQ2ID[]=$v['@attributes']['UID'];
	if(!in_array($_POST['Info']['id'], $sQ2ID)) $sQList['Q'][$n]['@attributes']=$Q['@attributes']; 
  }
   FileIO($file, $sQList, $root, "Write");
   if($ans) echo 'Got it!'; else echo 'Best wishes next time';
   return;
}
//-----------------------]
function ConductAssessment($CD,$Aid,$sid,$flag='',$more='') { $str=""; $str2=""; $copyflag=0; 
  $ADir="$CD/ASSESSMENT/$Aid"; $sADir="$CD/STUDENTS/$sid/$Aid"; if(!is_dir($sADir)) { if (!mkdir("$sADir",0777,true)) die("Failed to create folders"); } 
  $ShowAnswers= $more['AInfo'][$Aid]['ShowAnswers']; 
  FileIORead("$ADir/QList.xml", $QList, $root, "ReadQ"); //FileIO("$ADir/QList.xml", $QList, $root, "DisplayA2XML"); 
  foreach($QList['Q'] as  $k =>$v) $Q2ID[]=$v['@attributes']['UID'];

  $file="$sADir/QList.xml"; 
  if(file_exists("$file")) FileIORead("$file", $sQList, $root, "ReadQ"); 
  foreach($sQList['Q'] as  $k =>$v) {$UID=$v['@attributes']['UID']; $sQ2ID["Q_$UID"]=$v['@attributes'];}
  foreach($Q2ID as $k=>$w) { $v = "$ADir/$w.xml"; 
    $k1=$k+1;  $bid="QidB-$k-$Aid"; $divid="QidDesc-$k-$Aid";  $file="$w.xml"; $fileid = $w;

    $disabled=0; $checked=0; $tried=0; $submit=1; 
    if(isset($sQ2ID["Q_$w"]) ) { $Score=$sQ2ID["Q_$w"]['Score']; $disabled=1; $checked=1; $tried=1; $submit=1; }

    if(!file_exists("$sADir/$file") || $copyflag) { 
      FileIORead("$v", $Q, $root, "ReadQ"); $Q=$Q['Q'][0]; 
      if($Q['@attributes']['PHP']==1) {$Q=PHP_Q($Q);}
      if($Q['@attributes']['Random']==1 && $Q['@attributes']['Type']!=1 ) shuffle($Q['A']); 
      $QQ['Q'][0]=$Q;  FileIO("$sADir/$file", $QQ, $root, "Write"); 
    } else {FileIORead("$sADir/$file", $Q, $root, "ReadQ"); $Q=$Q['Q'][0];}

   //$str .=  "<input type=button class='QidB' id='$bid'  value='Q$k1' onclick=\"Show({id:'$divid',id2:1,type:'#',Highlight:{id:'#$bid'}})\"></input>"; 
   if($tried) { if($Score>0) $c='#00ff00'; else $c='#ff8800'; $qb="<font color='$c'><b>$k1</b></font>"; } else {$qb=$k1;}
   $str .=  "<button class='QidB' id='$bid'  value='Q$k1' onclick=\"Show({id:'$divid',id2:1,type:'#',Highlight:{id:'#$bid'}})\">$qb</button>"; 
   $str2 .=  "<div class='QidDescC' id='$divid' style='display:none;padding:3px;'>"; 
   $str2 .=  Display_Q($Q,array('Aid'=>$Aid, 'disabled'=>$disabled,'debug'=>0,'checked'=>$checked,'Submit'=>$submit,'fileid'=>$fileid, 'ShowAnswers'=>$ShowAnswers));
   //if($tried) $str2 .=  "<p/>Score = $Score"; 
   $str2 .=  "</div>"; 
  }
  if($GLOBALS['admin']) $str .=  "<br/><button onclick=\"QSubmit({id:'$fileid',Aid:'$Aid', Type:'reset'})\">Reset</button>"; 
  if($flag=='Return') return array($str, $str2); else echo "$str $str2"; 
} 
//-----------------------
function DisplayScript($flag='', $flag2='') {
echo <<<END
    <script> 
     var old = {}, sTime, eTime, TrackTime={};  
     function Show(In) {  var t='.'; if(In.type=='#') t='#';   var o1=$(t+In.id); var d = new Date(); sTime = d.getTime();
       $('.QidDescC').hide(); $("#QSubmitInfoID").html('');  
       if(In.hasOwnProperty("Highlight")) {  if(old.id)  $(old.id).css('background-color','transparent'); 
	  if(o1.css("display")=="none")  $(In.Highlight.id).css('background-color','#ff0');  else $(In.Highlight.id).css('background-color','transparent');  
	  old.id=In.Highlight.id; 
       }
        o1.toggle(); if(In.id2==1) return; 
       var o2=$(t+In.id2); if(o1.css("display")=="none") o2.show(); else o2.hide();
     }
    //-----------------------------------------
   function Questions_Load_Attempt(In) { In.AInfo = $('#'+In.AID+'-info').data();  dimag(In);  }
   function Questions_Edit_Save(In) { 
     if(In.hasOwnProperty('QData'))  {  var ValClass={}; 
	$('.'+In.QData).each(function(){var id = $(this).prop('id');  
           if( $(this).prop('type') == 'checkbox') {if( $(this).prop('checked')) $(this).val(1); else $(this).val(0); } //alert(id);
            if(CKEDITOR.instances[id]) ValClass[id] = CKEDITOR.instances[id].getData(); else ValClass[id] = $(this).prop('value');  
        }); 
        In.QData=ValClass; 
     } 
     dimag(In); 
  }
   //-----------------------------------------
    function QSubmit(In) { var AInfo = $('#'+In.Aid+'-info').data(); // alert(JSON.stringify(AInfo)); return; 
      if(In.Type==1) {var values=$('#FillIn-'+In.id).val(); if(!isNumber(values)) {alert('Entered value must be a number'); return; }
      } else if(In.Type == 'reset') { //--------------
      } else {  var values = $('input:checkbox:checked.MC-'+In.id).map(function () { return this.value;}).get();  if(values.length<1){ alert('Select one or more'); return; }}
       In.values = values;  Fun_TrackTime('eTime');  In.Time = TrackTime;

    //alert( ($("[name='Matlab']").val()) ); return;


    dimag({'outputid':In.outputid,'LoadPHP':In.LoadPHP, 'Question':{'Type':In.Type,'id':In.id, 'Time':In.Time, 'values': In.values},'COURSE':In.CourseID, 'AID':In.Aid, 'Submit':'SubmitQ', 'Attempt':In.Attempt, 'AInfo':AInfo}); 
      $('.MC-'+In.id).prop('disabled',true); 
     // alert(JSON.stringify(In)); 
    return; 

       $.ajax({url: "Main.php",  type: "POST",  dataType:"html", data: {'attr':$('#AssessmentLocationID').data(), 'id': 'Submit', 'LoadPHP':'Admin/NewAssessment.php', 'Info':In},
          success: function( data ) {//alert(JSON.stringify(In));
            $("#QSubmitInfoID").html(data); $('.MC-'+In.id).attr('disabled',true); 
          },
          error: function(data){alert('Error occurred'); }
       });
   }

   function isNumber(n) { return !isNaN(parseFloat(n)) && isFinite(n); }
   function Fun_TrackTime(n) { var d = new Date(); TrackTime[n]= d.getTime(); }
 </script>
END;
}
//.....
function checkbox($id,$flag,$o,$c='checked') {$style=' style="display:none"'; $id2="Ck-".$id;
  if($flag) return "<input type=checkbox onclick=\"driver({flag:'Score',flag2:'Checkbox',id:'$id',o:'$o'},'top')\" id=$id2 class=StudentsCB $style $c></input>";  
}
function UserData($d,$e) { $f="$d/Q.xml"; $s=0; $n=0; $nt=1; $Errata=array();
 if(is_array($e)) {foreach($e['Q'] as $i=>$v) $Errata[] = $v['@attributes']['UID'];} 
 if(file_exists($f)) {FileIORead($f, $Q, $root, "ReadQ"); 
    $n=sizeof($Q['Q']); if($n>0) $nt=$n;  $s=0; 
    foreach($Q['Q'] as $v) { $score=$v['@attributes']['CScore'];
      if(in_array($v['@attributes']['UID'], $Errata)) $score=10;  
      $s +=$score;
    }
 }
 return array('Score'=>$s, 'n'=>$n,'nt'=>$nt); 
}
 return;

function grade($ts) {  global $nG; $GB=array(88,78,68,58); 
 if($ts>$GB[0]) {$G='A'; $nG['A']++; } elseif($ts>$GB[1]) { $G='B'; $nG['B']++; } elseif($ts>$GB[2]) {$G='C'; $nG['C']++; } elseif($ts>$GB[3]) {$G='D'; $nG['D']++; } else { $G='F'; $nG['F']++; } return $G; 
}
function FScoreSum($Q) {$s=0; foreach($Q as $v) $s +=$v['@attributes']['CScore']; return $s; }
function ListFile($d) {foreach(glob($d) as $k) echo basename($k)."<br/>";}
function RA($Q) {return array(1,42,3);}
function GetTimeVK($v,$In='',$Out='') {
  if($In=='') $str=current(explode(':',$v)).' '.str_replace('-',':',end(explode(':',$v))) .':00'; else $str=$In; 
  $miliS=strtotime($str); if($Out=='h') return date('l, jS F Y h:i:s A',$miliS); else return $miliS; 
}
function htextVK($t,$flag=1, $c="#afa"){if(!$flag) $c="transparent";  return "<span style='background-color:$c'>$t</span>"; }
function ReadRoster($f) {FileIORead($f, $Stmp, $root, "ReadQ"); $nS=0; 
  foreach($Stmp['S'] as $k=>$v) { //if($nS>3) continue; 
   $S[$v['id']]=$v['name']; $nS++;};  return $S; 
}

//-----------------------
function Questions_DefaultQ($Qid,$uid) {
return <<<END
<?xml version="1.0" encoding="UTF-8"?>
<Questions>
  <Q Level="2" GQID="0" UID="$Qid" Random="1" Ch="Ch1,Regression,CFD" PHP="0" EXCEL="0" Type="0" Points="10">
    <Description ShortTitle="0">Description</Description>
    <A status="1">Ans1</A>
    <A status="0">Ans1</A>
    <A status="0">Ans1</A>
    <A status="0">Ans1</A>
    <Solution Review="Review.xml">Solution is</Solution>
    <Creator name="Name"  uid="$uid" time="2013-09-26_16:37:45">Created on</Creator>
    <Comment Name="Name" uid="$uid" time="2015-04-30-23:44">Comment</Comment>
  </Q>
</Questions>
END;
}
//-----------------------
function Questions_DefaultPHP() {
return '
$desc=$Q["Description"]["@value"];
$PHP_C=array("B & D Systems", "Envirodyne Inc."); shuffle($PHP_C);
$PHP_P=mt_rand(25,50); $PHP_N=mt_rand(1,3); $INT=mt_rand(3,10)*100; $PHP_T=$PHP_P+$INT;

$desc=str_replace("PHP_C",$PHP_C[0],$desc);
$desc=str_replace("PHP_P",number_format($PHP_P),$desc);
$desc=str_replace("PHP_N",$PHP_N,$desc);
$desc=str_replace("PHP_T",number_format($PHP_T),$desc);


$PHP_Ans=$PHP_T/$PHP_P; $PHP_N=1/$PHP_N;

$answer=pow($PHP_Ans,$PHP_N)-1; $answer=$answer*100;


$Q["Description"]["@value"]=$desc;

$Q["A"][0]["@value"]=str_replace("Ans1",$answer,$Q["A"][0]["@value"]);

';

}
?>
