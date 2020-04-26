<?php
 
$CDir = "$HOME/DATA/$CourseDir"; $SCDir = "$CDir/STUDENTS"; $ADir= "$CDir/ASSESSMENT"; 
if($_POST['id']=='Submit') { SubmitAnswers($CDir, $userid); return; }

DisplayScript(); 


FileIORead("$ADir/Assessment.xml", $A, $root, "ReadQ");

$str = ''; $str2 = ''; 
foreach($_POST['Assessments'] as $ia=>$tmp) {
 foreach($A['Q'] as $k=>$v) if($ia==$v['Directory']) $AInfo[$ia] = $v; 
 $strA = ConductAssessment($CDir,$ia,$userid,'Return',array('AInfo'=>$AInfo)); 
 $str .= sprintf('<p/><b>%s</b><br/>', $AInfo[$ia]['Name']); 
 $str .= $strA[0]; $str2 .= $strA[1]; 
}
  
  $str2 .= "<div id=QSubmitInfoID></div>"; 
  echo "<table border=1 width=100%><tr><td valign=top>$str2</td><td width=20% height=300 valign=top> $str</td></tr></table>"; 


//pa($AInfo);

exit('');

//-------------------------------------------
global $nG, $debug; 
 
$debug=1; if(!$admin || $_POST['StudentPriv']=='true') if($debug) exit('Under construction, please check back little later');
 $RosterFile="$CD/Roster.xml"; $flag2=$_POST['flag2']; 
if(!isset($_POST['flag2'])) { $b=array('All'=>'middle','Active'=>'middle','Conduct'=>'middle','Practice'=>'middle'); if(!$admin) $b=array('Practice'=>'middle');
 foreach($b as $k=>$v) { 
  echo "<button id='$flag-$k' class='$flag' onclick=\"driver({flag:'$flag',flag2:'$k',color:'#00aaff'},'$v')\">$k</button><br/>";
 }
 return; 
} 
$AFile="$CD/ASSESSMENT/Assessment.xml"; 

if(in_array($flag2, array('All','Active'))) { FileIORead($AFile, $A, $root, "ReadQ"); $str=''; 
 foreach($A['Q'] as $i=>$v) { if($admin) $jsonS=json_encode($v); $jsonS=str_replace('\/','/',$jsonS); 
    $Aid= $v['Directory']; $ADir="$CD/ASSESSMENT/$Aid";  $Aname= $v['Name'];
    $now=GetTimeVK('','now'); $Active=0; $color='#5f5'; $sTime=GetTimeVK($v['StartTime']); $eTime=GetTimeVK($v['EndTime']); 
    if($now>$sTime && $now<$eTime ) $Active=1; 
    if($flag2=="Active" && !$Active) {$now1wk=GetTimeVK('','now+1week'); if($now1wk>$sTime && $now<$eTime){$Active=1;$color='#ada';} else continue;} 
    echo "<span id='idAll-$i' onclick=\"Show({id:'idAll2-$i',id2:1,type:'#'})\">".htextVK($Aname,$Active,$color)."</span>"; 
    if($debug) echo "<div id='idAll2-$i' style='display:none;padding:10px'>'$jsonS'</div> | ";  
 }
 return; 
}

if($flag2=="Practice") { 
   if($admin) {$w=$u;  echo "<button onclick=\"driver({flag:'$flag',flag2:'$flag2',List:'All'},'middle')\">All</button>";
      if($_POST['List']=='All') {foreach(glob("$CD/STUDENTS/*") as $v) { if(!is_dir("$v/Practice")) continue;
                $w=basename($v);  echo "<button onclick=\"driver({flag:'$flag',flag2:'$flag2',List:'$w'},'middle')\">$w</button>"; } 
      return;   } else {  if(isset($_POST['List'])) $w=$_POST['List']; };
  }
   if($_POST['id']=='Submit') {CheckAnswers($CD,$_POST['qid'],$w);  }; 
   echo "<div id=Amessage></div>";  Practice($CD,'Database',$w);  
}

if(in_array($flag2, array('Conduct'))) {  
 
 FileIORead($AFile, $A, $root, "ReadQ"); $str=''; 
 foreach($A['Q'] as $i=>$v) { $Aid= $v['Directory']; $ADir="$CD/ASSESSMENT/$Aid";  $Aname= $v['Name']; $color='transparent'; if($Aid==$_POST['Aid']) $color='#0ff'; 
    $now=GetTimeVK('','now'); $sTime=GetTimeVK($v['StartTime']); $eTime=GetTimeVK($v['EndTime']); $TimeLeft=$eTime-$now; 
    if($now>$sTime && $now<$eTime ) {$str .="<button style='background-color:$color;' onclick=\"driver({flag:'$flag',flag2:'$flag2',Aid:'$Aid'},'middle')\">$Aname</button>";}
    //$str .="<button onclick=\"driver({flag:'$flag',flag2:'$flag2',Aid:'$Aid'},'middle')\">$Aname</button>  ";
 }
 echo "$str"; 
 if(isset($_POST['Aid'])) {ConductAssessment($CD,$_POST['Aid'],$u,'DisplayAll'); } 
 return; 
}
return;

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
function CheckAnswers($CD,$qid,$sid,$O='') { $Time=time(); $sTime=$_POST['Time']; 
   $SDir="$CD/STUDENTS/$sid/Practice";   $tmpfile="$SDir/tmpfile.xml";   $qfile="$SDir/$qid.xml";    rename($tmpfile,$qfile); 
   FileIORead($qfile, $Q, $root, "ReadQ"); $Q=$Q['Q'][0];   if(!isset($Q['AO']))  $Q['AO']=$Q['A']; 
    if($_POST['Type']==1) { 
      $vu=$_POST['values']; $v1= $Q['AO'][0]['@value']; $v2= $Q['AO'][1]['@value']; $Q['A'][0]['@value']=$vu; $dv1=$v1-$vu; $dv2=$v2-$vu;  if(abs($vu)<1e-6) $vu=1e-6; 
      if(abs($dv1/$vu) <0.03 || abs($dv2/$vu) <0.03) $ans=1; else $ans=0; 
   } else {
     foreach($Q['A'] as $k=>$v) { if(in_array($k,$_POST['values']))  $Q['A'][$k]['@attributes']['status']=1; else $Q['A'][$k]['@attributes']['status']=0;}  
     $ans=1; foreach($Q['A'] as $k=>$v) { if($Q['A'][$k]['@attributes']['status'] != $Q['AO'][$k]['@attributes']['status']) $ans=0; } 
   }
   if($ans) $Q['@attributes']['Score']=10; else $Q['@attributes']['Score']=0; 
   $Q['@attributes']['sTime']=$sTime; $Q['@attributes']['eTime']=$Time;  
   $QQ['Q'][0]=$Q;  FileIO($qfile, $QQ, $root, "Write");
   
   return; 
}
//-------------------
function SubmitAnswers($CD, $sid, $flag='') {$Time=time(); $sTime=$_POST['Info']['Time']; 
   $sADir="$CD/STUDENTS/$sid/".$_POST['Info']['Aid']; $file = $_POST['Info']['id'].'.xml'; 
   $qfile = "$sADir/$file"; 
   if($_POST['Info']['Type']=='reset') { $tmpid = uniqid(); mkdir("$sADir/reset_$tmpid",0777,true); 
       foreach(glob("$sADir/*.xml") as $i=>$v) { $fn=basename($v); rename("$v","$sADir/reset_$tmpid/$fn"); 
	echo "$fn moved to $sADir/reset_$tmpid/$fn";  }
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
function Display_Q($Q,$O='') {$str='';  $desc=$Q['Description']['@value']; $Type=$Q['@attributes']['Type']; $Time=time(); 
    if(isset($O['fileid'])) $k=$O['fileid']; else $k=1; 
    if(isset($O['Aid'])) $Aid=$O['Aid']; else $Aid=1; 
   if($O['disabled']) $disabled='disabled';
   if($O['Submit']) $strS = "<p/><input class='MC-$k' type=button value=Submit onclick=\"QSubmit({id:'$k',Aid:'$Aid', Type:'$Type',Time:$Time})\" $disabled></input>";
   if($O['debug']) {unset($Q['Description']); unset($Q['Solution']); 
     $PHPText='<pre>'.$Q['PHPXML'].'</pre>'; unset($Q['PHPXML']); 
     $debugstr=json_encode($Q) . "$PHPText"; 
   }

   if($Type==1) {  
        if($O['checked'] ||$O['disabled'] ){$value = $Q['A'][0]['@value'] ; }; 
        if($O['Submit'])  return "$desc<p/><input class='MC-$k' type=text id='FillIn-$k' value=$value $disabled></input>$strS <br/>$debugstr";  
        else return "$desc<p/>$strS <br/>$debugstr"; 
   } 

   foreach($Q['A'] as $j=>$w) {$checked=''; if($O['checked']){if($w['@attributes']['status']) $checked='checked'; }
     $val=$w['@value']; if($O['ShowAnswers'] && $Q['AO'][$j]['@attributes']['status']) $color='green'; else $color='none';
     $str .="<br/><span style='background-color: $color;'><input type=checkbox class='MC-$k' value=$j $checked $disabled></input></span>$val";
   }
   return "$desc <p/><u><b>Choices</b></u>$str $strS $debugstr"; 
   
}
function PHP_Q($Q) {$phpxml=$Q['PHPXML'];  $tmpfile = $_SESSION['TDIR']."/".uniqid().".php";file_put_contents("$tmpfile", '<?php ' . $phpxml . ' ?>');
    include("$tmpfile"); unlink("$tmpfile"); return $Q; 
}

//--------------------
//-----------------------
function DisplayScript($flag='', $flag2='') {
echo <<<END
    <script> 
     var old = {}, sTime, eTime;  
     function Show(In) {  var t='.'; if(In.type=='#') t='#';   var o1=$(t+In.id); var d = new Date(); sTime = d.getTime();
       $('.QidDescC').hide(); $("#QSubmitInfoID").html('');  
       if(In.hasOwnProperty("Highlight")) {  if(old.id)  $(old.id).css('background-color','transparent'); 
	  if(o1.css("display")=="none")  $(In.Highlight.id).css('background-color','#ff0');  else $(In.Highlight.id).css('background-color','transparent');  
	  old.id=In.Highlight.id; 
       }
        o1.toggle(); if(In.id2==1) return; 
       var o2=$(t+In.id2); if(o1.css("display")=="none") o2.show(); else o2.hide();
     }
    function QSubmit(In) { var d = new Date(); eTime = d.getTime();
      if(In.Type==1) {var values=$('#FillIn-'+In.id).val(); if(!isNumber(values)) {alert('Entered value must be a number'); return; }
      } else if(In.Type == 'reset') { //--------------
      } else {  var values = $('input:checkbox:checked.MC-'+In.id).map(function () { return this.value;}).get();  if(values.length<1){ alert('Select one or more'); return; }}
       In.values = values; In.eTime = eTime; In.sTime = sTime;  
       $.ajax({url: "Main.php",  type: "POST",  dataType:"html", data: {'attr':$('#AssessmentLocationID').data(), 'id': 'Submit', 'LoadPHP':'Admin/NewAssessment.php', 'Info':In},
          success: function( data ) {//alert(JSON.stringify(In));
            $("#QSubmitInfoID").html(data); $('.MC-'+In.id).attr('disabled',true); 
          },
          error: function(data){alert('Error occurred'); }
       });
   }
   function isNumber(n) { return !isNaN(parseFloat(n)) && isFinite(n); }

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

?>
