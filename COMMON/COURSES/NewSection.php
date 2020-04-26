<?php
$CourseID =$_POST['send']['COURSE'];   if(isset($_GET['CourseID'])) $CourseID = $_GET['CourseID']; 
$LoadPHP=$_POST['send']['LoadPHP'];   if(isset($_GET['LoadPHP'])) $LoadPHP= $_GET['LoadPHP']; 
if(isset($_GET['HOME'])) $HOME= $_GET['HOME']; 
if(isset($_GET['DATA'])) $DATA= $_GET['DATA']; 
$SectionDir="$DATA/COURSES/$CourseID/SECTIONS"; if(isset($_GET['SectionFile'])) $SectionDir= dirname($_GET['SectionFile']); 

include("$HOME/COMMON/common-old.php");    
$L=sprintf("%s?LoadPHP=%s&HOME=%s&DATA=%s",$_SERVER['PHP_SELF'],$LoadPHP,$HOME,$DATA); 
if($_SESSION['priv']=='Admin') $admin=1;
 LoadJS(); 
  if(isset($_GET['EditFile']) && !$admin) exit('only admin is allowed');
//$maxSections = 10; $maxFile = 2; 
list($maxSections, $maxRows, $maxCols, $maxFile, $maxFileSize) = array(10, 20, 10, 4, 20000); 
$FileExtensionAllowed=array('cas', 'dat', 'jpg', 'gif', 'pdf', 'txt', 'avi', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx','html');
$ChoiceArray=array('Show','Hide','Cross','3-Column', '3-Column-Highlighted', 'Highlight'); 
if(!isset($HOME)) exit("HOME is not set"); 

if (!is_dir($SectionDir)) {if (!mkdir("$SectionDir",0777,true)) die("<p><font color='red'>Failed to create folders '$SectionDir'</font>");}

echo "<button id=AssessmentDoneID style='display:none;' onclick=\"Assessment(0,0,0,'Done')\">Done</button>";
//if($admin) echo "<input type=checkbox id=rawdisplay onclick=\"AddtoDataObj('rawdisplay','flag','RawDisplay')\">Raw</button>";
echo "<span id=AssessmentLocationID style='display:none;' data-home='$HOME' data-coursedir='$CourseID'></span>";

$SectionFile = "$SectionDir/Section.xml";
$root="SECTIONS";

/*
if($CSID=='vkumar') { echo "<a href=$L&SECTIONS&EnterGrade>Enter Grade</a>"; 
   if(isset($_GET['EnterGrade'])) { include($_SESSION['HDIR']."/COMMON/Admin/EnterGrade.php"); exit(); }
}

  $Sec0['Name']='Course Name';
  $Sec0['Sec']['x0']['@attributes']=array("Name"=>'Name', "SecDir"=>'x0'); 
  $Sec0['Sec']['x0']['SubSec']['y0']['@attributes']=array("Name"=>'Name', "SubSecDir"=>'y0', 
    'CreatedBy'=>"$SNAME", 'ModifiedBy'=> "$SNAME", 
    'CreatedOn'=>date('h:i A, M d, Y'), 'ModifiedOn'=>date('h:i A, M d, Y')); 
*/
  if($admin) echo "<div id='canvasEditFrame'></div>"; //Canvas Frame Editing Goes here
  //-----------[
  if(isset($_GET['SubsectionFile']) && isset($_GET['EditFile'])) {SectionEditFile($SectionFile,$_GET['SubsectionFile'],$L,''); }
  if(isset($_GET['SubsectionFile']) && isset($_GET['ViewFile'])) {SectionViewFile($SectionFile,$_GET['SubsectionFile'],$L,''); }

  if(!file_exists($SectionFile)) { 
    // Create Default Value
    $Table['Info']['@attributes']=array('nRows'=>1, 'nCols'=>1, 'Created'=>"By '$SNAME' on " . date('h:i A, M d, Y')); 
    $Table['Info']['value']='Course name';
  
    $irow=0; $Table["x$irow"]['@attributes']=array('Type'=>0); 
      $icol=0; $Table["x$irow"]["y$icol"]['@attributes']=array('Type'=>0); $Table["x$irow"]["y$icol"]['value']=1;
    if($admin) FileIO($SectionFile, $Table, $root, "Write"); 
  } else { 
    FileIORead($SectionFile, $Table, $root, "Read"); 
    // Remove @value; changed to value
    if(isset($Table['Info']['@value']))  { $Table['Info']['value']=$Table['Info']['@value']; unset($Table['Info']['@value']); }

    $nCols=$Table['Info']['@attributes']['nCols']; $nRows=$Table['Info']['@attributes']['nRows'];

   		if($admin && !isset($_SESSION['StudentView'])) { 
    if($_GET['SECTIONS']=="AddRows" && $nRows<$maxRows)  { $Table["x$nRows"]= $Table["x0"]; 
      $nRows +=1; $Table['Info']['@attributes']['nRows']=$nRows; FileIO($SectionFile, $Table, $root, "Write"); 
    } elseif($_GET['SECTIONS']=="AddCols" && $nCols<$maxCols)  { for($i=0; $i<$nRows; $i++) {$Table["x$i"]["y$nCols"]=$Table["x$i"]["y0"]; } 
      $nCols +=1; $Table['Info']['@attributes']['nCols']=$nCols; FileIO($SectionFile, $Table, $root, "Write"); 
    } elseif($_GET['SECTIONS']=="DelRows" && $nRows>1)  { $nRows -=1; $Table['Info']['@attributes']['nRows']=$nRows;  
        unset($Table["x$nRows"]); FileIO($SectionFile, $Table, $root, "Write"); 
    } elseif($_GET['SECTIONS']=="DelCols" && $nCols>1)  { $nCols -=1; $Table['Info']['@attributes']['nCols']=$nCols;
        for($i=0; $i<$nRows; $i++) {unset($Table["x$i"]["y$nCols"]); } 
	FileIO($SectionFile, $Table, $root, "Write"); 
    } 
		} 
  } 
  if(strpos($_GET['SECTIONS'], 'Edit') !==false) SectionEditFunction($SectionFile,$L); 
  //Display Table
  $TString = $Table['Info']['value']; 
  if($admin && !isset($_SESSION['StudentView'])) $TString .= "<a href=$L&SECTIONS=EditInfo><img src='/images/Edit16x16.png' /></a>";
  foreach($Table['Info']['U'] as $k=>$vv) { if(!isset($vv['@attributes']['View'])) $vv['@attributes']['View']=1; 
    if($vv['@attributes']['View']) { 
      if($vv['@attributes']['Link']) $TString .= sprintf("<br/><a href=$WTEMP/%s>%s</a>",$vv['@attributes']['FileName'],$vv['@attributes']['Name']);
      else $TString .= sprintf("<br/>%s",$vv['@attributes']['Name']);
    } 
  }
  foreach($Table['Info']['F'] as $k=>$vv) { if(!isset($vv['@attributes']['View'])) $vv['@attributes']['View']=1; 
    if($vv['@attributes']['View']) { 
      if($vv['@attributes']['Link']) $TString .= sprintf("<br/><a href=$L&SECTIONS&SubsectionFile=$k&ViewFile>%s</a>",$vv['@attributes']['Name']);
      else $TString .= sprintf("<br/>%s",$vv['@attributes']['Name']);
    } 
  }

  $RowSpan=0; 
  $TString .= "<table id=SectionTableID border=1 width=100%>"; 
  for($i=0; $i<$nRows; $i++) {  $bg=$Table["x$i"]["y0"]['@attributes']['RowColor'];  
     if($bg != '') $TString .= "<tr bgcolor='$bg'>"; else $TString .='<tr>'; 
     for($j=0; $j<$nCols; $j++) { if($RowSpan>1 && $i==$iRow2 && $j==$iCol) $RowSpan=0; 
        if($Table["x$i"]["y$j"]['@attributes']["RowSpan"]>1) {$RowSpan=$Table["x$i"]["y$j"]['@attributes']["RowSpan"]; $iRow1=$i; $iRow2=$i + $RowSpan; $iCol=$j; }

        //echo "[$i, $j] $RowSpan=4;  $iRow1=1; $iRow2=$iRow1 + $RowSpan; $iCol=1;<br>";
	//$RowSpan=4;  $iRow1=1; $iRow2=$iRow1 + $RowSpan; $iCol=1;
	//if($i==1 && $j==1) { $TString .= "<td rowspan=4>"; } else  { if($i>1 && $j==1) continue; else { $TString .= "<td>"; } } 
        if($RowSpan>0) {
	  if($i>=$iRow1 && $i <$iRow2) { 
	    if($i==$iRow1) { if($j == $iCol) $TString .= "<td rowspan=$RowSpan>";  else $TString .= "<td>"; }
	    else { if($j == $iCol) continue;  else $TString .= "<td>"; }
	  } else  { $TString .= "<td>"; } 
	} else  { $TString .= "<td>"; } 

	$TString .= $Table["x$i"]["y$j"]['value']; 

	if(isset($Table["x$i"]["y$j"]['A'])) { 
          foreach($Table["x$i"]["y$j"]['A'] as $k=>$vv) {
	    $TString .= sprintf("<br/><input type=checkbox id=$k class=Class-$i-$j onclick=\"Assessment($i,$j,'$k','0')\">%s</input>",$vv['@attributes']['Name']);
	    //$TString .= sprintf("<br/><a href=%s&&ASSESSMENT=%s>%s</a>",$L,str_replace('A_','',$k),$vv['@attributes']['Name']);
	  } 
	  $TString .= sprintf("<p/><input id=All-$i-$j type=checkbox onclick=\"Assessment($i,$j,0,'All')\">All</input>");
	  $TString .= sprintf("<br/><button id=B-$i-$j style='display:none;' onclick=\"Assessment($i,$j,0,'Load')\">Load</button>");
        }
        foreach($Table["x$i"]["y$j"]['U'] as $k=>$vv) { if(!isset($vv['@attributes']['View'])) $vv['@attributes']['View']=1; 
    	  if($vv['@attributes']['View']) { 
	    if($vv['@attributes']['Link']) $TString .= sprintf("<br/><a href=$WTEMP/%s>%s</a>",$vv['@attributes']['FileName'],$vv['@attributes']['Name']);
	    else $TString .= sprintf("<br/>%s",$vv['@attributes']['Name']);
    	  } 
	}
        foreach($Table["x$i"]["y$j"]['F'] as $k=>$vv) { if(!isset($vv['@attributes']['View'])) $vv['@attributes']['View']=1; 
          if($vv['@attributes']['View']) { if($vv['@attributes']['Link']) 
		$TString .= sprintf("<br/><a href=$L&SECTIONS&SubsectionFile=$k&ViewFile>%s</a>",$vv['@attributes']['Name']);
            else $TString .= sprintf("<br/>%s",$vv['@attributes']['Name']);
          } 
        }
   		if($admin && !isset($_SESSION['StudentView'])) { 
        $n="SectionAddFID-x$i-y$j"; $mode='table';
	$TString .= "<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
	$TString .= "<div id='Hidden$n' name='Hidden$n' style='display:none;'><hr>"; 
        $TString .= "<a href=$L&SECTIONS=Editx$i-y$j><img src='/images/Edit16x16.png' /></a>";
	$TString .= " | Add Assessment | Upload File | Create File"; 
	$TString .= "</div>"; 
		}
	$TString .= "</td>"; 
     } 
     $TString .= "</tr>"; 
  }
  $TString .= "</table>";  
  echo "<hr><span id=SectionIDMain>$TString";
   		if($admin) { 
  //echo "<textarea><table border=1 width=100%>$ttt</table></textarea>";
   			if(isset($_SESSION['StudentView'])) { exit("Student View"); 
   			} else {
  $strEdit = "[<a href=$L&SECTIONS=AddRows>Add Row</a> | <a href=$L&SECTIONS=AddCols>Add Col</a> | "; 
  $strEdit .= "<a href=$L&SECTIONS=DelRows>Delete Row</a> | <a href=$L&SECTIONS=DelCols>Delete Col</a>"; 
  //$strEdit .= " | <a href=$L&SECTIONS&StudentView>Student View</a><br>"; 
  fp('HideUnhide',$strEdit,'AddDelRowColSectionsID','Section Options'); 
   			} 
		}

  //FileIO($SectionFile, $Table, $root, "DisplayA2XML"); 
  echo "</span>";
return;
  //-----------]

//$Sec=$Sec0; FileIO($SectionFile, $Sec, $root, "Write"); FileIO($SectionFile, $Sec, $root, "DisplayA2XML"); 
  if($admin) {
  echo "<div id='canvasEditFrame'></div>"; //Canvas Frame Editing Goes here
  if(!isset($_SESSION['EditS'])) $_SESSION['EditS']="Off";
  if($_GET['SECTIONS']=="EditOn") $_SESSION['EditS']="On";
  if($_GET['SECTIONS']=="EditOff") $_SESSION['EditS']="Off";
   
   echo " <br> " . htext('Editing',1,"#00ff00") . " <a href='$L&SECTIONS=EditOn'>" . fpRv("B","Enable") . "</a>";
   echo " | <a href='$L&SECTIONS=EditOff'>".fpRv("B","Disable") ."</a>";
  }
//FileIO($SectionFile, $Sec0, $root, "Write"); 


if($_GET['SECTIONS']=="ViewFile") { 
  $file = sprintf("%s/%s.xml",$SectionDir,$_GET['fn']);  
  if(file_exists($file)) {
     FileIORead($file, $FSec, $root, "Read"); 
     echo "<p>" . $FSec['Desc'];

     if($FSec['Type']=='Learning-Styles') { include($_SESSION['HDIR']."/COMMON/Admin/LearningStylesFormProcessing.php"); }
     if($FSec['Type']=='Survey-Form') { include($_SESSION['HDIR']."/COMMON/Admin/Survey_Form.php"); }
  } 
  exit();
}


if(file_exists("$SectionFile") && ($_SESSION['EditS'] !="On" || !$admin)) { 
   FileIORead($SectionFile, $Sec, $root, "ReadQ"); 
   echo "<table border=1 width=100%><tr><th colspan=3>".$Sec['Name']."</th></tr>";
   foreach($Sec['Sec'] as $i=>$w) { $n='Name';
       $v= $Sec['Sec'][$i]['@attributes'][$n]; 
       $v2= $Sec['Sec'][$i]['@attributes']['Material']; 
       $v3= $Sec['Sec'][$i]['@attributes']['Assess']; 

       $Status=$Sec['Sec'][$i]['@attributes']['Status']; 
       $color='#ffffff'; 
       if($Status=='Highlight') $color='#e3ffe3';
       if($Status=='3-Column-Highlighted') $color='#e3e3e3';

       //-------------------[
       $it=$i;  $tt="";
       foreach($Sec['File'][$it] as $jj=>$uu) { $vtmp=$uu['@attributes'];
	$tt=sprintf("<a href=$L&SECTIONS=ViewFile&fn=%s/S_%s>%s</a>",$i,$vtmp['ID'],$vtmp['Name']);
       } 
       foreach($Sec['UFile'][$it] as $jj=>$uu) { $vtmp=$uu['@attributes'];
	$tt .= sprintf("<a href=%s/TEMP/U_%s.%s>%s</a>",$_SESSION['HDIRW'],$vtmp['ID'],$vtmp['Type'],$vtmp['Name']);
       } 
       //-------------------]
       if(in_array($Status, array("3-Column","3-Column-Highlighted"))) echo "<tr bgcolor='$color'><td colspan=3>$v</td> </tr>";
       else echo "<tr bgcolor='$color'><td>$v</td> <td>$v2 $tt</td> <td> $v3</td></tr>";

       foreach($w['SubSec'] as $j=>$u) { $v=$Sec['Sec'][$i]['SubSec'][$j]['@attributes'][$n]; 
         $Status=$Sec['Sec'][$i]['SubSec'][$j]['@attributes']['Status']; 
         if ($Status != 'Hide') {
	   if($Status =="3-Column") echo "<tr><td  colspan='3'>$v </td></tr>"; 
	   if($Status =="Show" || $Status == "Cross") { 
   		$it=$i."_".$j;  
		$text="";
   		foreach($Sec['File'][$it] as $jj=>$uu) { $vtmp=$uu['@attributes'];
		  $text .= sprintf(" | <a href=$L&SECTIONS=ViewFile&fn=%s/%s/S_%s>%s</a>",$i,$j,$vtmp['ID'],$vtmp['Name']);
		} 
   		foreach($Sec['UFile'][$it] as $jj=>$uu) { $vtmp=$uu['@attributes']; 
		  if($_SESSION['HDIRW']=="/") $hh=""; else $hh=$_SESSION['HDIRW'];
		  $text .= sprintf(" | <a href=%s/TEMP/U_%s.%s>%s</a>",$hh,$vtmp['ID'],$vtmp['Type'],$vtmp['Name']);
		} 
		$text3="";
   		foreach($Sec['AFile'][$it] as $jj=>$uu) { 
		  $text3 .= sprintf(" | <a href=%s&&ASSESSMENT=%s>%s</a>",$L,str_replace('A_','',$jj),$uu);
		} 
		echo "<tr> <td>$v</td>  <td>$text</td>  <td>$text3</td></tr>"; 
	   }
         } 
       } 
   } 
   echo "</table>";
   return;
}

if(!$admin) exit();

if(!file_exists("$SectionFile")) { $Sec=$Sec0; 
   FileIO($SectionFile, $Sec, $root, "Write"); 
} else { 
   FileIORead($SectionFile, $Sec, $root, "ReadQ"); 
   if($_GET['SECTIONS']=="AddSection") { $nSec=sizeof($Sec['Sec']); if($nSec>$maxSections) exit("Num of sec > $maxSections");
   	$Sec['Sec']["x$nSec"]=$Sec0['Sec']['x0']; 
   }
   if($_GET['SECTIONS']=="DeleteSection") { $nSec=sizeof($Sec['Sec'])-1; 
   	if($nSec>0) unset($Sec['Sec']["x$nSec"]); else echo "Cannot Delect -  min # of section must be >1";
   }
   //-------Adding/Deleting Subsections -----------
   foreach($Sec['Sec'] as $i=>$w) { $Dir=sprintf("%s/%s", $SectionDir, $i);  
     if (is_dir($SectionDir) && !is_dir($Dir)) {if (!mkdir("$Dir",0777,true)) die("Failed to create folders");}
     $nSSec=sizeof($Sec['Sec'][$i]['SubSec']); 

     //unset($Sec['File']);
     
     // Add/Delete Files
     if($_GET['SECTIONS']=="AddFile_$i") SectionFileIORead($Sec['File'][$i], array('Ch'=>'Add', 'Dir'=>$Dir, 'maxFile'=>$maxFile)); 
     if($_GET['SECTIONS']=="DelFile_$i") SectionFileIORead($Sec['File'][$i], array('Ch'=>'Del', 'fn'=>$_GET['fn'],'Dir'=>$Dir, 'maxFile'=>$maxFile)); 

     // Upload/Delete Files
     if($_GET['SECTIONS']=="UploadFile_$i") SectionFileIORead($Sec['UFile'][$i], array('Ch'=>'Upload', 'Dir'=>$Dir, 'maxFile'=>$maxFile)); 
     if($_GET['SECTIONS']=="DelUFile_$i") SectionFileIORead($Sec['UFile'][$i], array('Ch'=>'DelU', 'fn'=>$_GET['fn'],'Dir'=>$Dir, 'maxFile'=>$maxFile)); 

     foreach($w['SubSec'] as $j=>$v) { $Dir=sprintf("%s/%s/%s", $SectionDir, $i, $j);  
       if (is_dir($SectionDir) && !is_dir($Dir)) {if (!mkdir("$Dir",0777,true)) die("Failed to create folders");}
       //unset($Sec['Sec'][$i]['SubSec'][$j]['@value']); unset($Sec['Sec'][$i]['File']); unset($Sec['Sec'][$i]['SubSec'][$j]['File']); 
     
       // Add/Delete Files
       if($_GET['SECTIONS']=="AddFile_".$i."_$j") SectionFileIORead($Sec['File'][$i."_$j"], array('Ch'=>'Add', 'Dir'=>$Dir, 'maxFile'=>$maxFile)); 
       if($_GET['SECTIONS']=="DelFile_".$i."_$j") SectionFileIORead($Sec['File'][$i."_$j"], array('Ch'=>'Del', 'Dir'=>$Dir, 'fn'=>$_GET['fn'], 'maxFile'=>$maxFile)); 

       // Upload/Delete Files
       if($_GET['SECTIONS']=="UploadFile_".$i."_$j") SectionFileIORead($Sec['UFile'][$i."_$j"], array('Ch'=>'Upload', 'Dir'=>$Dir, 'maxFile'=>$maxFile)); 
       if($_GET['SECTIONS']=="DelUFile_".$i."_$j") SectionFileIORead($Sec['UFile'][$i."_$j"], array('Ch'=>'DelU', 'Dir'=>$Dir, 'fn'=>$_GET['fn'], 'maxFile'=>$maxFile)); 

     } 

     // Add/Delete Subsections
     if($_GET['SECTIONS']=="AddSubSection_$i") { 
	     $Sec['Sec'][$i]['SubSec']["y$nSSec"]= $Sec['Sec'][$i]['SubSec']["y0"];
	     if($nSSec>$maxSections) exit("Num of subsec > $maxSections");
     }
     if($_GET['SECTIONS']=="DeleteSubSection_$i") { 
       if($nSSec>1) unset($Sec['Sec'][$i]['SubSec']["y".($nSSec-1)]); else echo "Cannot Delect, #section must be >1";
     }
   } 
   FileIO($SectionFile, $Sec, $root, "Write"); 
}

//Edit files.........
if($_GET['SECTIONS']=="EditFile") {
  echo "<a href='$L&SECTIONS'>Go to Section</a>";
 //if(!isset($_POST['FSection'])) { 
  $post="$L&SECTIONS=EditFile&fn=".$_GET['fn'];
  fp('Post',"$post");
 //}

  $n='Desc'; $file = sprintf("%s/%s",$SectionDir,$_GET['fn']);  
  if(!file_exists($file)) {
    $FSec[$n]="Add content"; $FSec['Type']=""; 
    FileIO($file, $FSec, $root, "Write"); 
  } else { 
	  FileIORead($file, $FSec, $root, "Read"); 
  }

 if(isset($_POST['FSection'])) { 
    foreach(array('Type','nCol','nRow','Display', 'SInput') as $ii) {
	 if(isset($_POST["FSection$ii"])) fpR("PV",$FSec[$ii],"FSection$ii");
    } 
	 fpR("PV",$FSec[$n],"FSection$n");
 }
	$nCol=1; if(isset($FSec['nCol'])) $nCol=$FSec['nCol'];
	$nRow=1; if(isset($FSec['nRow'])) $nRow=$FSec['nRow'];

 	fp("Button","Save", "FSection");
        echo "Type:"; fp("Select",array('Default','Learning-Styles', 'Survey-Form'),"FSectionType", $FSec['Type']);
	if($FSec['Type']=='Survey-Form') {
          $m='nRow'; echo "$m:"; fp("Select",array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25),"FSection$m", $FSec[$m]);
          $m='nCol'; echo "$m:"; fp("Select",array(1,2,3,4),"FSection$m", $FSec[$m]);
	}
        fp("Select",array('Display','HiddenAll', 'InActive'),"FSectionDisplay", $FSec['Display']);
        fp("Select",array('NoStudentInput','AllowUpload','AllowInput', 'FileInput'),"FSectionSInput", $FSec['SInput']);

	if($FSec['Display']=='HiddenAll') $Status="Closed"; else $Status="Opened";
   	fp("TA8",$FSec[$n],"FSection$n",'',1,'',array("EditorType"=>"Full", "Status"=>"$Status"));

	if($FSec['Type']=='Survey-Form') { 
	   echo "<table border=1 width=100%>";
	   for($ii=0; $ii<$nRow; $ii++) {echo "<tr>";
	    for($jj=0; $jj<$nCol; $jj++) {$m="Col$ii".'_'."Row$jj"; echo "<td>"; 
	    //$tt=$FSec[$m]; unset($FSec[$m]); $FSec[$m]['@value']=$tt;
	    $FSec[$m]['@attributes']=array("Selected"=>0); 
 	    if(isset($_POST['FSection']))  fpR("PV",$FSec[$m]['@value'],"FSectionColn-$m");
   	     fp("TA8",$FSec[$m]['@value'],"FSectionColn-$m",'',1,'',array("EditorType"=>"ToolBarWidth", "Status"=>"$Status"));
	    echo "</td>";
	    } echo "</tr>";
	   } echo "</table>";
	}
 	if(isset($_POST['FSection']))  FileIO($file, $FSec, $root, "Write"); 
 	fp("Button","Save", "FSection");
 	fp('Finish');
  exit();
}

//Upload files...............[

if($_GET['SECTIONS']=="UploadFile") {
 if(!isset($_POST['USection'])) { 
  $post="$L&SECTIONS=UploadFile&fn=".$_GET['fn'];
  echo "<form action='$post' method='post' enctype='multipart/form-data'>"; 
  echo '<p><label for="file"></label>';
  echo "<input type='file' name='file' id='file'><br>"; 
  echo "<input type='submit' name='USection' value='Upload'>";

  echo "<p><a href=$L&SECTIONS>Go back to Section</a>";
  foreach(glob($SectionDir . "/". $_GET['fn'] . ".*") as $i) echo "<br> File $i exists<br>";
 }


 if(isset($_POST['USection'])) { $FileSize= round($_FILES["file"]["size"] / 1024); 
    if ($_FILES["file"]["error"] > 0 || $FileSize > $maxFileSize) { echo "Uploading unsuccessful or file size is >$maxFileSize kB <br>";
    } else {
	$FileName=$_FILES["file"]["name"];
	$FileExt=pathinfo($FileName, PATHINFO_EXTENSION);
  	$FileBName=basename($FileName, ".$FileExt");
        $file = sprintf("%s/%s.%s",$SectionDir,$_GET['fn'],strtolower($FileExt));  
  	$fileC=basename($file, ".$FileExt") . ".$FileExt";

        if(!in_array(strtolower($FileExt),$FileExtensionAllowed )) {echo "File extension $FileExt is not allowed!"; return; }

 	move_uploaded_file($_FILES["file"]["tmp_name"], "$file");
	if(file_exists($file)) 
  	 printf("<p>Overwrote: '$FileName' to '$file' (Type=%s, Size=%s kB)", $_FILES["file"]["type"], round($_FILES["file"]["size"] / 1024));
	else 
  	 printf("<p>Copied: '$FileName' to '$file' (Type=%s, Size=%s kB)", $_FILES["file"]["type"], round($_FILES["file"]["size"] / 1024));
	 if(file_exists($file) && is_dir($TEMPW)) copy($file,"$TEMPW/$fileC");

	 if($_SESSION['HDIRW']=="/") echo "<p><a href=".$_SESSION['HDIRW']."TEMP/$fileC>$FileName</a>";
	 else echo "<p><a href=".$_SESSION['HDIRW']."/TEMP/$fileC>$FileName</a>";

	 foreach($Sec['Sec'] as $i=>$w) { foreach($w['SubSec'] as $j=>$u) {
   		$it=$i."_".$j;  
   		foreach($Sec['UFile'][$it] as $jj=>$uu) { 
		  $fn = $uu['@attributes']['ID']; 
	          if("$i/$j/U_$fn"==$_GET['fn']) { $TypeO=$Sec['UFile'][$it][$jj]['@attributes']['Type']; 
			if($TypeO !="$FileExt") {
        	          $fileO = sprintf("%s/%s.%s",$SectionDir,$_GET['fn'],$TypeO);  
			   if(file_exists($fileO)) {unlink($fileO); echo "<p>Deleted '$fileO'"; }
			} 
			$Sec['UFile'][$it][$jj]['@attributes']['Name']=$FileBName; 
			$Sec['UFile'][$it][$jj]['@attributes']['Type']=$FileExt; 
         		FileIO($SectionFile, $Sec, $root, "Write"); 
		  } 
		} 
	 } } 
    }
 }
 if(!isset($_POST['USection'])) { 
   echo "</form>";
  exit();
 } 
}
//...............]

	//$Sec['Sec']['x3']['SubSec']['y0']['File']=0; pa($Sec['Sec']);
   //FileIO($SectionFile, $Sec, $root, "DisplayA2XML"); 
	//exit();
//---------------------
 //$post=$_SESSION['COMMON']."/Admin/CourseOrganizer.php";
 $post="$L&SECTIONS=PostedValues";
 fp('Post',"$post");

 $n='Name'; $m=$n; 
echo "<table border=1 width=100%><tr><th colspan=3>"; 
 if(isset($_POST['Section'])) fpR("PV",$Sec[$n],"Section$m");
 fp("TA7",$Sec[$n],"Section$m",$Sec[$n]);
echo "</th></tr>";
//echo "</table>";
//echo "<table border=1 width=100%>";

foreach($Sec['Sec'] as $i=>$w) { echo "<p>";
echo "<tr bgcolor='#e3e3e3'><td>"; 
 $n='Name'; $m="Sec$i$n"; $vp=substr(strip_tags($Sec['Sec'][$i]['@attributes'][$n]),0,10);
 if(isset($_POST['Section'])) fpR("PV",$Sec['Sec'][$i]['@attributes'][$n],"Section$m");
 fp("TA8",$Sec['Sec'][$i]['@attributes'][$n],"Section$m", $vp);

 if(isset($_POST['Section'])) fpR("PV",$Sec['Sec'][$i]['@attributes']['Status'],"SectionS$m");
 fp("Select",$ChoiceArray,"SectionS$m", $Sec['Sec'][$i]['@attributes']['Status']);

echo "</td><td>";
 $n='Material'; $m="Sec$i$n"; 
 if(isset($_POST['Section'])) fpR("PV",$Sec['Sec'][$i]['@attributes'][$n],"Section$m");
 fp("TA8",$Sec['Sec'][$i]['@attributes'][$n],"Section$m", $Sec['Sec'][$i]['@attributes'][$n]);

   foreach($Sec['File'][$i] as $j=>$u) { $n='Name'; $m="$i-$j-$n"; $vtmp=&$Sec['File'][$i][$j]['@attributes'][$n]; 
   	if(isset($_POST['Section'])) fpR("PV",$vtmp,"Section$m");
   	fp("T1",$vtmp,"Section$m","");
        printf("<a href='%s&SECTIONS=DelFile_%s&fn=%s'>X</a>", $L, $i,$j);
        printf(" | <a href='%s&SECTIONS=EditFile&fn=%s/S_%s.xml'>E</a>", $L, $i,$u['@attributes']['ID']);
   } 
   
   printf("<a href='%s&SECTIONS=AddFile_%s'>%s</a>", $L, $i,fpRv("B","Add File"));

   foreach($Sec['UFile'][$i] as $j=>$u) { $n='Name'; $m="U$i-$j-$n"; $vtmp=&$Sec['UFile'][$i][$j]['@attributes'][$n]; 
   	if(isset($_POST['Section'])) fpR("PV",$vtmp,"Section$m");
   	fp("T1",$vtmp,"Section$m","");
        printf("<a href='%s&SECTIONS=DelUFile_%s&fn=%s'>X</a>", $L, $i,$j);
        printf(" | <a href='%s&SECTIONS=UploadFile&fn=%s/U_%s'>Upload</a>", $L, $i,$u['@attributes']['ID']);
   } 
 unset($vtmp);


 printf("<a href='%s&SECTIONS=UploadFile_%s'>%s</a>", $L, $i,fpRv("B","Upload File"));

echo "</td><td>";
 $n='Assess'; $m="Sec$i$n"; 
 if(isset($_POST['Section'])) fpR("PV",$Sec['Sec'][$i]['@attributes'][$n],"Section$m");
 fp("TA8",$Sec['Sec'][$i]['@attributes'][$n],"Section$m", $Sec['Sec'][$i]['@attributes'][$n]);

echo "</td></tr>";
echo "<tr><td colspan='3'>";

 foreach($w['SubSec'] as $j=>$u) {
   $n='Name'; $m="Sec$i$j$n"; 
   if(isset($_POST['Section'])) fpR("PV",$Sec['Sec'][$i]['SubSec'][$j]['@attributes'][$n],"Section$m");
   $v=$Sec['Sec'][$i]['SubSec'][$j]['@attributes'][$n]; $vp=substr(strip_tags($v),0,10);
   fp("TA8",$Sec['Sec'][$i]['SubSec'][$j]['@attributes'][$n],"Section$m","<br>$vp");

   if(isset($_POST['Section'])) fpR("PV",$Sec['Sec'][$i]['SubSec'][$j]['@attributes']['Status'],"SectionS$m");
   fp("Select",$ChoiceArray,"SectionS$m", $Sec['Sec'][$i]['SubSec'][$j]['@attributes']['Status']);

   //File Creation
   $it=$i."_".$j;  
   $n="SectionAddFID$it"; $mode='table';
   echo  "Files<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
   echo "<div id='Hidden$n' name='Hidden$n' style='display:none;'><hr>"; 
   $nfiles=0;
   foreach($Sec['File'][$it] as $jj=>$uu) { $nfiles++;
   	$n='Name'; $m="Sec$it$jj$n"; $vtmp=&$Sec['File'][$it][$jj]['@attributes'][$n]; 
   	if(isset($_POST['Section'])) fpR("PV",$vtmp,"Section$m");
   	fp("T",$vtmp,"Section$m","");
        printf("<a href='%s&SECTIONS=DelFile_%s_%s&fn=%s'>X</a>", $L, $i,$j,$jj);
        printf(" | <a href='%s&SECTIONS=EditFile&fn=%s/%s/S_%s.xml'>E</a>", $L, $i,$j,$uu['@attributes']['ID']);
   }
   printf("<a href='%s&SECTIONS=AddFile_%s_%s'>%s</a>", $L, $i,$j,fpRv("B","Add File"));
   echo "</div> ($nfiles files) | ";

   //Upload
   $n="SectionAddUID$it"; $mode='table';
   echo "Upload<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
   echo "<div id='Hidden$n' name='Hidden$n' style='display:none;'><hr>";
   $nfiles=0;
   foreach($Sec['UFile'][$it] as $jj=>$uu) { $nfiles++;
   	$n='Name'; $m="USec$it$jj$n"; $vtmp=&$Sec['UFile'][$it][$jj]['@attributes'][$n]; 
   	if(isset($_POST['Section'])) fpR("PV",$vtmp,"Section$m");
   	fp("T",$vtmp,"Section$m","");
        printf("<a href='%s&SECTIONS=DelUFile_%s_%s&fn=%s'>X</a>", $L, $i,$j,$jj);
        printf(" | <a href='%s&SECTIONS=UploadFile&fn=%s/%s/U_%s'>Upload</a>", $L, $i,$j,$uu['@attributes']['ID']);
   }
   printf("<a href='%s&SECTIONS=UploadFile_%s_%s'>%s</a>", $L, $i,$j,fpRv("B","Upload File"));
   echo "<hr></div> ($nfiles files) | ";

   unset($vtmp);

   //------------[
   echo "ASSESSMENTS";

        $n="SectionAddAID$it"; $mode='table';
        $sb="<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
	$s = htext("Select the assessment(s)") . " to be included in the subsection and click save<br>"; 
	$AssessmentFile=getFile("ASSESSMENT", "DATA", "Assessment.xml");
  	FileIORead($AssessmentFile, $SQList, $root, "ReadQ"); 
	foreach($SQList['Q'] as $k=>$vv) { $value=$vv['Name'];$ADir=$vv['Directory'];
	  if(isset($_POST['Section'])) {
            if(isset($_POST["SAV-$n-$k"])) $Sec['AFile'][$it][$ADir]=$value; //vk ... else unset($Sec['AFile'][$it][$ADir]);
	  }
	  if(isset($Sec['AFile'][$it][$ADir])) $s .= "<input type='checkbox' name='SAV-$n-$k' value='$value' checked='checked'> $value | ";
	  $s .= "<input type='checkbox' name='SAV-$n-$k' value='$value'> $value | ";
	}
        echo "$sb <div id='Hidden$n' name='Hidden$n' style='display:none;'><hr>$s<hr></div>";
   //------------]

 } 
 echo "<br>" .  htext('Subsection',1,"#00ff00");
 printf("<a href='%s&SECTIONS=AddSubSection_%s'>%s</a>", $L, $i,fpRv("B","Add"));
 printf("<a href='%s&SECTIONS=DeleteSubSection_%s'>%s</a>", $L, $i,fpRv("B","Delete"));

fp("Button","Save", "Section");
echo "</td></tr>";

  if(isset($_POST['Section'])) FileIO($SectionFile, $Sec, $root, "Write"); 
 
} 
echo "</table>";
 echo " <p>" . htext("Section",1,"#00ff00") . " <a href='$L&SECTIONS=AddSection'>" . fpRv("B","Add") . "</a>";
 echo " | <a href='$L&SECTIONS=DeleteSection'>" .fpRv("B","Delete") . "</a><br>";
 fp("Button","Save", "Section");
 fp('Finish');
//---------------------



exit('<br>Exiting Sections');

//------------------------
function SectionFileIO($v, $Options="") { if(isset($v['f0'])) $nfile=sizeof($v); else $nfile=0;
        //echo htext($nfile); echo "<pre>" ;  print_r($v); echo "</pre>";
	$Dir=$Options['Dir'];
	if(is_dir($Options['Dir'])) { //echo $Dir;
	  if($Options['Ch']=="Add") {
	     if($nfile>=$Options['maxFile']) exit('Max file allowed=1');
	     $v["f$nfile"]['@attributes']=array("Name"=>'file name', "ID"=>uniqid());
	  } elseif($Options['Ch']=="Del") { 
	     $w = $v; unset($v['f'.($nfile-1)]); $k=0; 
	     //echo "<pre>$nfile...." ;  print_r($v); echo "</pre>";
	     foreach($w as $i=>$vv) {$file = sprintf("%s/S_%s.xml",$Dir,$vv['@attributes']['ID']); 
	        if($Options['fn']=="$i") { //echo "$i ......: ".$Options['fn'];
		     if(file_exists("$file")) unlink($file); 
		} else { $v['f'.$k] = $w[$i]; $k++; }
	     }

	  //File Upload
	  } elseif($Options['Ch']=="Upload") {
	     if($nfile>=$Options['maxFile']) exit('Max file allowed=1');
	     $v["f$nfile"]['@attributes']=array("Name"=>'file name', "ID"=>uniqid());
	  } elseif($Options['Ch']=="DelU") { 
	     //echo "<pre>" ;  print_r($v); echo "</pre>";
	     $w = $v; unset($v['f'.($nfile-1)]); $k=0; 
	     foreach($w as $i=>$vv) {$file = sprintf("%s/U_%s.%s",$Dir,$vv['@attributes']['ID'],$vv['@attributes']['Type']); 
	        if($Options['fn']=="$i") { // echo "$i ......: ".$Options['fn'];
		     if(file_exists("$file")) { unlink($file); echo "'$file' was deleted!";} 
		} else { $v['f'.$k] = $w[$i]; $k++; }
	     }
	  } 
	  



	} else exit("$Dir directory does not exists!");  
}
//------------------------
function SectionFileIORead(&$v, $Options="") { if(isset($v['f0'])) $nfile=sizeof($v); else $nfile=0;
        //echo htext($nfile); echo "<pre>" ;  print_r($v); echo "</pre>";
	$Dir=$Options['Dir'];
	if(is_dir($Options['Dir'])) { //echo $Dir;
	  if($Options['Ch']=="Add") {
	     if($nfile>=$Options['maxFile']) exit('Max file allowed=1');
	     $v["f$nfile"]['@attributes']=array("Name"=>'file name', "ID"=>uniqid());
	  } elseif($Options['Ch']=="Del") { 
	     $w = $v; unset($v['f'.($nfile-1)]); $k=0; 
	     //echo "<pre>$nfile...." ;  print_r($v); echo "</pre>";
	     foreach($w as $i=>$vv) {$file = sprintf("%s/S_%s.xml",$Dir,$vv['@attributes']['ID']); 
	        if($Options['fn']=="$i") { //echo "$i ......: ".$Options['fn'];
		     if(file_exists("$file")) unlink($file); 
		} else { $v['f'.$k] = $w[$i]; $k++; }
	     }

	  //File Upload
	  } elseif($Options['Ch']=="Upload") {
	     if($nfile>=$Options['maxFile']) exit('Max file allowed=1');
	     $v["f$nfile"]['@attributes']=array("Name"=>'file name', "ID"=>uniqid());
	  } elseif($Options['Ch']=="DelU") { 
	     //echo "<pre>" ;  print_r($v); echo "</pre>";
	     $w = $v; unset($v['f'.($nfile-1)]); $k=0; 
	     foreach($w as $i=>$vv) {$file = sprintf("%s/U_%s.%s",$Dir,$vv['@attributes']['ID'],$vv['@attributes']['Type']); 
	        if($Options['fn']=="$i") { // echo "$i ......: ".$Options['fn'];
		     if(file_exists("$file")) { unlink($file); echo "'$file' was deleted!";} 
		} else { $v['f'.$k] = $w[$i]; $k++; }
	     }
	  } 
	  



	} else exit("$Dir directory does not exists!");  
}
//---------------------------------------
function SectionUploadFile($A,$f,$Options="") { 
  $AllowedExtension=array("gif", "jpeg", "jpg", "png",'bmp','doc','docx','ppt','pptx','pdf','txt','m');
  global $TEMPW, $WTEMP;
  $F=$_FILES["file"]; $UFlag=false;  

  if(!isset($_POST["FSection"])) return; 

  foreach($A['U'] as $k=>$vv) { //if($k=='') unset($A['U'][$k];
        if($_POST["OverWrite"]==$k) { $UFlag=true; $UFile="$k"; } 
        if(isset($_POST["SFName$k"])) $A['U'][$k]['@attributes']['Name']=$_POST["SFName$k"];  
	if(isset($_POST["Link$k"]))  $A['U'][$k]['@attributes']['Link']=1; else $A['U'][$k]['@attributes']['Link']=0; 
	if(isset($_POST["View$k"]))  $A['U'][$k]['@attributes']['View']=1; else $A['U'][$k]['@attributes']['View']=0; 
	if($_POST["Delete$k"]=='Delete') { $dname=dirname($f).'/upload'; $fname=$A['U'][$k]['@attributes']['FileName']; unset($A['U'][$k]); 
	  echo "<br>Delete $k : $dname/$fname" . $_POST['SFName'];
	  if($fname != '') { if(file_exists("$TEMPW/$fname")) unlink("$TEMPW/$fname");  if(file_exists("$dname/$fname")) unlink("$dname/$fname");  } 
	} 
  } 
  $nn='SectionFile-NewFile'; 
  if(isset($_POST[$nn])) { if(sizeof($A['U'])>=20) {echo htext('Max #files allowed=20'); exit(); }
        $UFile=$_POST[$nn]; $UFlag=true; $A['U'][$UFile]['@attributes']['Name']=$_POST['SFName']; 
	$A['U'][$UFile]['@attributes']['Link']=1; $A['U'][$UFile]['@attributes']['View']=1;
  }

  		if($UFlag) { $dname=dirname($f).'/upload'; 
  if (!is_dir($dname)) {if (!mkdir("$dname",0777,true)) die("<p><font color='red'>Failed to create folders '$dname'</font>");}
  if ($F["error"] > 0) { echo "Error: " . $F["error"] . "<br>";
  } else { $info = pathinfo($F["name"]); $fname="$UFile.".$info['extension']; 
    if(!in_array(strtolower($info['extension']),$AllowedExtension)) exit("File extension ".$info['extension']." is not allowed"); 
    printf("<br>File '%s', uploaded as '%s' (type: %s, size=%s)", $F["name"],$fname,  $F["type"], $F["size"]);
    if (is_dir($dname) && $fname !='') {
	   move_uploaded_file($F["tmp_name"], "$dname/$fname"); copy("$dname/$fname", "$TEMPW/$fname");
	   $A['U'][$UFile]['@attributes']['FileName']=$fname; 
	   $A['U'][$UFile]['@attributes']['Type']=$info['extension'];
	   $A['U'][$UFile]['@attributes']['Name']=$info['basename'];
    } 
  }
  		} 
}
function SectionEditFunction($SectionFile,$L) { 
  global $TEMPW, $WTEMP, $SNAME;
  $maxNumofFile=6; 
  // -------written by vkumar@utep.edu------[
  // This function edits the 'SectionFile': Add/Del Rows/Cols, Add Assessment, Add File, Upload file
  // --------------------------------]
  FileIORead($SectionFile, $Table, $root, "Read"); $nCols=$Table['Info']['@attributes']['nCols']; $nRows=$Table['Info']['@attributes']['nRows'];
  $AssessmentFile=getFile("ASSESSMENT", "DATA", "Assessment.xml"); FileIORead($AssessmentFile, $SQList, $Aroot, "ReadQ"); 

  $post="$L&SECTIONS=".$_GET['SECTIONS'];
  echo "<form action='$post' method='post' enctype='multipart/form-data'>";
  fp("Button","Submit", "FSection");

  $n="Info"; 
  if($_GET['SECTIONS']=="Edit$n") {
    if(isset($Table['Info']['@value']))  { $Table['Info']['value']=$Table['Info']['@value']; unset($Table['Info']['@value']); }
    if(isset($_POST[$n])) { 
	fpR("PV",$Table['Info']['value'],"$n"); 
        $Table['Info']['@attributes']['Modified']="By '$SNAME' on " . date('h:i A, M d, Y'); 
    }
    fp("TA8",$Table['Info']['value'],"$n",'',1,'',array("EditorType"=>"Full", 'Status'=>'Opened'));
    SectionUploadFile($Table['Info'],$SectionFile); 
    if(isset($_POST["FSection"])) SectionDisplayFile($Table['Info']['F'],"",'File','GetPostedValue', $SectionFile);

    if(isset($_POST[$n])) FileIO($SectionFile, $Table, $root, "Write"); 
    if(isset($_GET['SubsectionFile']) && $_GET['SubsectionFile'] !='') { $FID=$_GET['SubsectionFile']; 
	if(!isset($Table['Info']['F']["$FID"])) { 
		$Table['Info']['F']["$FID"]['@attributes']['Name']='New Name';
	        if(sizeof($Table['Info']['F'])>$maxNumofFile) exit("Max number of file=$maxNumofFile");
    		FileIO($SectionFile, $Table, $root, "Write"); 
	} 
    }
    $AddedA=$Table['Info']; 
  }
  for($i=0; $i<$nRows; $i++) {  
     for($j=0; $j<$nCols; $j++) {$n="x$i-y$j"; 
       if($_GET['SECTIONS']=="Edit$n") {$m=$n; 
         if(isset($_POST[$n])) { unset($Table["x$i"]["y$j"]['A']); 
	     foreach($SQList['Q'] as $k=>$vv) {$ADir=$vv['Directory']; 
	       if(isset($_POST[$ADir])) $Table["x$i"]["y$j"]['A'][$ADir]['@attributes']=array('Name'=>$vv['Name']); 
	     }
	     fpR("PV",$Table["x$i"]["y$j"]['value'],"$n"); 
	     if(isset($_POST['RowColor'])) fpR("PV",$Table["x$i"]["y$j"]['@attributes']['RowColor'],"RowColor"); 
	     if(isset($_POST['RowSpan'])) fpR("PV",$Table["x$i"]["y$j"]['@attributes']['RowSpan'],"RowSpan"); 
	     SectionUploadFile($Table["x$i"]["y$j"],$SectionFile); 

    	     SectionDisplayFile($Table["x$i"]["y$j"]['F'],"",'File','GetPostedValue', $SectionFile);
             FileIO($SectionFile, $Table, $root, "Write"); 
             //FileIO($SectionFile, $Table, $root, "DisplayA2XML"); 
	 }
    	if(isset($_GET['SubsectionFile']) && $_GET['SubsectionFile'] !='') { $FID=$_GET['SubsectionFile']; 
	   if(!isset($Table["x$i"]["y$j"]['F']["$FID"])) { 
		$Table["x$i"]["y$j"]['F']["$FID"]['@attributes']['Name']='New Name';
	        if(sizeof($Table["x$i"]["y$j"]['F'])>$maxNumofFile) exit("Max number of file=$maxNumofFile");
    		FileIO($SectionFile, $Table, $root, "Write"); 
	   } 
    	}
         $AddedA=$Table["x$i"]["y$j"]; 
         fp("TA8",$Table["x$i"]["y$j"]['value'],"$n","<hr>".htext('Edit'),1,'',array("EditorType"=>"Full", 'Status'=>'Closed'));
         if($j==0) fp("T",$Table["x$i"]["y$j"]['@attributes']['RowColor'],"RowColor","Row Color"); 
         fp("T1",$Table["x$i"]["y$j"]['@attributes']['RowSpan'],"RowSpan","Row Span"); 
       }
     } 
  }

  //----------ADD ASSESSMENT--------------
  echo "<hr>".htext('Add Assessment')." : "; 
  foreach($SQList['Q'] as $k=>$vv) {$ADir=$vv['Directory']; fp('C1',isset($AddedA['A'][$ADir]),$ADir); echo  $vv['Name'] . ' | '; }

  //---------UPLOAD---------------
  echo "<hr>".htext('Upload')." : "; 
  foreach($AddedA['U'] as $k=>$vv) { if(!isset($vv['@attributes']['View'])) $vv['@attributes']['View']=1; 
    $fname=$vv['@attributes']['Name']; $filename=$vv['@attributes']['FileName']; 
    if(file_exists("$TEMPW/$filename") && $filename != '' && $vv['@attributes']['Link']!=0) $str="<a href=$WTEMP/$filename>$fname</a>"; else $str="$fname";
    $n="HideUnhideUploadFile$k"; $mode='table';
    echo " | $str<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
    echo "<div id='Hidden$n' name='Hidden$n' style='display:none;'><hr>"; 
    	fp('T',"$fname","SFName$k");  echo " New Name | Over write ";  fp('R',$k,'OverWrite'); 
	fp('C','Delete',"Delete$k", ' | Delete'); echo ' | Link '; fp('C1',$vv['@attributes']['Link']==1,"Link$k"); 
	echo ' | Viewable '; fp('C1',$vv['@attributes']['View']==1,"View$k"); 
    echo "<hr/></div>"; 
  }
  fp('C','U'.uniqid(),'SectionFile-NewFile',' | New File'); echo "<input type='file' name='file' id='file'><br>"; 
  //------------------------

  echo "<hr>".htext('File')." : "; $newFID=uniqid();  
  SectionDisplayFile($AddedA['F'],"$post",'File',$SectionFile);

  echo "<a href=$post&SubsectionFile=F$newFID>New File</a>"; 
  echo "<hr>";
  //------------------------

  fp("Button","Submit", "FSection");
  echo "</form>";
  echo "<a href='$L&SECTIONS'>Done</a><br>";

  exit('Editing is on'); 
}

//-----------------
function SectionDisplayFile($A,$L,$type='File',$Options='', $f) {
  global $TEMPW, $WTEMP, $SNAME, $admin;

  $dname=dirname($f).'/upload'; 
  if($Options=="GetPostedValue") { 
    foreach($A as $k=>$vv) { 
    if(isset($_POST["FSection"])){if(!isset($A[$k]['@attributes']['FileName'])) $A[$k]['@attributes']['FileName']="$k.xml";
      if(isset($_POST["$type-Name$k"])) $A[$k]['@attributes']['Name']=$_POST["$type-Name$k"]; 
      if(isset($_POST["$type-Link$k"])) $A[$k]['@attributes']['Link']=1; else $A[$k]['@attributes']['Link']=0;
      if(isset($_POST["$type-View$k"])) $A[$k]['@attributes']['View']=1; else $A[$k]['@attributes']['View']=0;
      if($_POST["$type-Delete$k"]=='Delete') { $fname=$A[$k]['@attributes']['FileName']; unset($A[$k]); unlink("$dname/$fname"); }

    } 
    } 
   return;
  }
  foreach($A as $k=>$vv) { if(!isset($vv['@attributes']['View'])) $vv['@attributes']['View']=1;
    $fname=$vv['@attributes']['Name']; $filename=$vv['@attributes']['FileName'];
    if($vv['@attributes']['Link']!=0) $str="<a href=$L&SubsectionFile=$k&ViewFile>$fname</a>"; else $str="$fname";

    $n="$type-HideUnhideUploadFile$k"; $mode='table';
    echo " | $str<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
    echo "<div id='Hidden$n' name='Hidden$n' style='display:none;'><hr>";
        if($admin && !isset($_SESSION['StudentView'])) echo "<a href=$L&SubsectionFile=$k&EditFile>Edit</a>";
        fp('T',"$fname","$type-Name$k");  echo " New Name"; 
        fp('C','Delete',"$type-Delete$k", ' | Delete'); echo ' | Link '; fp('C1',$vv['@attributes']['Link']==1,"$type-Link$k");
        echo ' | Viewable '; fp('C1',$vv['@attributes']['View']==1,"$type-View$k");
    echo "<hr/></div>";
  }
  //------------------------
}

function SectionViewFile($dirfile,$f,$L,$Options='') { global $admin, $fileD; 
  $root="SECTIONS"; 
  
  //if(!$admin) return; 
  $dname=dirname($dirfile).'/upload'; $file="$dname/$f.xml";
  if(file_exists($file)) FileIORead($file, $FSec, $root, "Read"); 
  if($FSec['Type']=='Survey-Form') { include($_SESSION['HDIR']."/COMMON/Admin/Survey_Form.php"); }
  else echo "<br />".$FSec['Desc'];
  exit();
} 
//------------------------
function SectionEditFile($dirfile,$f,$L,$Options='') {
  global $admin; 
  $root="SECTIONS";
  if(!$admin) return; 

  $dname=dirname($dirfile).'/upload'; $file="$dname/$f.xml";

  echo "<a href='$L&SECTIONS'>Section</a>";
 //if(!isset($_POST['FSection'])) { 
  $post=sprintf("$L&SECTIONS=%s&SubsectionFile=$f&EditFile",$_GET['SECTIONS']);
  fp('Post',"$post");
 //}

  $n='Desc'; 
  if(!file_exists($file)) { $FSec[$n]="Add content"; $FSec['Type']=""; FileIO($file, $FSec, $root, "Write"); 
  } else { FileIORead($file, $FSec, $root, "Read"); }


 if(isset($_POST['FSection'])) { 
    foreach(array('Type','nCol','nRow','Display', 'SInput') as $ii) {
	 if(isset($_POST["FSection$ii"])) fpR("PV",$FSec[$ii],"FSection$ii");
    } 
	 fpR("PV",$FSec[$n],"FSection$n");
 }
	$nCol=1; if(isset($FSec['nCol'])) $nCol=$FSec['nCol'];
	$nRow=1; if(isset($FSec['nRow'])) $nRow=$FSec['nRow'];

 	fp("Button","Save", "FSection");
        echo "Type:"; fp("Select",array('Default','Learning-Styles', 'Survey-Form'),"FSectionType", $FSec['Type']);
	if($FSec['Type']=='Survey-Form') {
          $m='nRow'; echo "$m:"; fp("Select",array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25),"FSection$m", $FSec[$m]);
          $m='nCol'; echo "$m:"; fp("Select",array(1,2,3,4),"FSection$m", $FSec[$m]);
	}
        fp("Select",array('Display','HiddenAll', 'InActive'),"FSectionDisplay", $FSec['Display']);
        fp("Select",array('NoStudentInput','AllowUpload', 'AllowInput', 'FileInput'),"FSectionSInput", $FSec['SInput']);

	if($FSec['Display']=='HiddenAll') $Status="Closed"; else $Status="Opened";
   	fp("TA8",$FSec[$n],"FSection$n",'',1,'',array("EditorType"=>"Full", "Status"=>"$Status"));

	if($FSec['Type']=='Survey-Form') { 
	   echo "<table border=1 width=100%>";
	   for($ii=0; $ii<$nRow; $ii++) {echo "<tr>";
	    for($jj=0; $jj<$nCol; $jj++) {$m="Col$ii".'_'."Row$jj"; echo "<td>"; 
	    //$tt=$FSec[$m]; unset($FSec[$m]); $FSec[$m]['@value']=$tt;
	    $FSec[$m]['@attributes']=array("Selected"=>0); 
 	    if(isset($_POST['FSection']))  fpR("PV",$FSec[$m]['@value'],"FSectionColn-$m");
   	     fp("TA8",$FSec[$m]['@value'],"FSectionColn-$m",'',1,'',array("EditorType"=>"ToolBarWidth", "Status"=>"$Status"));
	    echo "</td>";
	    } echo "</tr>";
	   } echo "</table>";
	}
 	if(isset($_POST['FSection']))  FileIO($file, $FSec, $root, "Write"); 
  
 	fp("Button","Save", "FSection");
 	fp('Finish');
  exit();
}

//----------------------
function LoadJS(){
echo <<<END
 <script> 
  $('#TableTopMenuID').hide(); var attr = {};
  function AddtoDataObj(id,k,v) { attr = $('#AssessmentLocationID').data(); if($('#'+id).prop('checked')) {attr[k]=v;} else attr[k]=0; }
  function Assessment(i,j,k,f) { 
   var id='B-'+i+'-'+j; var o = $('#'+id); var d=o.data(); 
   if(f=='Load') { 
	$.each( $('.Class-'+i+'-'+j), function() { if( $('#'+this.id).prop('checked')) d[this.id] = 1; else delete d[this.id];  });
        $('#AssessmentDoneID').show(); $('#AssessmentLocationID').show(); $('#SectionIDMain').hide();
        $('#AssessmentLocationID').html(ObjToSource(d));
        dimag({'outputid':'bottom','LoadPHP':'COURSES/NewAssessment.php', 'COURSE':'1'}); 
   /*
 	$.ajax({url: "dimag.php",  type: "POST",  dataType:"html", data: {'attr': $('#AssessmentLocationID').data(), 'LoadPHP':'Admin/NewAssessment.php', 'Assessments':d},
  	  success: function( data ) {// alert(JSON.stringify(data));
            $("#AssessmentLocationID").html(data); 
  	  },
  	  error: function(data){alert('Error occurred'); }
 	});
   */

   } else if(f=='Done') {  
         $('#AssessmentDoneID').hide(); $('#AssessmentLocationID').hide(); $('#SectionIDMain').show();
   } else if(f=='All') {  $('#B-'+i+'-'+j).show(); 
      if($('#All-'+i+'-'+j).prop('checked')) $('.Class-'+i+'-'+j).prop('checked',true); else { $('.Class-'+i+'-'+j).prop('checked',false);  }
   } else { $('#B-'+i+'-'+j).show(); 
     //if($('#'+k).prop('checked')) d[k]=1; else delete d[k]; 
   } 
  }
//-----------------
function ObjToSource(o){
    if (!o) return 'null';
    if (typeof(o) == "object") {
        if (!ObjToSource.check) ObjToSource.check = new Array();
        for (var i=0, k=ObjToSource.check.length ; i<k ; ++i) {
            if (ObjToSource.check[i] == o) {return '{}';}
        }
        ObjToSource.check.push(o);
    }
    var k="",na=typeof(o.length)=="undefined"?1:0,str="";
    for(var p in o){
        if (na) k = "'"+p+ "':";
        if (typeof o[p] == "string") str += k + "'" + o[p]+"',";
        else if (typeof o[p] == "object") str += k + ObjToSource(o[p])+",";
        else str += k + o[p] + ",";
    }
    if (typeof(o) == "object") ObjToSource.check.pop();
    if (na) return "{"+str.slice(0,-1)+"}";
    else return "["+str.slice(0,-1)+"]";
}
 </script>
END;

}
?>

