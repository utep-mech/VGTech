<?php
include("$HOME/COMMON/common-old.php");  $idtmp = 'inside-Assessment-Load';  $MaxNumQinA = 60; 
$ExcludeKeys = array("CreatedOn", "CreatedBy", "ModifiedBy", "ModifiedOn","Questions"); 

if($_POST['send']['flag']=="AQsel") {RandomQSelected(); return; }

$CourseID=$_POST['send']['COURSE']; $LDIR="$DATA/COURSES/$CourseID";  $O['TEMP']="$TEMP"; $O['COURSE']=$CourseID; $LoadPHP = $_POST['send']['LoadPHP'];  

if(isset($_POST['send']['HDir'])) $HDir=$_POST['send']['HDir']; else $HDir="$LDIR/ASSESSMENT"; 

if(isset($_POST['send']['ADDQ'])) {$f=$_POST['send']['AJSONFile'];  $AInfo=json_decode(file_get_contents($f),true); 
   if(!in_array($_POST['send']['ADDQ'], $AInfo['Questions']) && sizeof($AInfo['Questions'])<=$MaxNumQinA) $AInfo['Questions'][]=$_POST['send']['ADDQ']; else die("Error, MaxNumQinA = 60"); 
   file_put_contents($f,json_encode($AInfo));  echo "Congratulations! Added a New Q, Number of Qs=".sizeof($AInfo['Questions']); //echo json_encode($AInfo); 
   return; 
}

if($_POST['send']['Save'] == "Assessment_Quick_Save") {Assessment_Load_Save_AInfo($_POST['send']['AInfo'], $HDir, 'JSON'); return; }
?>
<script>  
    var InLocal = {}, editortmp, tstr; 
    function Assessment_Question_Load(In) {  In.AInfo = $('#'+In.AID+'-info').data();  $('.'+In.AID).css('font-weight','');   $('#'+In.Question.id+'-info').css('font-weight','Bold');  dimag(In);     } 
    function Assessment_Quick_Edit(In) { var s = '', skip = ['course','uid','directory','submit','disabled'], idtmp = 'tmpAssessment_Quick_Edit', iid; 
         var AInfo = $('#'+In.AID+'-info').data();  //alert(JSON.stringify(AInfo)); 
         InLocal  = In; 
         for (var key in AInfo) { if (AInfo.hasOwnProperty(key)) if(!(skip.indexOf(key)+1)) { iid = key+idtmp; 
             if(key == 'description') {
              s = s +'<textarea id='+iid+ '>'+AInfo[key] + '</textarea>'+key+'<button id=b'+iid+' value=+ onclick="Assessment_Quick_Editor('+"'"+iid+"'"+',{toolbar:'+"'Full'"+'})">+</button>';   
             } else {s = s +'<input id='+iid+ ' value="'+AInfo[key] + '">'+ key+ '</input> ';   }
             if(key == 'file') { s = s + ' (LoadFromQDatabase, QList.xml)'; }
             if(key == 'showsolution' || key == 'showanswers') { s = s + ' [ -1 (never show), 0 (always show), n (n>0, show after n attempts)]'; }
             s = s + '<br/>'; 
        }         }
         s = s + '<button onclick="Assessment_Quick_Save()">Save</button>'; 
        $('#'+In.outputid).html(s); 
        //dimag(In);     
    } 
    function Assessment_Quick_Editor(id,config) { var bobj = $('#b'+id);   
      if(bobj.val() == '-') {try { var value = CKEDITOR.instances[id].getData(); $('#'+id).val(value); CKEDITOR.instances[id].destroy(true);  } catch (e) { } } else      { CKEDITOR.replace(id, config);}
      if(bobj.val()=='+') {bobj.val('-'); bobj.html('Close editor'); } else {bobj.val('+'); bobj.html('+');}; 
    }
    function Assessment_Quick_Save() {  
         var s = '', skip = ['course','uid','directory'], idtmp = 'tmpAssessment_Quick_Edit', iid; 
         var AInfo = $('#'+InLocal.AID+'-info').data();  //alert(JSON.stringify(AInfo)); 
         for (var key in AInfo) { if (AInfo.hasOwnProperty(key)) if(!(skip.indexOf(key)+1)) { iid = key+idtmp; AInfo[key]  = $('#'+iid).val();  }         }
         InLocal.AInfo = AInfo; InLocal.Save="Assessment_Quick_Save"; 
        dimag(InLocal);     
    } 
</script>

<?php

$GoBack = sprintf("<button onclick=\" dimag({'outputid':'middle','LoadPHP':'COURSES/COURSES_Contents.php', 'COURSE':'$CourseID'}); \">Home</button> "); 
echo "<table border=1 width=100%><tr><td width=90%  valign=top><span id='$idtmp-Q'>Question will be displayed here</span></td><td valign=top>$GoBack"; 

foreach($_POST['send']['ASSESSMENTS'] as $k=>$v) { $O['AID']=$k; $InfoB= ''; 
  $sADir = "$DATA/COURSES/$CourseID/STUDENTS/$uid/$k";  
  if(!file_exists("$HDir/$k/AInfo.json") )  Create_AInfo_JSON("Assessment.xml", $HDir, $O); 
  if(file_exists("$HDir/$k/AInfo.json")) $AInfo=json_decode(file_get_contents("$HDir/$k/AInfo.json"),true);  //echo file_get_contents("$HDir/$k/AInfo.json"); 
  //-----------Randomly create problems ---------  
  if(!($AInfo['CAManagement'] =="Default")) { $CAM=json_decode($AInfo['CAManagement'],true); //echo $AInfo['CAManagement']; 
   if(isset($CAM['LoadRandom'])) { 
     if($admin) {if(file_exists("$sADir/QSelected.json")) unlink("$sADir/QSelected.json"); }
     if(file_exists("$sADir/QSelected.json")) { //echo "<textarea>".file_get_contents("$sADir/AInfo.json")."</textarea>"; 
      $AInfo['Questions']=json_decode(file_get_contents("$sADir/QSelected.json"),true);  //echo "$sADir/AInfo.json"; 
     } else {   
      if(!file_exists("$HDir/$k/AQsel.json")) { 
         $AQs=json_decode(file_get_contents("$DATA/COURSES/$CourseID/Questions/English/QInfo.json"),true); 
         foreach($CAM['LoadRandom']['Keywords'] as $ir=>$jr) {if(!isset($AQsel)) $AQsel=$AQs['Keywords'][$jr]; else $AQsel=array_merge($AQsel, $AQs['Keywords'][$jr]); }
         file_put_contents("$HDir/$k/AQsel.json",json_encode($AQsel));
      } else {$AQsel= json_decode(file_get_contents("$HDir/$k/AQsel.json"),true);}
      shuffle($AQsel); 
      foreach($AQsel as $ir=>$jr) {if($ir<$CAM['LoadRandom']['nQ']) $AInfo['Questions'][$ir]=$jr; }
      file_put_contents("$sADir/QSelected.json",json_encode($AInfo['Questions']));
      //pa($AInfo); 
     }
   }
   if($admin) echo "<button onclick=\"dimag({'outputid':'$idtmp-Q','LoadPHP':'$LoadPHP', 'f':'$HDir/$k/AQsel.json', 'sf':'$sADir/QSelected.json', 'Loc':'$DATA/COURSES/$CourseID/Questions/English', 'flag':'AQsel'}); \">AQsel</button> ";
   if($admin) echo "<button onclick=\"dimag({'outputid':'$idtmp-Q','LoadPHP':'$LoadPHP', 'f':'$HDir/$k/AQsel.json', 'sf':'$sADir/QSelected.json','flag':'AQsel','Del':1}); \">DelAQsel</button> ";

   //pa($AInfo); 
  }
  //---------------------------
  $dtS = Assessment_GetTimeVK($AInfo['StartTime']); $dtE = Assessment_GetTimeVK($AInfo['EndTime']); $dtN = time(); 
  if(!($dtN>$dtS && $dtN<$dtE))  { 
       $imsg = sprintf("This assessment was active from <br/> %s to <br/>%s. <br/>Current time is <br/>%s", date('l, jS F Y h:i:s A',$dtS), date('l, jS F Y h:i:s A',$dtE), date('l, jS F Y h:i:s A',$dtN)); 
       echo "<span id=$idtmp-inactive style='display:none;'>$imsg</span>"; 
       $Submit=0; //echo "<br/>".htext('Inactive'); 
       $InfoB="<button style='background-color:#FFFF00' onclick=\" document.getElementById('$idtmp-Q').innerHTML = document.getElementById('$idtmp-inactive').innerHTML; \">Inactive</button>"; 
  } else $Submit = 1; 
  
  $dstr = " data-Submit='$Submit' "; foreach($AInfo as $kk=>$vv) { if(!in_array($kk,$ExcludeKeys)) $dstr .= "data-$kk='$vv' "; } 

   //if($admin) $EditButton = "<span onclick=\"Assessment_Quick_Edit({'outputid':'$idtmp-Q','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','AID':'$k'}); \">$EditButtonText</span>"; 
   if($admin) $EditButton = "<span onclick=\"dimag({'outputid':'$idtmp-Q','LoadPHP':'COURSES/Asssessment_Edit.php', 'COURSE':'$CourseID','AID':'$k'}); \">$EditButtonText</span>"; 

   echo "<p/><b><span id='$k-info' $dstr>$v</span></b>$EditButton $InfoB<br/>"; 
   if($AInfo['File'] == "LoadFromQDatabase") {      
   $sAInfo=json_decode(file_get_contents("$sADir/AInfo.json"), true);  

    if($dtS>$dtN && !$admin) { echo "<br/>Assessment will start on ".date('l, jS F Y h:i:s A',$dtS);  continue; }

      $ktmp=0; $status = $AInfo['Status']; 
      foreach($AInfo['Questions'] as $kk=>$vv) { $qid=$vv; $qstr="Q".(1+$ktmp);  if($ktmp<1) $AllQIDStr = "['$vv.xml'"; else $AllQIDStr .= ", '$vv.xml'"; 
         if(isset($sAInfo['Questions'][$qid]['Score'])) {$avgscore=0; $num=sizeof($sAInfo['Questions'][$qid]['Score']); 
            for($j=0; $j<$num; $j++) $avgscore += $sAInfo['Questions'][$qid]['Score'][$j]; 
            $avgscore = $avgscore/$num; $percentagescore = round($avgscore * 100/10); 
             if($percentagescore>90) $color="#00ff00"; elseif($percentagescore>70) $color="#00FFFF"; elseif($percentagescore>70) $color="#EE82EE"; else $color="#FF8C00"; 
             if(sizeof($AInfo['Questions']) > 0) { if(in_array($qid, $AInfo['ErrataQ'])) $color = $color="#00FFFF"; }
             if($status==2) $qstr="Q".(1+$ktmp); else $qstr = htext("Q".(1+$ktmp), 1, $color); 
         }
        echo sprintf("<button id='$qid-info' class='$k' onclick=\"Assessment_Question_Load({'outputid':'$idtmp-Q','LoadPHP':'COURSES/Questions.php', 'Question':{'Dir':'$HDir/$k','id':'$qid'},'COURSE':'$CourseID', 'AID':'$k'}); \">$qstr </button> "); 
        $ktmp++;
      }
     $AllQIDStr .="]"; 
      if($admin) {$QID=uniqid(); 
         //echo "<button onclick=\"dimag({'outputid':'$idtmp-Q','LoadPHP':'$LoadPHP', 'AJSONFile':'$HDir/$k/AInfo.json','ADDQ':'$QID'}); \">AddQ</button>"; 
         echo  "<br/><button class='$k' onclick=\"document.getElementById('$idtmp-NewQYesNo').style.display = 'inline';\">AddQ</button><span id=$idtmp-NewQYesNo style='display:none;'>"; 
         echo "<br/>Are you sure? <br/><button onclick=\"dimag({'outputid':'$idtmp-Q','LoadPHP':'$LoadPHP', 'AJSONFile':'$HDir/$k/AInfo.json','ADDQ':'$QID'});  \" >Yes</button>"; 
         echo "<button onclick=\"document.getElementById('$idtmp-NewQYesNo').style.display = 'none';\" >No</button></span>"; 
         echo "<button class='$k' onclick=\"dimag({'outputid':'$idtmp-Q','LoadPHP':'COURSES/Questions.php', 'COURSE':'$CourseID', 'AID':'$k', 'flag':'LoadAll', 'Questions':$AllQIDStr}); \">All</button> ";
      }
   } else  Assessment_Load("QList.xml", "$HDir/$k", "$idtmp-Q", $O); 
   if($admin) echo "<button class='$k' onclick=\"dimag({'outputid':'$idtmp-Q','LoadPHP':'COURSES/Questions.php', 'COURSE':'$CourseID', 'AID':'$k', 'flag':'LoadAllDatabase'}); \">Database</button> ";
}
echo "</td></tr></table><span id='$idtmp'></span>"; 
//-------------------
function Create_AInfo_JSON($file,$D=".", $O="") { // This function reads all the Assessments (.xml) and creates A???/AInfo.jso, click debug to overwrite AInfo.json
  FileIORead("$D/$file", $A, $root, 'ReadQ');   $CourseID = $O['COURSE']; $AID=$O['AID']; //pa($A);
  foreach($A['Q'] as $k=>$v)  { $ADir="$D/".$v['Directory']; 
      foreach(glob("$ADir/?????????????.xml") as $kk=>$vv) $v['Questions'][$kk]=basename($vv,'.xml'); 
     $fout = "$ADir/AInfo.json"; $v['COURSE']=$CourseID;  
     file_put_contents("$fout",json_encode($v)); 
  }
}
//-------------------
function Assessment_GetTimeVK($v,$In='',$Out='') {
  if($In=='') $str=current(explode(':',$v)).' '.str_replace('-',':',end(explode(':',$v))) .':00'; else $str=$In;
  $miliS=strtotime($str); if($Out=='h') return date('l, jS F Y h:i:s A',$miliS); else return $miliS;
}
//-------------------
function Assessment_Load_Save_AInfo($AInfo,$D=".", $flag="") { $ADir = sprintf("$D/%s",$AInfo['directory']); $file = "$ADir/AInfo.json"; 
  $A =   json_decode(file_get_contents("$file"), true);  //echo file_get_contents("$file");
   foreach($A as $k=>$v) {if(isset($AInfo[strtolower($k)])) $A[$k] = $AInfo[strtolower($k)];  }
   $A['ModifiedBy'] = $_SESSION['logged']; $A['ModifiedOn'] = time();  
   file_put_contents("$file",json_encode($A)); 
   echo "File $file saved on " . date("Y-m-d H:i:s"); 
}
//-------------------
function Assessment_Load($file,$D="", $id="", $O="") {FileIORead("$D/$file", $A, $root, 'ReadQ');   $CourseID = $O['COURSE']; $AID=$O['AID'];  global $admin, $DATA; 
  foreach($A['Q'] as $k=>$v)  { $qid = $v['@attributes']['UID'];  if($k<1) $AllQIDStr = "['$qid.xml'"; else $AllQIDStr .= ", '$qid.xml'"; 
     //$fileA = "$D/$qid.xml"; $fileD = "$DATA/COURSES/$CourseID/Questions/English/$qid.xml";  if(!file_exists($fileD)) copy($fileA,$fileD); 
     echo sprintf("<button id='$qid-info' class='$AID' onclick=\"Assessment_Question_Load({'outputid':'$id','LoadPHP':'COURSES/Questions.php', 'Question':{'Dir':'$D','id':'$qid'},'COURSE':'$CourseID', 'AID':'$AID'}); \">Q%2d </button> ",($k+1)); 
      //echo Read_Q($qf,$D,$O); 
  }
  $AllQIDStr .="]";  
  if($admin) echo "<button class='$AID' onclick=\"dimag({'outputid':'$id','LoadPHP':'COURSES/Questions.php', 'COURSE':'$CourseID', 'AID':'$AID', 'flag':'LoadAll', 'Questions':$AllQIDStr}); \">All</button> ";
}


//-------------------

function RandomQSelected($O=""){ 
  $f=$_POST['send']['f']; $sf=$_POST['send']['sf'];  $Loc=$_POST['send']['Loc']; $LoadPHP=$_POST['send']['LoadPHP']; $outputid=$_POST['send']['outputid'];
  if($_POST['send']['AddedQ2sel']) { 
     copy("$f-add",$f); echo "cp $f-add $f $sf"; 
     if(file_exists($sf)) {unlink($sf); echo "<br/>deleted '$sf'";  }
     return; 
  }
  if($_POST['send']['Del']) {
    if(file_exists($f)) {unlink($f); unlink("$f-add"); echo "<br/>deleted '$f'";  }
    if(file_exists($sf)) {unlink($sf); echo "<br/>deleted '$sf'";  }
    return;
  }
  $AQsel=json_decode(file_get_contents("$f"), true); 

  if(isset($_POST['send']['DelQ'])) {$DelQ=$_POST['send']['DelQ']; //echo "Deleted:$DelQ";  
    foreach($AQsel as $i=>$v) {
       if($v==$DelQ) {array_splice($AQsel, $i, 1);}
    }
    file_put_contents($f,json_encode($AQsel));
    return; 
  }
  if(isset($_POST['send']['AddQ'])) {$AddQ=$_POST['send']['AddQ']; //echo "Deleted:$DelQ";  
    if(file_exists("$f-add")) $AAQsel=json_decode(file_get_contents("$f-add"), true); 
    if(!in_array($AddQ,$AAQsel)) {$AAQsel[]=$AddQ; file_put_contents("$f-add",json_encode($AAQsel)); }
    pa($AAQsel); 
    return; 
  }
  echo "<button onclick=\" dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'f':'$f', 'sf':'$sf', 'flag':'AQsel','AddedQ2sel':1}); \">AddedQ2sel</button>"; 
  foreach($AQsel as $i=>$v) { 
     $qf="$Loc/$v.xml"; 
     FileIORead("$qf", $Q, $root, 'ReadQ'); //if($i==2) pa($Q); 
     $a=$Q['Q'][0]['@attributes']; 
     $info=sprintf("Level=%s, Ch=%s, PHP=%s, Type=%s",$a['Level'], $a['Ch'], $a['PHP'], $a['Type']); 
     $desc=$Q['Q'][0]['Description']['@value']; 

     $ops="<button onclick=\" dimag({'outputid':'random-q-$i','LoadPHP':'$LoadPHP', 'f':'$f', 'flag':'AQsel','DelQ':'$v'}); \">X</button>"; 
     $ops .="<button onclick=\" dimag({'outputid':'random-q-$i','LoadPHP':'$LoadPHP', 'f':'$f', 'flag':'AQsel','AddQ':'$v'}); \">Add</button>"; 

     echo "<span id='random-q-$i'><hr/><u>$ops (Q$i)$info</u> <br/>$desc</span>";  // \$('#random-q-$i').hide(); 
  }
}
//---------------------
?>


