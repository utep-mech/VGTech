<?php
include("$HOME/COMMON/common-old.php");  $idtmp = 'middle'; 

$CourseID =$_POST['send']['COURSE']; $AHOME="$DATA/COURSES/$CourseID/ASSESSMENT";  $LoadPHP = $_POST['send']['LoadPHP'];  $MaxNumAssessments=100; 

if(isset($_POST['send']['NewAssessment'])) {Create_NewAssessment($uid, $CourseID, $AHOME);  return; }

$str = ""; 

foreach(glob("$AHOME/A_?????????????") as $k=>$v) { $ADir=basename($v); $n=$ADir;
 if(file_exists("$v/AInfo.json")) { $AInfo=json_decode(file_get_contents("$v/AInfo.json"), true); $n=$AInfo['Name']; } //echo file_get_contents("$v/AInfo.json");
 $str .= "<button onclick=\"dimag({'outputid':'$idtmp','LoadPHP':'COURSES/Asssessment_Load.php', 'ASSESSMENTS':{'$ADir':'$n'}, 'HDir':'$AHOME', 'COURSE':'$CourseID'}); \">$n</button>"; 
}
if($admin) {
   $str .= "<button onclick=\"document.getElementById('$idtmp-NewAYesNo').style.display = 'inline';\">New Assessment</button><span id=$idtmp-NewAYesNo style='display:none;'>"; 
    $str .= "Are you sure? <button onclick=\"dimag({'outputid':'middle','LoadPHP':'$LoadPHP', 'NewAssessment':'1', 'COURSE':'$CourseID'}); \" >Yes</button>"; 
    $str .= "<button onclick=\"document.getElementById('$idtmp-NewAYesNo').style.display = 'none';\" >No</button></span>"; 
     $str .= "<button onclick=\"dimag({'outputid':'$idtmp','LoadPHP':'COURSES/Questions_Database.php', 'COURSE':'$CourseID','flag':'LoadAllDatabase'}); \" >Quenstion Bank</button>"; 
}
echo togglePHP("$str",uniqid(), '-','str'); 

echo "<span id='$idtmp'></span>"; 


//-----------------
function Create_NewAssessment($uid, $CourseID, $AHOME, $O=""){ $MaxNumAssessments = $GLOBALS['MaxNumAssessments']; 
  if(sizeof(glob("$AHOME/A_?????????????")) >= $MaxNumAssessments) { echo "<font color=red>Max number of assessment allowed=$MaxNumAssessments</font>"; return; }
$uqid=uniqid(); $CreatedBy="$uid"; $CreatedOn=time(); $sTime=time(); $eTime=$sTime + 20*60; 
$str = <<<END
{"Name":"Quiz","Status":"1","Description":"Edit","Directory":"A_$uqid","File":"LoadFromQDatabase","UID":"$uqid","CreatedOn":"$CreatedOn","CreatedBy":"$CreatedBy","MaxAttempts":"1","Randomize":"1","ShowAnswers":"-1","ShowSolution":"-1","ShowComment":"-1","sTime":"$sTime","eTime":"$eTime","DAManagement":"Default","CAManagement":"Default","TimeLimit":"-1","nQuestionsAllowed":"-1","Skip":"1","COURSE":"$CourseID"}
END;
echo "<p/>Congratulations! A new assessment (named 'Quiz') was created in the directory <br/>$AHOME/A_$uqid<br/> with the following details: <p/>$str"; 
if(!mkdir("$AHOME/A_$uqid",0777, true)) die('<p/><font color=red>Failed to create dir</font>'); file_put_contents("$AHOME/A_$uqid/AInfo.json",$str); 
}

?>
