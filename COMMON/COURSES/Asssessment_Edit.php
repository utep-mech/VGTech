<?php
include("$HOME/COMMON/common-old.php");  $PData=$_POST['send']; 
//$ExcludeKeys = array("CreatedOn", "CreatedBy", "ModifiedBy", "ModifiedOn","Questions"); 

$outputid=$PData['outputid']; $LoadPHP =  $PData['LoadPHP'];  $CourseID=$PData['COURSE']; $AID=$PData['AID']; 

$idtmp = "$outpudid-Edit"; $CDir="$DATA/COURSES/$CourseID";  $O['TEMP']="$TEMP"; $O['COURSE']=$CourseID; 

$AFile="$CDir/ASSESSMENT/$AID/AInfo.json"; $AInfo=json_decode(file_get_contents($AFile), true); 
$AEdit = array("Name", "StartTime", "EndTime", "Status", "Description", "File", "DAManagement", "CAManagement",  "TimeLimit", "MaxAttempts", "nQuestionsAllowed", "Randomize", "ShowAnswers", "ShowSolution", "ShowComment", "ErrataQ"); 
$AToggle = array("Status", "Description",  "DAManagement", "CAManagement", "MaxAttempts", "nQuestionsAllowed", "Randomize", "TimeLimit", "ShowComment"); 

if($PData['Save'] == "Update") { $uqid=$PData['GetValClassID']; $SVal=$PData['ValClass']; echo "$AFile has been updated"; 
   foreach($AEdit as $k=>$v) {
       if(isset($SVal["$uqid-$v"])) { if($v=='ErrataQ' && $SVal["$uqid-$v"] != '') $AInfo[$v] = json_decode($SVal["$uqid-$v"], true); else $AInfo[$v] = $SVal["$uqid-$v"];  }
   }
   file_put_contents($AFile, json_encode($AInfo)); 
   return; 
}


?>
<script>
function ErrataQ(In) { var vo= $('#'+In.id); var v={}; var vs=vo.val(); if(vs=='') vs="[]"; var vv= JSON.parse(vs);  //alert( $('#C-'+In.val).is(':checked')); 
   if(!(vv.indexOf(In.val) > -1)) vv.push(In.val);
  if(!$('#C-'+In.val).is(':checked')) vv.splice(vv.indexOf(In.val), 1);
  //if( $(this).attr('checked')) alert(In.val); 
   vo.val(JSON.stringify(vv));   // alert(JSON.stringify(vv)); //vo[0]=In.val; 

}
</script>
<?php



$str = ""; $strT = ""; $uqid=uniqid();  $now = time();  $tN= date('Y-m-d:H-i',$now); 
$tIC1= sprintf("%s:10-20", date('Y-m-d',$now)); $tIC2 = sprintf("%s:10-50", date('Y-m-d',$now)); $tIC3 = sprintf("%s:23-59", date('Y-m-d',$now)); 


foreach($AEdit as $k=>$v) { $s = '';  
   $t20= date('Y-m-d:H-i',$now + 20*60); $t30= date('Y-m-d:H-i',$now + 30*60); $t1day= date('Y-m-d:H-i',$now + 24*60*60); 
   if($v=='ErrataQ' && $AInfo[$v] !='') $w= json_encode($AInfo[$v]); else $w=$AInfo[$v]; 
   $s .= sprintf("<input id='$uqid-$v' class=$uqid value='%s' />",$w);
   if($v=='StartTime') {      
      $s .= " <input name='$uqid-S-$v' type='radio' onclick=\" \$('#$uqid-$v').val('$tN'); \"   />now";   
      $s .= " <input name='$uqid-S-$v' type='radio' onclick=\" \$('#$uqid-$v').val('$tIC1'); \"   />Quiz";   
      $s .= " <input name='$uqid-S-$v' type='radio' onclick=\" \$('#$uqid-$v').val('$tIC2'); \"   />Home";   
    }
   if($v=='EndTime') {   
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('$t20'); \"  />+20min | ";      
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('$t30'); \"  />+30min";      
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('$t1day'); \"  />1day";   
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('$tIC2'); \"   />Quiz";   
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('$tIC3'); \"   />Home";   
      //$s .= " <input name='$uqid-$v' type='date' onclick=\" \$('#$uqid-$v').val('$t1day'); \"  />1day";                 
  }
   if($v=='File') {   $c1=''; $c2=''; if($AInfo[$v]=='LoadFromQDatabase') $c1 = 'checked'; else $c2='checked'; 
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('LoadFromQDatabase'); \"  $c1 />LoadFromQDatabase | ";      
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('QList.xml'); \"  $c2 />QList.xml";        
  }
 if($v=='ShowAnswers' || $v=='ShowSolution' || $v=='ShowComment') {  
     $c1=''; $c2=''; $c3='';  if($AInfo[$v]=='-1') $c1 = 'checked'; elseif($AInfo[$v]=='0') $c2='checked'; else $c3='checked'; 
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('-1'); \"  $c1 />Never | ";      
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('0'); \"  $c2 />Always | ";     
      $s .= " <input name='$uqid-$v' type='radio' onclick=\" \$('#$uqid-$v').val('1'); \"  $c3 />After these many attempts";           
  }
 if($v=='ErrataQ') {  $EQstr = ''; 
     foreach($AInfo['Questions'] as $kk=>$vv) { $kp1=$kk+1; $checked=''; if(in_array($vv,$AInfo['ErrataQ'])) $checked='checked';  
       $EQstr .= " <input id='C-$vv' type=checkbox onclick=\" ErrataQ({'id':'$uqid-$v', 'val':'$vv'}); \"  $checked /> Q$kp1 | "; 
     }   
     $s .= $EQstr;       
  } 
  if(in_array($v, $AToggle))    $strT .="<tr><td>$v</td><td>$s</td></tr>"; else    $str .= "<tr><td>$v</td><td>$s</td></tr>"; 
}
echo "<table border=1>$str</table>" .  togglePHP("<table border=1>$strT</table>", uniqid(),'+','str', "More options"); 
echo "<br/><button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'AID':'$AID','COURSE':'$CourseID', 'GetValClassID':'$uqid', 'Save':'Update'}); \">Save</button>"; 



return; 

?>


