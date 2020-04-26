<?php
$CourseDIR="$DATA/COURSES";  if(!is_dir($CourseDIR)) mkdir("$CourseDIR",0777); 
$CFile = "$CourseDIR/Courses.json";   $LoadPHP = $_POST['send']['LoadPHP']; $idmain="COURSES"; 
$default='{"EA1":{"Name":"EA-1"},"EA2":{"Name":"EA-2"},"Statics":{"Name":"Statics"}}'; 
if($device=='Apps') 	$Break="<br/>";
if(file_exists("$CFile")) $CStr=file_get_contents("$CFile"); else {$CStr=$default; file_put_contents("$CFile", $CStr); }
if(!isset($_POST['send'][$idmain])) {  $CList=json_decode($CStr,true);  //pa($CList); 
  foreach($CList as $k=>$v) { $name=$v['Name'];    
    if( !(($_POST['opt']['info'][$k]=='COURSES')  || ($v['Type']=='All'))  && !($_SESSION["AdminLevel"]=='SuperAdmin') ) continue;
   $RecordActivity = "None";  if($v['RecordActivity']['Who']=='Student')  $RecordActivity = "Student"; 
   if(isset($v['QuizTime']))  {$QuizTime1 = $v['QuizTime'][0]; $QuizTime2 = $v['QuizTime'][1];} else {$QuizTime1 = "10:20"; $QuizTime2 = "10:50";}
   if(isset($v['HomeworkTime']))  {$HomeworkTime1 = $v['HomeworkTime'][0]; $HomeworkTime2= $v['HomeworkTime'][1];} else {$HomeworkTime1 = "10:20"; $HomeworkTime2 = "23:59";}
    echo "<button onclick=\"Add2Info({ 'COURSE':'$k', 'RecordActivity':'$RecordActivity' , 'QuizTime1':'$QuizTime1' , 'QuizTime2':'$QuizTime2', 'HomeworkTime1':'$HomeworkTime1' , 'HomeworkTime2':'$HomeworkTime2'}); dimag({'outputid':'middle','LoadPHP':'COURSES/Courses_Load.php', 'COURSE':'$k'}); \">$name</button>";  
    echo $Break;
  }

 echo "<span id='$idmain'></span>"; 
 if($admin) { 
 echo "<button onclick=\"dimag({'outputid':'$idmain','LoadPHP':'$LoadPHP','$idmain':'Edit'}); document.getElementById('$idmain-Save').style.display = 'inline'; \">Edit</button>";
 echo "<button id='$idmain-Save' style='display:none' onclick=\"dimag({'outputid':'$idmain-msg','LoadPHP':'$LoadPHP','$idmain':'Save','GetValID':'$idmain-TA'})\">Save</button>";
 }
 echo "</span><span id='$idmain-msg'></span>"; 
}

if($_POST['send'][$idmain]=="Edit") { echo "<br/><textarea id='$idmain-TA' rows=5 cols=100>$CStr</textarea><br/>"; return; }
if($_POST['send'][$idmain]=='Save') {file_put_contents("$CFile", $_POST['send']['val']);  echo "<p/>$CFile Saved at " . date('Y-m-d H:i:s'); return; }

return; 



global $nG, $debug; $debug=0; 
$mout="COURSESMout";
$StudentAllowed=array('Interaction','Group'); $AllB=array('Roster'=>'middle','Monitor'=>$mout,'Assessment'=>$mout, 'Interaction'=>$mout,'Group'=>$mout);

return; 
include("$COMMON/common.php");
if($debug && !$admin) exit('Under construction, please check back little later');

//-----------Display Courses from $flag.json-------[
$HD="$DATA/$flag"; if(!is_dir($HD)) mkdir("$HD", 0777, true);

if(!isset($_POST['flag2'])) {  
  ReadMenu("$HD/$flag.json",$flag, 'Edit', $flag, $admin,!$admin, array('Loc'=>'CMenuOut'));  echo '<span id=CMenuOut></span>'; return; 
} else $flag2=$_POST['flag2'];

if($flag2=="Edit") {   WriteMenu2("$HD/$flag.json","$flag-Menu",array('default'=>$default));   return;  }

//-----------Display Courses from $flag.json-------]
$CDir="$HD/$flag2"; if(!is_dir($HD)) mkdir("$HD", 0777, true); 
$RosterFile="$CDir/Roster.xml"; 

if(!isset($_POST['flag3'])) {     
 foreach($AllB as $k=>$v) {if(!$admin && !in_array($k,$StudentAllowed)) {continue; }; 
  $ic="$flag-$flag2"; echo "<button id='$ic-$k' class='$ic' onclick=\"driver({flag:'$flag',flag2:'$flag2',flag3:'$k'},'$v')\">$k</button>"; 
 }
 echo "<script> $('.$ic').click( function() { $('.$ic').css('background-color',''); $(this).css('background-color','#99f'); } ); </script>"; 
 echo "<span id=$mout></span>"; return;
}
$flag3=$_POST['flag3']; 
$file="$COMMON/$flag/$flag3.php"; if(file_exists($file)) include($file); else exit(basename($file)." does not exists"); 
return; 
// Everything else below will go into id=mout (above)
pa(glob("$CDir/SECTIONS/upload/*")); 

$flag3=$_POST['flag3'];
if($flag3=='Assessment') $file="$COMMON/COURSES/Assessment.php";  

if(file_exists($file)) include("$file"); 



?>
