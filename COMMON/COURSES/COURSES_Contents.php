<?php

include("$HOME/COMMON/common-old.php");  


$id=$_POST['send']['outputid']; $LoadPHP = $_POST['send']['LoadPHP']; $LID=$_POST['send']['COURSE']; $LDIR="$DATA/COURSES/$LID";  
$O['COURSE']=$LID; $O['LoadPHP']=$LoadPHP; 
$SectionDir = "$DATA/COURSES/$LID/SECTIONS"; if(!is_dir($SectionDir)) {if(!mkdir($SectionDir)) die(htext('Failed to create SectionDir')); }

if(isset($_POST['send']['FileInSection']))  { 
     $file=$_POST['send']['FileInSection']; $ext=end(explode('.',$file)); 
     echo "<button onclick=\"Course_Load_Section_File({'done':1})\">Done</button><br/>"; 
     if($ext=="PDF" || $ext=='pdf') { 
         //echo "<iframe width=100% height=100% src=http://104.197.195.53/$file></iframe>"; 
	echo "<iframe src=\"http://docs.google.com/gview?url=http://104.197.195.53/$file&embedded=true\" style=\"width:100%; height:500px;\" frameborder=0></iframe>"; 
         return; 
     }
     FileIORead($_POST['send']['FileInSection'], $A, $root, 'Read');       if($A['Display']=='Display') echo $A['Desc'];  return;
}
?>
<script>
  function Course_JS(id,ic) { if($('#'+id).prop('checked')) $('.'+ic).prop('checked',true); else $('.'+ic).prop('checked',false); }
  function Course_Load_Section_File(In) {  
    if(In.done==1) { $('#SectionIDTEMP').html(''); $('#SectionTableID').show(); return;}; 
    $('#SectionTableID').hide();  dimag({'outputid':'SectionIDTEMP','LoadPHP':In.LoadPHP, 'FileInSection':In.FileInSection}); 
  }
  function Course_Load_Assessment_JS(In) { 
      if(In.hasOwnProperty('GetAIDByClass'))  {  var ValClass={}, id; 
	$('.'+In.GetAIDByClass).each(function(){id=$(this).prop('id'); if( $('#'+id).prop('checked')) ValClass[id] = $(this).prop('value');  }); 
    }  //alert(JSON.stringify(ValClass)); 
     dimag({'outputid':In.id,'LoadPHP':In.LoadPHP, 'ASSESSMENTS':ValClass, 'HDir':In.HDir,'COURSE':In.COURSE}); 
 }
</script>
<?php 
//echo "<button onclick=\"Course_JS(1,2)\">Test</button>"; 
$SectionFile = "$SectionDir/Section.xml"; 
 $EditSection = sprintf("<a href=%s?LoadPHP=$HOME/COMMON/Playground/Section.php&SectionDir=$SectionDir>$EditButtonText</a><br/>", $_POST['opt']['url']); 
 if(!file_exists($SectionFile)) { SectionDefault($SectionFile); }
if($admin) echo $EditSection; 
Course_DisplaySection("Section.xml", "$LDIR", $id, $O); 

//-------------------
function Section_Assessment_Display($AList, $HDir, $id, $O="") {$stmp2 = ""; $uid=uniqid(); $CourseID = $O['COURSE']; 
  foreach($AList as $k=>$v)  if (!preg_match('/All/',$v['@attributes']['Name']))  { $n=$v['@attributes']['Name'];  $m=htmlspecialchars($n); 
    $nb="<button onclick=\"dimag({'outputid':'middle','LoadPHP':'COURSES/Asssessment_Load.php', 'ASSESSMENTS':{'$k':'$n'}, 'HDir':'$HDir/ASSESSMENT','COURSE':'$CourseID'});  \">$m</button>"; 
    //$nb="<a href=#self onclick=\"dimag({'outputid':'middle','LoadPHP':'COURSES/Asssessment_Load.php', 'ASSESSMENTS':{'$k':'$n'}, 'HDir':'$HDir/ASSESSMENT','COURSE':'$CourseID'}); return false; \">$m</a>";
    $stmp2 .= "<br/>$nb";
    //$stmp2 .= "<br/><input id=$k type=checkbox  class='c-$uid'  value='$n' onclick=\"document.getElementById('$uid-Load').style.display = 'inline';\" />$nb"; 
  }
  //if(sizeof($AList)>1) $stmp2 .= "<br/><input id=id-$uid type=checkbox onclick=\"Course_JS('id-$uid', 'c-$uid'); document.getElementById('$uid-Load').style.display = 'inline';\" />All"; 
  $stmp2 .= "<br/><button id=$uid-Load style='display:none' onclick=\"Course_Load_Assessment_JS({'id':'middle','LoadPHP':'COURSES/Asssessment_Load.php', 'GetAIDByClass':'c-$uid', 'HDir':'$HDir/ASSESSMENT','COURSE':'$CourseID'}); \">Load</button>"; 
  return $stmp2; 
}
function Section_File_Display($AList, $HDir, $id, $O="") {$stmp2 = ""; $uid=uniqid(); $CourseID = $O['COURSE']; $LoadPHP = $O['LoadPHP'];  //pa($O); 
  foreach($AList as $k=>$v)   { $attr=$v['@attributes'];   
    if($attr['View']) { $n=$attr['Name']; $fn=$attr['FileName']; $FileInSection="$HDir/SECTIONS/upload/$fn"; 
        if($attr['Link']) $stmp2 .= "<br/><button onclick=\"Course_Load_Section_File({'LoadPHP':'$LoadPHP', 'FileInSection':'$FileInSection'}); \">$n</button>"; else $stmp2 .= "<br/>$n"; 
     } 
  }
  return $stmp2; 
}
function Section_Upload_Display($AList, $HDir, $id, $O="") { global $HOME, $WebHOME; $UploadDir = "$HDir/SECTIONS/upload";  $WebTEMP = "$WebHOME/TEMP"; 
  $stmp2 = ""; $uid=uniqid(); $CourseID = $O['COURSE']; $LoadPHP = $O['LoadPHP'];  if(!is_dir($WebTEMP)) mkdir($WebTEMP,0777); 
  foreach($AList as $k=>$v)   { $attr=$v['@attributes'];   //pa($attr);
     if($attr['View']) { $n=$attr['Name']; $fn=$attr['FileName']; if(file_exists("$UploadDir/$fn") && !file_exists("$WebTEMP/$fn")) copy("$UploadDir/$fn", "$WebTEMP/$fn"); $PathInfo=pathinfo($fn); 
        if($attr['Link']) { 
            if($PathInfo['extension']=='pdf' || $PathInfo['extension']=='xml') $stmp2 .= "<br/><button onclick=\"Course_Load_Section_File({'LoadPHP':'$LoadPHP', 'FileInSection':'TEMP/$fn'}); \">$n</button>"; 
            else $stmp2 .= "<br/><a href='TEMP/$fn'>$n</a>"; 
        } else {$stmp2 .= "<br/>$n"; }
     } 
  }
  return $stmp2; 
}
//--------------------------
function Course_DisplaySection($file, $HDir, $id, $O="") { 
FileIORead("$HDir/SECTIONS/$file", $A, $root, 'Read'); 
$n=$A['Info']['@attributes']['nRows']; $m=$A['Info']['@attributes']['nCols']; $name=$A['Info']['value']; 
$strName= "$name";

$str="";  //pa($A); 
for($i=0; $i<$n; ++$i) { $stmp=""; 
  for($j=0; $j<$m; ++$j) {   $Sij= $A["x$i"]["y$j"];  $Astr = "";  //pa($Sij); 
       if(sizeof($Sij['A'])>0) $Astr .= Section_Assessment_Display($Sij['A'], $HDir, $id, $O) ; 
       if(sizeof($Sij['F'])>0) $Astr .= Section_File_Display($Sij['F'], $HDir, $id, $O) ; 
       if(sizeof($Sij['U'])>0) $Astr .= Section_Upload_Display($Sij['U'], $HDir, $id, $O) ; 
      $stmp .= sprintf("<td>%s <br/> %s</td>", $Sij['value'], $Astr); 
  }
  $str .= "<tr valign=top>$stmp</tr>"; 
}
echo "<span id=SectionIDTEMP></span><span id=SectionTableID>$strName<table width=100% border=1>$str</table></span>"; 
}
//---------------------
function SectionDefault($SectionFile) {$SNAME=$_SESSION['logged']; 
    // Create Default Value
    $Table['Info']['@attributes']=array('nRows'=>1, 'nCols'=>1, 'Created'=>"By '$SNAME' on " . date('h:i A, M d, Y')); 
    $Table['Info']['value']='Course name';
  
    $irow=0; $Table["x$irow"]['@attributes']=array('Type'=>0); 
      $icol=0; $Table["x$irow"]["y$icol"]['@attributes']=array('Type'=>0); $Table["x$irow"]["y$icol"]['value']=1;
    FileIO($SectionFile, $Table, "SECTIONS", "Write"); 
}
//---------------------
?>
