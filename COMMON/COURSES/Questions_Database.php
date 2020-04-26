<?php
include("$HOME/COMMON/common-old.php");  $PData = $_POST['send']; $PData2 = $_POST['opt']; 
global $CKEditorRemoveButtons;  $CKEditorRemoveButtons="'Save,NewPage,Print,Templates,Language,Image,Flash,Uploadcare,gg,About'"; 
$outputid = $PData['outputid']; $QInfo=$PData['Question'];  $LoadPHP = $PData['LoadPHP'];  $CourseID= $PData['COURSE'];  $Aid =  $PData['AID']; $Qid = $QInfo['id']; 
$idtmp = 'inside-Questions'; 
$QDatabase = "$DATA/COURSES/$CourseID/Questions/English"; $QDir = $QDatabase;  if(!is_dir("$QDatabase/TEMP")) {if(!mkdir("$QDatabase/TEMP",0777, true)) die("Error creating $QDatabase/TEMP dir"); }
$O = array('TEMP'=>"$TEMP", 'Qid'=>$Qid, 'Aid'=>$Aid, 'Submit'=>1, 'disabled'=>0, 'LoadPHP'=>$LoadPHP, 'CourseID'=>$CourseID, 'outputid'=>$outputid, 'idtmp'=>$idtmp); 
 $O['COURSEO']= $PData2['info']['COURSE']; 

if($PData['EditQ'] == 'Move') {$file=basename($PData['file']); echo "Moved to TEMP";  copy("$QDatabase/$file", "$QDatabase/TEMP/$file"); unlink("$QDatabase/$file");  return;  }
if(isset($PData['EditQ']) ){ Question_EditQ($PData['file'], $O); return;  }
if($PData['flag'] == 'DelfromA'){ Questions_QDelFromAssessment($PData, $O) ;  return; }
if($PData['flag'] == 'Duplicate'){ $file=$PData['file']; $newfile=dirname($file).'/'.uniqid().'.xml'; copy($file,$newfile); echo "<br/>$file <br/>copied to<br/> $newfile<br/>Reload to see it at end";  return; }
if($PData['flag'] == 'Copy2Course'){ $file=$PData['file']; $newfile="$DATA/COURSES/".$PData['CourseO'].'/Questions/English/'.basename($file);  copy($file,$newfile); echo "<br/>$file <br/>copied to<br/> $newfile<br/>";  return; }

DisplayScript(); 

//---------------------------List Questions in Database/Assessment................
if(!file_exists("$QDatabase/QInfo.json") || $PData['flag']=='RecreateQInfo') Questions_Database_CreateQInfo($QDatabase, $O); 
$QInfo = json_decode(file_get_contents("$QDatabase/QInfo.json"),true); 
$s = "Filter by"; 
foreach($QInfo['Keywords'] as $k=>$v) { $str=$k; if($PData['FilterBy'] == $k) $str = "<b>$k</b>"; 
  $s .= "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID', 'flag':'LoadAllDatabase', 'FilterBy':'$k'}); \">$str</button> "; 
}

$CourseInfo=json_decode(file_get_contents("$DATA/COURSES/Courses.json"), true); $str=''; 
foreach($CourseInfo as $k=>$v) {$name=$v['Name'];   $str .= "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$k'}); \">".htext($name,$k==$CourseID,'yellow')."</button> "; }
$s .=  togglePHP("<span style='border: 4px solid blue;'>$str</span>", uniqid(),'+','str', "All Courses"); 

echo "<p/>$s"; 
  echo "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID',  'flag':'RecreateQInfo'}); \">".htext('Recreate Q-Info',1,'white')."</button> "; 
  Questions_ListAllQ($PData, $QInfo, $QDatabase, $O); 
  return; 
echo "<script>MathJax.Hub.Queue(['Typeset',MathJax.Hub]); </script>"; 
echo "<span id='$idtmp'></span>"; 

//--------------------------
function Questions_QDelFromAssessment($PData, $O = "")  { $AInfo = json_decode(file_get_contents($PData['ADir'].'/AInfo.json'),true);  $Qid=$PData['Qid']; 
 $Qstmp = $AInfo['Questions']; unset($AInfo['Questions']); $ktmp = 0; 
 foreach($Qstmp as $k=>$v) {if(!($v == $Qid)) $AInfo['Questions'][$ktmp]=$v;  $ktmp++; }
 file_put_contents($PData['ADir'].'/AInfo.json', json_encode($AInfo)); 
}
//--------------------------
function Questions_QDuplicatetoAssessment($PData, $O = "")  { $AInfo = json_decode(file_get_contents($PData['ADir'].'/AInfo.json'),true);  
 $Qid=$PData['Qid']; $QDatabase = $PData['QDatabase']; $newQid = uniqid();  echo "copy $QDatabase/$Qid.xml $QDatabase/$newQid.xml"; 
 copy("$QDatabase/$Qid.xml", "$QDatabase/$newQid.xml"); $AInfo['Questions'][] = $newQid; 
 file_put_contents($PData['ADir'].'/AInfo.json', json_encode($AInfo)); 
}
//-----------------------------------------
function Questions_Database_CreateQInfo($QDatabase="", $O = "")  {
    $qfiles = glob("$QDatabase/?????????????.xml");  
    foreach($qfiles as $k=>$v) { $Qid = basename($v,'.xml');  FileIORead("$v", $QQ, $root, 'ReadQ');  
       foreach(explode(',',$QQ['Q'][0]['@attributes']['Ch']) as $ik=>$iv) {if(!isset($Keywords["$iv"])) $Keywords["$iv"][0] = $Qid; else $Keywords["$iv"] []=$Qid; }
    }
    $QInfo['Keywords'] = $Keywords; 
    echo json_encode($QInfo); 
    file_put_contents("$QDatabase/QInfo.json", json_encode($QInfo)); 
}
//---------------------------List Questions in Database/Assessment................
function Questions_ListAllQ($PData, $QInfo="", $QDatabase="", $O = "")  { $strAll = "";  $CourseO= $O['COURSEO']; 
  $LoadPHP = $O['LoadPHP']; $outputid= $O['outputid'];  $TEMP= $O['TEMP']; $CourseID= $PData['COURSE'];   $O['Submit']=0; $O['checked']=1;
  $qfiles = glob("$QDatabase/?????????????.xml");  
  
  $ktmp = 0; 
   
  foreach($qfiles as $k=>$v) {    $Qid = basename($v,'.xml');  $strM = ""; $O['DisplayChoices']='toggle';   
   if(isset($PData['FilterBy'])) { $O['PHP_Q']=1;  $FilterBy = $PData['FilterBy']; if(!in_array($Qid, $QInfo['Keywords'][$FilterBy]) ) continue; }
   $ktmp++;     
   FileIORead($v, $QQ, $root, 'ReadQ');    $uqid = uniqid(); 
   $strAll .=  togglePHP(Read_Q(basename($v), $QDatabase, $TEMP, $O, $PData['AInfo']), $uqid,'-','str', "<hr/>(Q$ktmp)"); 
   $strAll .= "<button onclick=\"dimag({'outputid':'$uqid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID', 'EditQ':'Open', 'file':'$v'}); \">Edit</button> "; 
   $strAll .= "<button onclick=\"dimag({'outputid':'$uqid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID', 'EditQ':'Move', 'file':'$v'}); \">Delete</button> "; 
   $strAll .= "<button onclick=\"dimag({'outputid':'$uqid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID', 'flag':'Duplicate', 'file':'$v'}); \">Duplicate</button> "; 
   if($CourseID != $CourseO) $strAll .= "<button onclick=\"dimag({'outputid':'$uqid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID', 'flag':'Copy2Course', 'file':'$v', 'CourseO':'$CourseO'}); \">Copyto $CourseO </button> "; 

  }
  
  echo "<br/>$filterS <hr/> $strAll"; 
  return;
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
function Question_EditQ($file, $O) { global $CKEditorRemoveButtons;  
   $AddAttr = array("Points"=>10, 'Type'=>0);  
   $qfile = "$file"; $ignoreKeys = array('UID', 'GQID'); $strE = "";  $class = 'EditQClass'; 
    
   $LoadPHP = $O['LoadPHP']; $CourseID= $O['CourseID']; $outputid= $O['outputid']; $idtmp= $O['idtmp'];  $Qid = basename($file,'.xml'); $Aid = $O['Aid']; 
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
     $QQ['Q'][0] = $Q; FileIO("$qfile", $QQ, $root, 'Write'); ; 
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
    echo "<button onclick=\"Questions_Edit_Save({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'EditQ':'Save','QData':'$class', 'file':'$file'});\">Save</button>";  

   //pa($Q); 
}
//--------------------------
function Read_Q($file, $D="", $TEMP='/tmp', $O = "", $AInfo="") { 
    if(isset($O['Attempt'])) $Attempt = $O['Attempt']; else $Attempt = 0; //echo "$D/$file"; pa($O); 
    FileIORead("$D/$file", $QQ, $root, 'ReadQ');   $nAttempt = sizeof($QQ['Q']);  if($nAttempt>1 && !isset($_POST['send']['Attempt']) ) $Attempt=$nAttempt - 1;  
    $Q=$QQ['Q'][$Attempt]; if ($O['PHP_Q'] == 1)  { $Q = PHP_Q($Q, $TEMP); }
    if(isset($Q['AO'])) {$O['checked']=1; $O['disabled']=1;  }
    $str  = ''; 
    $desc = $Q['Description']['@value'];
    $Type = $Q['@attributes']['Type'];    

    $Time = time();
    $LoadPHP = $O['LoadPHP']; $CourseID= $O['CourseID']; $outputid= $O['outputid']; $idtmp= $O['idtmp'];  $Qid = $O['Qid']; $Aid = $O['Aid']; 
    if ($O['disabled'])         $disabled = 'disabled';
    if ($O['Submit']){ echo "<script>Fun_TrackTime('sTime');</script>"; 
       $strS = "<br/><input class='MC-$Qid' type=button value=Submit onclick=\"QSubmit({id:'$Qid',Aid:'$Aid', Type:'$Type', LoadPHP:'$LoadPHP',CourseID:'$CourseID',outputid:'$idtmp', 'Attempt':'$Attempt'})\" $disabled></input>$solved";  
     }
       if($nAttempt>1) { $stmp = ""; 
         for($iattempt = 0; $iattempt <$nAttempt; $iattempt++)  { $atr=$QQ['Q'][$iattempt]['@attributes'];  $color = "";  $hcolor = ""; 
           if($Attempt == $iattempt) {$hcolor  = 'yellow'; $sattempt = "<b>$iattempt</b>"; } else            $sattempt = "$iattempt"; 
           if(isset($atr['Score'])) { if($atr['Score']) $color='#00ff00'; else $color='#DDA0DD'; } 
           $stmp .= "<button style='background-color:$color' onclick=\"Questions_Load_Attempt({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Attempt':$iattempt});\">$sattempt </button>";  
         }
         $strS .= " | Attempts $stmp | "; 
      }
   if ($O['disabled'])     { 
       if($nAttempt<$AInfo['maxattempts']) $strS .= "<button onclick=\"Questions_Load_Attempt({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Attempt':$nAttempt, 'TryAgain':1});\">Try again</button>";  
     }
     if($nAttempt >= $AInfo['showsolution'] && !($AInfo['showsolution'] < 0) && isset($AInfo['showsolution']) ) { 
        $strS .= "<button onclick=\"dimag({'outputid':'$idtmp','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Submit':'SeeSolution'}); \" >Solution </button>";  
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
