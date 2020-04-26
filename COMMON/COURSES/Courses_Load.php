<?php 

$CourseID=$_POST['send']['COURSE']; $CourseDIR="$DATA/COURSES/$CourseID";  if(!is_dir($CourseDIR)) mkdir("$CourseDIR",0777); 

echo $_POST['send']['COURSE'].":";  
$CFile = "$CourseDIR/CourseInfo.json";   $LoadPHP = $_POST['send']['LoadPHP']; $idmain="$CourseID"; 
$default='{"Contents":{"Name":"Sections"},"Assessments":{"Name":"Assessments","Allowed":"Admin"},"Monitor":{"Name":"Monitor","Allowed":"Admin"}}'; 

if(file_exists("$CFile")) $CStr=file_get_contents("$CFile"); else {$CStr=$default; file_put_contents("$CFile", $CStr); }
if(!isset($_POST['send'][$idmain])) {  $CList=json_decode($CStr,true);  
  foreach($CList as $k=>$v) { if($v['Allowed']=='Admin' && !$admin) continue;  
    $name=$v['Name'];    if(isset($v['LoadPHP'])) $LoadPHP1 = $v['LoadPHP']; else $LoadPHP1 = "COURSES/COURSES_$k.php"; 
    echo "<button onclick=\"dimag({'outputid':'middle','LoadPHP':'$LoadPHP1', 'COURSE':'$CourseID'}); \">$name</button>";  
  }

 echo "<span id='$idmain'></span>"; 
  if($admin) {
    echo "<button onclick=\"dimag({'outputid':'$idmain','LoadPHP':'$LoadPHP','$idmain':'Edit', 'COURSE':'$CourseID'}); document.getElementById('$idmain-Save').style.display = 'inline'; \">Edit</button>";
    echo "<button id='$idmain-Save' style='display:none' onclick=\"dimag({'outputid':'$idmain-msg','LoadPHP':'$LoadPHP','$idmain':'Save','GetValID':'$idmain-TA', 'COURSE':'$CourseID'})\">Save</button>";
 }
 echo "<span id='$idmain-content'></span><span id='$idmain-msg'></span>"; 
}

if($_POST['send'][$idmain]=="Edit") { echo "<br/><textarea id='$idmain-TA' rows=5 cols=100>$CStr</textarea><br/>"; return; }
if($_POST['send'][$idmain]=='Save') {file_put_contents("$CFile", $_POST['send']['val']);  echo "<p/>$CFile Saved at " . date('Y-m-d H:i:s'); return; }

return; 

?>
