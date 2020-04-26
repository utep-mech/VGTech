<?php
include("$HOME/COMMON/common-old.php");  
$PData=$_POST['send'];  
list($CourseID, $LoadPHP, $outputid, $logged) = array($PData['COURSE'], $PData['LoadPHP'], $PData['outputid'], $_POST['opt']['logged']); 
$CourseDir="$DATA/COURSES/$CourseID"; 
$RDir="$CourseDir/RAT"; if(!is_dir("$RDir")) mkdir("$RDir",0777,true);
$sRDir="$CourseDir/STUDENTS/$uid/RAT"; if(!is_dir("$sRDir")) mkdir("$sRDir",0777,true);
$id=uniqid(); $rid='RATid'; $Active_RAT="$RDir/Active.txt"; 

$Opt=array('outputid'=>"main-$rid",'LoadPHP'=>$LoadPHP, 'CourseID'=>$CourseID, 'rid'=>$rid,'uid'=>$uid, 'sRDir'=>$sRDir);

if(!isset($PData['flag'])) {echo "<span id='RATinfo' data-outputid='main-$rid' data-loadphp='$LoadPHP' data-course='$CourseID' style='display:none;'></span>"; RAT_JS(); }
if($PData['flag']=='SubmitA') { $aRDir="$RDir/".$PData['id']; $asRDir="$sRDir/".$PData['id']; 
  $PData['f']="$aRDir/RAT.json"; $PData['sf']="$asRDir/RAT.json";;
  if(!is_dir("$asRDir")) mkdir("$asRDir",0777,true); 
  //pa(glob("$CourseDir/STUDENTS/*/RAT/".$PData['id']));
  if(!file_exists($Active_RAT)) {echo "Sorry, no active RAT at this time!";  return; }
  RAT_IO($PData, $admin);  
  return; 
}

if(!isset($PData['flag'])) { // Initial display
   if(!is_dir("$RDir")) mkdir("$RDir",0777,true); //if(!is_dir("$sRDir")) mkdir("$sRDir",0777,true);
   echo "<button onclick=\"dimag({'outputid':'admin-$rid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'List'}); \" >List</button>";
   echo "<button onclick=\" dimag({'outputid':'$rid-admin','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'SubmittedList'}); \">Submitted</button>"; 

   echo "<span id='RATinfo' data-rid='$rid' data-outputid='main-$rid' data-outputid3='msg-$rid' data-outputid2='admin-$rid' data-loadphp='$LoadPHP' data-course='$CourseID' style='display:none;'></span>"; 
   echo "<span id='RAT-infoS'></span><span id='$rid-admin'></span>"; 

   echo "<table width=100% border=0><tr><td valign=top><span id=admin-$rid></span></td><td valign=top><span id=main-$rid></span><div id=msg-$rid></div></td></tr></table>";
   RAT_JS();
   //echo "<button onclick=\" RAT_Start({'outputid':'$rid-admin','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Auto'},this); \">Start</button>"; 

   return; 
} 

//----------------STUDENT--------------
if(file_exists($Active_RAT)) {$Active_RATid=file_get_contents("$Active_RAT"); $Opt['Active_RATid']=$Active_RATid; } else $Active_RATid=$PData['id'];
if($PData['flag']=='Load') { $aRDir="$RDir/$Active_RATid"; //$sRDir="$aRDir/STUDENTS/$uid"; 
  $f="$aRDir/RAT.json";$sf="$sRDir/$Active_RATid/RAT.json";  $PData['f']=$f; $PData['sf']=$sf; $PData['id']="$Active_RATid"; 
 RAT_IO($PData, $admin);
 return; 

}
if($PData['flag']=='List') { RAT_List($RDir, $Opt,$admin); }
if(!$admin) return; 
//---------------STUDENT stops here----

if($PData['flag']=='SubmittedList') {RAT_Auto($Opt); }
function RAT_Auto($Opt) { echo 'h'; 
}

if($PData['flag']=='New') { mkdir("$RDir/RAT_$id",0777,true);  return; }
if($PData['flag']=='SaveRaw') { $f=$PData['f']; if(file_exists($f)) file_put_contents($f, $PData['RawVal']);  return; }
//if($PData['flag']=='Edit') { $PData['f']=sprintf("$RDir/%s/RAT.json",$PData['id']); RAT_IO($PData, $admin);  return; }
if($PData['flag']=='EditRaw') { $tmpid=$PData['id'];  $edir="$RDir/$tmpid"; $f="$edir/RAT.json"; if(is_dir($edir)) EditRAT($f, $PData); 
 echo "<br/><button onclick=\"RATSaveRaw({'outputid':'msg-$rid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'SaveRaw', 'TAid':'EditTA-$tmpid','f':'$f'}); \">SaveTA</button><br/>";
 return; 
}
if($PData['flag']=='Activate') {file_put_contents("$Active_RAT", $PData['id']);  return; }
if($PData['flag']=='DeActivate') {if(file_exists("$Active_RAT")) { unlink("$Active_RAT"); echo "Removed '$Active_RAT' to deactivate all"; }; return; }


//---------------------
function RAT_List($RDir, $Opt,$admin=0, $flag='List') { $s=''; $sRDir=$Opt['sRDir']; 
  $outputid=$Opt['outputid']; $LoadPHP=$Opt['LoadPHP']; $CourseID=$Opt['CourseID']; $rid=$Opt['rid']; $uid=$Opt['uid'];
  foreach(glob("$RDir/RAT_?????????????") as $d) { $i=basename($d); $f="$d/RAT.json"; $n=$i;  $sf="$sRDir/$i/RAT.json";
    if(file_exists($f)) { $R=json_decode(file_get_contents($f), true); if(isset($R['Attr']['Name'])) $n=$R['Attr']['Name']; }
   if(!$admin) { if(!file_exists($sf)) $disabled='disabled'; } 
   if(isset($Opt['Active_RATid'])) $disabled='disabled';
   if("$i"==$Opt['Active_RATid']) {$n="<b>$n</b>"; $disabled=''; }
   $s .="<button onclick=\"dimag({'outputid':'main-$rid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Load', 'id':'$i'}); \" $disabled>$n</button>";
   
   if($admin) {
     $s .= "<button onclick=\"dimag({'outputid':'admin-$rid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Activate', 'id':'$i'}); \">Activate</button>";
     $s .= "<button onclick=\"dimag({'outputid':'main-$rid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Edit', 'id':'$i'}); \">Edit</button>";
     $s .= "<button onclick=\"dimag({'outputid':'main-$rid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'EditRaw', 'id':'$i'}); \">EditRaw</button>";
   }
   $s .="<br/>"; 
  } 
  echo $s; 
  if(!$admin) return; 
  echo "<button onclick=\" \$('#newRAT').show(); \" >New RAT</button>";
  echo "<button id=newRAT style='display:none;' onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'New'}); \" >Yes</button>";
  echo "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'DeActivate'}); \" >Deactive</button>";
}
//---------------------
function RAT_IO($PData,$admin=0,$flag='Load') { $f=$PData['f']; $sf=$PData['sf']; $id=$PData['id']; $submit=0; $sCounter=0; $sClass='cSubmitA'; $sflag=0;
  if($PData['Reset'] || $PData['ReTry']==1) { echo "Deleted old $sf"; unlink($sf); }
  if(file_exists($sf) && !$admin) {$f=$sf; $disabled='disabled'; $sflag=1; }
  if($PData['flag'] == 'SubmitA') {$submit=1; $ValS=$PData['SubmitV']; $disabled='disabled'; }
  $R=json_decode(file_get_contents($f), true);   
  
  if($R['Attr']['nAttempts']=='Unlimited' ) $disabled=' '; 

  if($submit && isset($ValS["$sClass$sCounter"]))  {$R['Desc']['Val']=$ValS["$sClass$sCounter"]; }; $desc=$R['Desc']['Val']; 
  $s= "<span id=$sClass$sCounter class=$sClass data-stype='html'>$desc</span>";  $sCounter++; 
 
  //if($R['Attr']['Type']=='Fill-In') { $s .= "<br/><input id=SubmitA class=cSubmitA type=text></input>";   }
  if($R['Attr']['Type']=='MultipleQ') { 
    foreach($R['Q'] as $k=>$v) { $a=$v['Attr']; $kp1=($k+1); $ans=''; 

     if($submit && isset($ValS["$sClass$sCounter"]) )  {$R['Q'][$k]['Desc']['Val']=$ValS["$sClass$sCounter"]; }; $desc=$R['Q'][$k]['Desc']['Val']; 

     $ss = "<span id=$sClass$sCounter class=$sClass data-stype='html'>$desc</span>";  $sCounter++; 
     if($submit) {
          if(!$admin && !isset($R['Q'][$k]['AnsO']) ) $R['Q'][$k]['AnsO']=$R['Q'][$k]['Ans']; // Store the original values for the answer
          $R['Q'][$k]['Ans']['Val']=$ValS["$sClass$sCounter"];
          if(!$admin && $R['Q'][$k]['Attr']['Validate']) { 
               $Points=10; $R['Q'][$k]['Attr']['Score']=0; $val=$R['Q'][$k]['Ans']['Val']; $valO=$R['Q'][$k]['AnsO']['Val'];  
               if(isset($R['Q'][$k]['Attr']['Points'])) $Points=$R['Q'][$k]['Attr']['Points']; 
               if(abs($val-$valO)<1E-6) {$R['Q'][$k]['Attr']['Score'] = $Points; }
          }; 
          
     }
     if($admin || $submit || $sflag) $ans=$R['Q'][$k]['Ans']['Val']; else $ans=''; 
     //if(isset($a['Enforce'])) $enforce=$a['Enforce']; else $enforce=0; 
     if($a['Type']=='Text') $s .= "<br/>(Q$kp1) $ss<br/><textarea id=$sClass$sCounter class=$sClass cols=50 rows=3>$ans</textarea>";
     
     if($a['Type']=='Code') { $ans=$R['Q'][$k]['Ans']['Val']; 
         if($admin) $s .= "<br/>(Q$kp1) $ss<br/><textarea id=$sClass$sCounter class=$sClass cols=50 rows=3>$ans</textarea>";
         if(!$admin) $s .= "<br/>(Q$kp1) $ss<br/><span id=$sClass$sCounter class=$sClass data-stype='html'><pre>$ans</pre></span>";
     }

     if($a['Type']=='Float') { //pa($v); 
         if(isset($R['Q'][$k]['Attr']['Score'])) { $scolor='#FFC300'; if($R['Q'][$k]['Attr']['Score']>0) $scolor='#BCFF00'; }
         $s .= "<br/>(Q$kp1) $ss<br/><input data-type=float id=$sClass$sCounter class=$sClass style='background-color:$scolor' type=text value='$ans'></input>";
     }
     $sCounter++;
    }   
  }
  if($submit) { $of=$sf; if($admin) $of=$f; echo htext("Submitted at ".date("Y-m-d h:i:sa")).'<br/>'; file_put_contents($of, json_encode($R)); }
  echo $s; 
  echo "<br/><button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'SubmitA', 'Class':'cSubmitA', 'id':'$id','f':'$f', 'sf':'$sf'}); \" $disabled>Submit</button>";
  //echo "<button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'Load', 'ReTry':1, 'id':'$id', 'Class':'cSubmitA'}); \">ReTry</button>";
  
 // echo "<button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'SubmitA', 'Class':'cSubmitA', 'id':'$id'}); \">Try again</button>";
 // echo "<button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'SubmitA', 'Class':'cSubmitA', 'id':'$id'}); \">Improve</button>";

  if($admin) { 
    echo "<button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'Load', 'Reset':1, 'id':'$id', 'Class':'cSubmitA'}); \">Reset</button>";
    echo "<button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'Load', 'Edit':1, 'id':'$id', 'Class':'cSubmitA'}); \">Edit</button>";
   }

  if($R['Attr']['debug']) pa($R); 
  return; 
}
//---------------------
function RAT_IO2($PData,$admin=0,$flag='Load') { $f=$PData['f']; $sf=$PData['sf']; $id=$PData['id']; $submit=0; $sCounter=0; $sClass='cSubmitA'; 
  if($PData['Reset']) unlink($sf); 
  if(file_exists($sf) && !$admin) {$f=$sf; $disabled='disabled'; }
  if($PData['flag'] == 'SubmitA') {$submit=1; $ValS=$PData['SubmitV']; $disabled='disabled'; }
  $R=json_decode(file_get_contents($f), true);  
  
  if($submit && isset($ValS["$sClass$sCounter"]))  {$R['Desc']['Val']=$ValS["$sClass$sCounter"]; }; $desc=$R['Desc']['Val']; 
  $s= "<span id=$sClass$sCounter class=$sClass data-stype='html'>$desc</span>";  $sCounter++; 
 
  //if($R['Attr']['Type']=='Fill-In') { $s .= "<br/><input id=SubmitA class=cSubmitA type=text></input>";   }
  if($R['Attr']['Type']=='MultipleQ') { 
    foreach($R['Q'] as $k=>$v) { $a=$v['Attr']; $kp1=($k+1); $ans=''; 

     if($submit && isset($ValS["$sClass$sCounter"]) )  {$R['Q'][$k]['Desc']['Val']=$ValS["$sClass$sCounter"]; }; $desc=$R['Q'][$k]['Desc']['Val']; 

     $ss = "<span id=$sClass$sCounter class=$sClass data-stype='html'>$desc</span>";  $sCounter++; 
     if($submit) $R['Q'][$k]['Ans']['Val']=$ValS["$sClass$sCounter"]; 
     if($admin || ($disabled=='disabled')) $ans=$R['Q'][$k]['Ans']['Val']; else $ans=''; 
     //if(isset($a['Enforce'])) $enforce=$a['Enforce']; else $enforce=0; 
     if($a['Type']=='Text') $s .= "<br/>(Q$kp1) $ss<br/><textarea id=$sClass$sCounter class=$sClass cols=50 rows=3 $disabled>$ans</textarea>";
     if($a['Type']=='Float') $s .= "<br/>(Q$kp1) $ss<br/><input data-type=float id=$sClass$sCounter class=$sClass type=text value='$ans' $disabled></input>";
     $sCounter++;
    }   
  }
  if($submit) { $of=$sf; if($admin) $of=$f; echo htext("Submitted at ".date("Y-m-d h:i:sa")).'<br/>'; file_put_contents($of, json_encode($R)); }
  echo $s; 
  echo "<br/><button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'SubmitA', 'Class':'cSubmitA', 'id':'$id','f':'$f', 'sf':'$sf'}); \" $disabled>Submit</button>";
 // echo "<button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'SubmitA', 'Class':'cSubmitA', 'id':'$id'}); \">Try again</button>";
 // echo "<button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'SubmitA', 'Class':'cSubmitA', 'id':'$id'}); \">Improve</button>";

  if($admin) { 
    echo "<button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'Load', 'Reset':1, 'id':'$id', 'Class':'cSubmitA'}); \">Reset</button>";
    echo "<button onclick=\"JS_SubmitRAT({'dInfoFrom':'#RATinfo','flag':'Load', 'Edit':1, 'id':'$id', 'Class':'cSubmitA'}); \">Edit</button>";
   }

  if($R['Attr']['debug']) pa($R); 
  return; 
}
//---------------------

function RAT_JS() { 
echo <<<_END

<script>

function JS_SubmitRAT(In){    var v={}, d=$(In.dInfoFrom).data(); In.outputid=d.outputid; In.LoadPHP=d.loadphp; In.COURSE=d.course; 
  $('.'+In.Class).each(function(){ var id=$(this).prop('id'); var dL=$('#'+id).data(); 
   if(!dL.ignore) { 
      if(dL.stype=='html') {v[id]=$(this).html(); if(In.Edit) $('#'+id).attr('contenteditable',true); 
      }  else  { v[id]=$(this).val(); if(In.Edit) { $('#'+id).prop('disabled',false); }  
      }
   }
  });  
  if(In.Edit) return; 
  In.SubmitV = v; 
  //alert(JSON.stringify(In)); 
  dimag(In);
}

var myInterval=null, ii=0;
function RAT_Start(In,e) { 
  //var v={}, d=$(In.dInfoFrom).data(); In.outputid=d.outputid; In.LoadPHP=d.loadphp; In.COURSE=d.course; alert(JSON.stringify(d)); 
  if(myInterval ) { 
    clearInterval(myInterval); myInterval=null; $(e).text('Start');  $(e).css('background-color','');
  } else { 
    myInterval = setInterval(function(){RAT_Update({In})}, 3000);  $(e).text('Stop'); $(e).css('background-color','green');
  }
}
function RAT_Update(In) { dimag(In); 
  //var v={}, d=$(In.dInfoFrom).data(); In.outputid=d.outputid; In.LoadPHP=d.loadphp; In.COURSE=d.course;   var rid=d.rid; 
  ii++; $('#RAT-infoS').html(JSON.stringify(In)); 
}
function RATSaveRaw(In) { In.RawVal = $('#'+In.TAid).val(); dimag(In); }		
</script>
_END;

  return; 
}

//---------------------
function EditRAT($rf, $PData) { $id=$PData['id']; if(!file_exists($rf)) file_put_contents($rf, '{}'); 
  $s=file_get_contents($rf);
  $R=json_decode($s, true);  $R['id']=$id;  
  if($R['Attr']['Print']=='Pretty') $s=json_encode($R, JSON_PRETTY_PRINT); else $s=json_encode($R); 
  //echo "Desc:<br/><textarea id=Editdesc-$id class=Edit-$id>$s</textarea>"; 
  echo "<br/><textarea id=EditTA-$id cols=100 rows=20>$s</textarea>"; 
  return; 
}
//echo "$RDir, $sRDir"; pa(scandir("$RDir"));pa(scandir("$sRDir"));

return; 

?>
