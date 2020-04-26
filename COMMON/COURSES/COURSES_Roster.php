<?php
if(!$admin) return; 
include("$HOME/COMMON/common-old.php");  
$idtmp = 'inside-COURSES-Roster'; $PData=$_POST['send']; 
$CourseID =$PData['COURSE']; $LoadPHP = $PData['LoadPHP'];  $outputid=$PData['outputid']; $CourseDir="$DATA/COURSES/$CourseID";  

$O['LoadPHP'] = $LoadPHP ; $O['outputid'] =  $idtmp; 
if($PData['flag']=='Roster2Users') { Roster_Roster2Users("$CourseDir/Roster.xml", $CourseID); return;}
if($PData['flag']=='Modify') { Roster_EditUsers($PData, "$CourseDir/Roster.json", $CourseID, $O); return;}
if($PData['flag']=='Upload') { Roster_Upload($PData, "$CourseDir/Roster.json", $CourseID,  $idtmp, $LoadPHP, $O); return;}


$str = "<button onclick=\"dimag({'outputid':'$idtmp','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Roster2Users'}); \" >Roster2Users</button> "; 
$str .=  togglePHP("Create Users from Roster.xml  or Roster.csv (1st choice)", uniqid(),'+','str'); 
$str .= " | <button onclick=\"dimag({'outputid':'$idtmp','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Upload'}); \" >Upload</button>"; 
$str .= " | <button onclick=\"dimag({'outputid':'$idtmp','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Modify'}); \" >EditUsers</button>"; 
echo "$str<hr/>"; 


echo "<span id=$idtmp></span><span id=progress></span>";
?>
<script>
function RosterUploadJS(In, fileid) {     var files = document.getElementById(fileid).files; 
  for (var i = 0, f; f = files[i]; i++) { var ftmp={};  ftmp.name=f.name; ftmp.size=f.size; ftmp.type=f.type; ftmp.lastModified=f.lastModified; 
    if(f) { var reader = new FileReader();  reader.readAsBinaryString(f); //reader.readAsArrayBuffer(f); reader.readAsBinaryString(f); reader.readAsDataURL(f); reader.readAsText(f); 
      reader.onprogress = function (e) { if (e.lengthComputable) { $('#progress').append('File:'+f.name+'['+e.loaded / e.total + '],'); } }
      reader.onload = function (e) {  ftmp.data = base64_encode(e.target.result);  In.file = ftmp; $('#progress').append('<br/>Uploaded file:'+ftmp.name); 
         dimag(In); 
     }
      reader.onerror= function (e) {  if(e.target.error.name == "NotReadableError") { alert('Error');} }
    }
  } 
}

</script>

<?php

//----------------
function Roster_Upload($PData, $RosterFile, $CourseID, $outputid, $LoadPHP, $O="") { $Dir = dirname($RosterFile);   $fileid='files'; 
  if(isset($PData['SaveFile'])) { $outfile=sprintf("$Dir/%s",$PData['file']['name']); $data=base64_decode($PData['file']['data']); 
     if(file_put_contents($outfile, $data)) echo "Wrote file $outfile <br/>"; else die("Error in writting file $outfile"); 
  }
  echo "<input type='file' id='$fileid' name='files[]' multiple />"; 
  echo "<button onclick=\"RosterUploadJS({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Upload', 'SaveFile':'SameName'}, '$fileid'); \" >Upload</button>"; 
}
//----------------
function Roster_Roster2Users($RosterFile, $CourseID, $O="") { global $DATA; 
$UDir="$DATA/USERS";  $RosterFileJSON = dirname($RosterFile).'/Roster.json';  $RosterFileCSV = dirname($RosterFile).'/Roster.csv';  
if(!is_dir("$UDir")) mkdir("$UDir"); 
if(!is_dir("$UDir/Photos")) mkdir("$UDir/Photos"); 

if(file_exists($RosterFileCSV)) { 
   $csv = array_map('str_getcsv', file($RosterFileCSV));  
   foreach($csv as $k=>$v) {   $n2i=0; $p2i=1; $e2i=2; $EmailA=explode('@',$v[$e2i]);  if($k===0) { continue;  } 
     if($v[0]=='' || $v[1]=='' || $v[2]=='') {echo sprintf("User (%s, %s, %s) with empty value not created.<br/>", $v[0], $v[1], $v[2]); continue;}
     $Roster['S'][($k-1)]['name']=$v[$n2i]; $Roster['S'][($k-1)]['password']=$v[$p2i]; $Roster['S'][($k-1)]['email']=$v[$e2i]; $Roster['S'][($k-1)]['id']=$EmailA[0]; 
   }
} else {
   if(file_exists($RosterFile)) FileIORead($RosterFile, $Roster, $root, 'ReadQ');  else die('Roster file does not exists'); 
}

foreach($Roster['S'] as $k=>$v) { $userid=$v['id']; unset($W); unset($U); $sfile="$UDir/$userid.json"; $CourseID2="EA2"; 
   if(file_exists($sfile)) {$U=json_decode(file_get_contents("$sfile"),true); $U=$U[$userid]; }
   $U['UserID']=$v['id']; $U['Password']=$v['password']; $U['Email']=$v['email']; $U['LastName']=$v['name']; $U['Privilege']='Student'; 
   //$U['COURSES'][]=$CourseID; 
   if(isset($U['COURSES'])) { if(!in_array($CourseID,$U['COURSES']) ) $U['COURSES'][]=$CourseID;} else $U['COURSES'][]=$CourseID; 

   $W[$userid]=$U;  
   $RosterJSON[$userid] = array('UserID'=>$U['UserID'], 'LastName'=>$U['LastName']); 
   if(file_put_contents("$UDir/$userid.json", json_encode($W))) { file_put_contents("$UDir/Photos/$userid-photo.json", json_encode($v['photo'])); 
     echo "<br/>Created $UDir/$userid.json"; 
    } else die("Failed to create $UDir/$userid.json"); 
}
file_put_contents("$RosterFileJSON", json_encode($RosterJSON));  echo "Wrote $RosterFileJSON"; 

//pa($Roster); //pa(scandir($CourseDir));
return;

}
//----------------
function Roster_EditUsers($PData, $RosterFile, $CourseID, $O="") { global $DATA; $LoadPHP = $O['LoadPHP']; $outputid = $O['outputid']; $idtmp = "ClassSaveUserRoster"; 
$UDir="$DATA/USERS";  $RosterFileJSON = dirname($RosterFile).'/Roster.json';  
$DisplayKeys = array("UserID"=>"UserID", "Password"=>"Password", "LastName"=>"Last Name", "FirstName"=>"FirstName", "photo"=>"Photo"); 
if(file_exists($RosterFileJSON )) $RosterJSON=json_decode(file_get_contents($RosterFileJSON),true);  //pa($RosterJSON); 

//---------------Save File --------------------
if(isset($PData['DelUserID'])) { $userid = $PData['DelUserID']; unset($RosterJSON[$userid]); 
  file_put_contents("$RosterFileJSON", json_encode($RosterJSON));  echo "<br/>Deleted $userid";
}
if(isset($PData['SaveUserID'])) { $userid = $PData['SaveUserID']; $saveid = "$idtmp$userid"; $UVal = $PData['ValClass']; 
   if($userid=="NewUserID") { $newuserid = $UVal["$saveid-UserID"];     if($newuserid=="NewUserID") die("Userid $newuserid not allowed"); else $userid = $newuserid; }

   echo "Congratulations! $userid modified/created."; 
    $Password=$UVal["$saveid-Password"];     $LastName=$UVal["$saveid-LastName"];     $FirstName=$UVal["$saveid-FirstName"]; 
    if(file_exists("$UDir/$userid.json")) $U = json_decode(file_get_contents("$UDir/$userid.json"),true); 
    foreach($DisplayKeys as $j=>$w) { if(isset($UVal["$saveid-$j"])) $U[$userid][$j]  = $UVal["$saveid-$j"];  }
    if(!in_array($CourseID, $U[$userid]['COURSES'])) $U[$userid]['COURSES'][] = $CourseID; 
    file_put_contents("$UDir/$userid.json", json_encode($U)); echo "<br/>Wrote $UDir/$userid.json";
    
     $RosterJSON[$userid]['UserID'] = $userid;  $RosterJSON[$userid]['LastName'] = $U[$userid]['LastName']; 
     file_put_contents("$RosterFileJSON", json_encode($RosterJSON));  echo "<br/>Wrote $RosterFileJSON";
     //pa($U); 
     
}
//----------------------------------------------

$RosterJSON['NewUserID']=array('UserID'=>'NewUserID', 'Password'=>uniqid());


$strH= "<td></td>";    
foreach($DisplayKeys as $j=>$w) { 
   if($j=='photo') $strH .= sprintf("<td><button onclick=\" \$('.showphotoclass').css('display','inline'); \">%s</button></td>", $w); 
   else    $strH .= sprintf("<td>%s</td>", $w); 
}

$str = "<tr>$strH</tr>";  
foreach($RosterJSON as $k=>$v) { $userid=$k; $ValClassID = "$idtmp$userid"; 
   if(file_exists("$UDir/$userid.json")) $U = json_decode(file_get_contents("$UDir/$userid.json"),true); else $U[$userid]=$v; 
  if(file_exists("$UDir/Photos/$userid-photo.json")) {$photo = json_decode(file_get_contents("$UDir/Photos/$userid-photo.json"), true); $U[$userid]['photo'] =$photo; }

   $SaveButton="<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Modify', 'SaveUserID':'$userid','GetValClassID':'$ValClassID'}); \" >Save</button>"; 
   $DelButton="<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Modify', 'DelUserID':'$userid'}); \" >Delete</button>"; 

    $display='inline'; $AddB=""; if($userid=="NewUserID") {$display='none'; $AddB = "<button onclick=\" \$('.SaveDelB$userid').show(); \$(this).hide(); \">Add New User</button>";  }
    $strU= "<td><span class='SaveDelB$userid' style='display:$display'>$SaveButton $DelButton</span>$AddB</td>"; 
   foreach($DisplayKeys as $j=>$w) {
     if($j=="UserID" && $userid != "NewUserID")      $strU .= sprintf("<td>%s</td>", $U[$userid][$j]); 
     else if($j=="photo")     { 
       $strU .= sprintf("<td class=showphotoclass style='display:none;'>%s <textarea id='ckeditor$userid' onclick=\"CKEDITOR.replace('ckeditor$userid',{toolbar:[['base64image']]}); \">haha</textarea> </td>", $U[$userid][$j]); 
     } else { 
       $strU .= sprintf("<td><span class='SaveDelB$userid' style='display:$display'><input id=$ValClassID-$j  class=$ValClassID  type=text value='%s' ></input></span></td>", $U[$userid][$j]); 
     }
   }
   $str .= "<tr>$strU</tr>";  
}
//file_put_contents("$RosterFileJSON", json_encode($RosterJSON));  echo "Wrote $RosterFileJSON"; 
//pa($W);
echo "<table border=1>$str</table>"; 

//pa($Roster); //pa(scandir($CourseDir));
return;

}
?>

