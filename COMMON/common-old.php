<?php

//-------------------------FUNCTIONS----------------
function htext($t,$flag=true, $c="#ffff00", $s="11pt") {
        if($flag) $CC=$c; else $CC="#e3e3e3"; return "<span style='background-color:$CC; font-size:$s;'>$t</span>";
}
function htext2($t,$flag=true, $c="#ffff00", $s="11pt") {
        if($flag) $CC=$c; else $CC="#ffffff"; return "<span style='background-color:$CC; font-size:$s;'>$t</span>";
}
function etext($s,$t) {return "<font size=$s>$t</font>";}
function psh($t) {echo "<pre>"; echo htmlspecialchars($t); echo "</pre>";}
function tpa($t) {echo "<textarea>"; print_r($t); echo "</textarea>";}

//----------------------------------------

function erf($x) 
{ 
        $pi = 3.1415927; 
        $a = (8*($pi - 3))/(3*$pi*(4 - $pi)); 
        $x2 = $x * $x; 

        $ax2 = $a * $x2; 
        $num = (4/$pi) + $ax2; 
        $denom = 1 + $ax2; 

        $inner = (-$x2)*$num/$denom; 
        $erf2 = 1 - exp($inner); 

        if($x<0) return -sqrt($erf2); 
        else return sqrt($erf2); 
} 
//-------------------------
function cleaned($query) {
        $query=htmlspecialchars($query,ENT_QUOTES);
        // Cleaning appostrophy and quotation
        $query = str_replace('\&quot', "&quot", $query);
        $query = str_replace('\&#039', "&#039", $query);
        // Cleaning backslash
        $data = explode("\\",$query);
        $s=""; $i=0; $n=sizeof($data); 
        foreach($data as $c) { $i=$i+1; $p=$c; if($c =="" && $i!="1" && $i != $n) { $p="&#92;";  } $s=$s.$p; }
        //echo "Size: ". $s ;
        return $s;
}

function sr($query) {
        // Cleaning appostrophy and quotation
        $s= str_replace('&lt;br&gt;', "<br>", $query);
        $s= str_replace('&lt;br /&gt;', "<br />", $s);
        $s= str_replace('&lt;p&gt;', "<p>", $s);

        $s= str_replace('&lt;em&gt;', "<em>", $s); 
	$s= str_replace('&lt;/em&gt;', "</em>", $s);

        $s= str_replace('&lt;pre&gt;', "<pre>", $s); 
	$s= str_replace('&lt;/pre&gt;', "</pre>", $s);

	$s= str_replace('&lt;p /&gt;', "<p>", $s);
	$s= str_replace('&lt;img', "<img", $s);
	$s= str_replace('&quot;', '"', $s);
	$s= str_replace('&gt;', ">", $s);
        //echo "Size: ". $s ;
        return $s;
}

//--------------------------
function WriteAnswers($Q, $G, $CScore, $Student_id, $CQ, $fname, $lname)
{
 global $C_FROM_STUDENTS, $S_DIR; 
 $filename= "$S_DIR/$Student_id.xml";

 $L = $CQ->level; $t = sr($CQ->title);
 $dom = new DomDocument('1.0'); 
 $Students = $dom->appendChild($dom->createElement('Students')); 

 $student= $Students->appendChild($dom->createElement('student')); 
   $SID = $student->appendChild($dom->createElement('SID')); 
   $SID->appendChild($dom->createTextNode($Student_id)); 
   $fn= $student->appendChild($dom->createElement('fname')); 
   $fn->appendChild($dom->createTextNode($fname)); 
   $ln= $student->appendChild($dom->createElement('lname')); 
   $ln->appendChild($dom->createTextNode($lname)); 

if($Q != 1 ||  file_exists($filename) )  {
$dom2= simplexml_load_file($filename);

   foreach ($dom2->student[0]->children() as $c) {
   if($c->getName() == "question") {
   
   $question= $student->appendChild($dom->createElement('question')); 
 	$QID = $question->appendChild($dom->createElement('QID')); 
 	$QID->appendChild($dom->createTextNode($c->QID)); 
 	$GQID = $question->appendChild($dom->createElement('GQID')); 
 	$GQID->appendChild($dom->createTextNode($c->GQID)); 
 	$level= $question->appendChild($dom->createElement('level')); 
 	$level->appendChild($dom->createTextNode($c->level)); 
 	$title= $question->appendChild($dom->createElement('title')); 
 	$title->appendChild($dom->createTextNode($c->title)); 
 	$Score= $question->appendChild($dom->createElement('Score')); 
 	$Score->appendChild($dom->createTextNode($c->Score)); 
 	$Score= $question->appendChild($dom->createElement('Comments')); 
 	$Score->appendChild($dom->createTextNode("$c->Comments")); 
   }
   }
}
   $question= $student->appendChild($dom->createElement('question')); 
 	$QID = $question->appendChild($dom->createElement('QID')); 
 	$QID->appendChild($dom->createTextNode($Q)); 
 	$GQID = $question->appendChild($dom->createElement('GQID')); 
 	$GQID->appendChild($dom->createTextNode($G)); 
 	$level= $question->appendChild($dom->createElement('level')); 
 	$level->appendChild($dom->createTextNode("$L")); 
 	$title= $question->appendChild($dom->createElement('title')); 
 	$title->appendChild($dom->createTextNode("$t")); 
 	$Score= $question->appendChild($dom->createElement('Score')); 
 	$Score->appendChild($dom->createTextNode("$CScore")); 
 	$Score= $question->appendChild($dom->createElement('Comments')); 
 	$Score->appendChild($dom->createTextNode("$C_FROM_STUDENTS")); 
//generate xml 
 $dom->formatOutput = true; $test1 = $dom->saveXML(); $dom->save($filename); 

}
//---------------------------
function Print_all_Student_Answers2($sxml, $SDIR) {
  global $_SESSION, $PrintSAll, $details;
  $CSID=$_SESSION['logged']; $priv = $_SESSION['priv'];
  global $Data;
  $Data= array();
  $sOverAll = array();
//-------------
//-------------
  $i = 0;  $cqid1=0; $ii=0; 
        $bg1="#e3e3e3";  $bg2="#ffffff";
  $nColn=3; 
  echo "<table width=100% border=1>";
  if($PrintSAll==2) echo "<tr>";
  foreach ($sxml->student as $CQ) {  $ii=$ii+1; $bg=$bg1; $sOverAll[$ii-1]=0;
        $id = $CQ->id; $id1=$CSID; if($priv=="Admin") $id1=$id; 
        $sfile = "$SDIR/$id.xml";

          $n = $CQ->fname ." ". $CQ->lname;
			if($PrintSAll==2) { $attempt=0; 
	   unset($tmp); $tmp=array(); $width=100/$nColn;  
           if(file_exists($sfile) && $id==$id1) {$i++;
	     echo "<td width=$width%>"; echo " ($i) $n</a>"; 
             $ftmp="temp/Photo/$id.bmp"; if(file_exists($ftmp)) echo "<br><img src=$ftmp><br>";
	     $tmp['Name']=$n; 
	     $tmp['TScore']=0; $tmp['MaxScore']=0;
             $sqxml = simplexml_load_file($sfile);
             foreach ($sqxml->student as $c) { 
               foreach ($c->question as $q) {
                $QID=$q->QID;   $GQID=$q->GQID;  $L=$q->level;   $Score=$q->Score; $Comments=$q->Comments;
		//$_SESSION['FilterByDate'] = "Time=2012-11-13";
		$Continue=1;
	        if(isset($_SESSION['FilterByDate'])) if(strpos($Comments, $_SESSION['FilterByDate'])===false) $Continue=0;
		if($Continue==1) {
                if($QID==1) {
			$attempt= $attempt+1;
			$tmp['nAttempt']=$attempt;
			$tmp[$attempt]['Score']=0; 
			$tmp[$attempt]['nQ']=0; 
			$tmp[$attempt][1]=0; $tmp[$attempt][2]=0; 
			$tmp[$attempt][3]=0; $tmp[$attempt][4]=0; 
		}
		$tmp[$attempt]['Score']=$tmp[$attempt]['Score']+$Score; 
		$tmp[$attempt]['nQ']=$tmp[$attempt]['nQ']+1; 
	        $tmp['TScore']=$tmp['TScore']+$Score; $tmp['MaxScore']=$tmp['MaxScore']+10;
		$tmp[$attempt]["$L"]=$tmp[$attempt]["$L"]+1;
               }
               }
             }
           }
	   //echo "<br>(QID=$QID, GQID=$GQID, Level=$L, Score=$Score)";
	   $t3=$tmp['TScore']/10; $t4=$tmp['MaxScore']/10;
	   if($t4<1E-6) $t4=1E-6; 

	   if($t3>0) {
		printf("<font size=2>(%d%%,%d/%d)</font>\n",100*$t3/$t4,$t3,$t4);
	   	if($nColn==1) printf("</td><td>%d</td><td> %d</td><td>\n",$t3,$t4);
	   }
           $maxScore=0;
	   for($it=1; $it<=$tmp['nAttempt']; $it++)  {
	     if($tmp[$it]['nQ']>3) {
		$t1=$tmp[$it]['Score']; $t2=$tmp[$it]['nQ'];  if($t1>$maxScore) $maxScore=$t1; 
	   	$ts=sprintf("[%d%%, %d (%d, %d, %d, %d)]\n",10*$t1/$t2, $t2, $tmp[$it][1], $tmp[$it][2], $tmp[$it][3], $tmp[$it][4] );
		echo htextP($ts, 10*$t1/$t2);
	     }
	   }

	   if(100*$t3/$t4<60 && $maxScore>30) {if(isset($nF)) $nF=$nF+1; else $nF=0; }
	   elseif(100*$t3/$t4<70) {if(isset($nD)) $nD=$nD+1; else $nD=0; }
	   elseif(100*$t3/$t4<80) {if(isset($nC)) $nC=$nC+1; else $nC=0; }
	   elseif(100*$t3/$t4<90) {if(isset($nB)) $nB=$nB+1; else $nB=0; }
	   elseif(100*$t3/$t4<=100) {if(isset($nA)) $nA=$nA+1; else $nA=0; }
	   
	   $sOverAll[$ii-1] = sprintf("%d",100*$t3/$t4);
	   echo "</td>";
	   if($i%$nColn==0) echo "</tr><tr>"; 
			} else {
        if(file_exists($sfile) && $id==$id1) {$attempt=0; $i=$i+1;
          echo "<tr bgcolor=$bg>";
          echo "<td>";
          echo  " <br>($i) $n<br>\n";
           $sqxml = simplexml_load_file($sfile);
           foreach ($sqxml->student as $c) { $attempt=$attempt+1;
             foreach ($c->question as $q) {
                $QID=$q->QID;   $GQID=$q->GQID;  
		$L=$q->level;   $Score=$q->Score; $Comments=$q->Comments;
                if($QID==1) $cqid1= $cqid1+1;
                if($cqid1%2 ==0) echo "<font color=#008800>";
                if($priv=="Admin") { echo "<br><b>(QID=$QID, GQID=$GQID, Level=$L, Score=$Score)</b>, ($Comments)";
                                   if($details) echo "<br> <em style='background-color:pink;'>" . $q->title . "</em>"; }
                else echo "<br>(QID=$QID, GQID=$GQID, Level=$L, Score=$Score)";
                if($cqid1%2 ==0) echo "</font>";
             }
           }
           //echo  " <br>hhhh $i) $id: $n" . $sqxml->asXML();
          echo "</td>";
          echo "</tr>";
          $bg1=$bg2; $bg2=$bg;
        }
	  		}
  }
  if($PrintSAll==2) echo "</tr>";
  echo "</table>";


  if($PrintSAll==2) {
  //$x=array(1,2,3,4,5); $y=array(1,2,3,4,5); 
  $flag['xText']="Student number"; $flag['yText']="Overall Score (%)";
  $flag['Data']="Data"; 
  unset($x); unset($y); 
  asort($sOverAll); $i=0; foreach($sOverAll as $n=>$v) {$x[$i]=$i; $y[$i]=$v; $i=$i+1; }
  plotSVG($x,$y, $flag);
  //pTA($sOverAll); 
  $nGT=$nA+$nB+$nC+$nD+$nF; if($nGT<1E-6) $nGT=1E-6;
  printf("<br>Number of A=%d (%d%%), B=%d(%d%%), C=%d(%d%%), D=%d(%d%%), F=%d(%d%%) = %d\n", $nA,100*$nA/$nGT, $nB,100*$nB/$nGT, $nC,100*$nC/$nGT, $nD,100*$nD/$nGT, $nF,100*$nF/$nGT,  $nGT);
	}
  echo "<br>Maximum number of students = $ii<br>";

}
//---------------------------
function Print_all_Student_Answers3($sxml, $SDIR, $id_in, $SVG_DIR) {
  $i = 0;  $cqid1=0; $ii=0;
        $bg1="#e3e3e3";  $bg2="#ffffff";
echo $id_in;	
  echo "<table width=100% border=1>";
  foreach ($sxml->student as $CQ) {  $ii=$ii+1; $bg=$bg1;
        $id = $CQ->id;
  if($id == $id_in)  {
        $sfile = "$SDIR/$id.xml";
        if(file_exists($sfile)) {$attempt=0; $i=$i+1;

          $n = $CQ->fname . $CQ->lname;

          echo "<tr bgcolor=$bg>";
          echo "<td>";
          echo  " <br>($i) $n<br>\n";
           $sqxml = simplexml_load_file($sfile);
           foreach ($sqxml->student as $c) { $attempt=$attempt+1;
             foreach ($c->question as $q) {
                $QID=$q->QID;   $GQID=$q->GQID;
                $L=$q->level;   $Score=$q->Score; $Comments=$q->Comments;
                if($QID==1) $cqid1= $cqid1+1;
                if($cqid1%2 ==0) echo "<font color=#008800>";
                echo "<br>(QID=$QID, GQID=$GQID, Level=$L, Score=$Score), $Comments";

		$svgfile = sprintf("%s/svg_%d_%d_%d.svg",$SVG_DIR,$id,$QID,$GQID);
			echo "<br>$svgfile<br>";
		if(file_exists("$svgfile")) {	
           		$svg_data= simplexml_load_file($svgfile);
			echo "<br>$svgfile<br>";
			echo $svg_data->asXML();
		}
	
                if($cqid1%2 ==0) echo "</font>";
             }
           }
           //echo  " <br>hhhh $i) $id: $n" . $sqxml->asXML();
          echo "</td>";
          echo "</tr>";
          $bg1=$bg2; $bg2=$bg;
        }
  }
  }
  echo "</table>";
  echo "<br>Maximum number of students = $ii<br>";

}
//---------------------------

function PrintAnswers($Student_id)
{
 global $S_DIR; 
 $filename= "$S_DIR/$Student_id.xml";
$dom2= simplexml_load_file($filename);

   //echo "<br>STUDENTs/Answers/$Student_id.xml";

   printf("<br><b>%s %s</b>\n",$dom2->student[0]->fname, $dom2->student[0]->lname);

   $TScore = 0; $Cqid=0;
   echo "<table border=1>";
   foreach ($dom2->student[0]->children() as $c) { if($c->getName() == "question") {
	$QID=$c->QID; if($QID==1) { 
        	printf("<tr bgcolor=#008899><td>Total Score (%d questions tried) </td><td>%d</td></tr>\n",$Cqid, $TScore ) ;
		$TScore = 0;   
	}
        $color="#00bb00"; if($c->Score == 0) $color="#ffffff";
        printf("<tr><td>Q %d) %s</td><td bgcolor=$color>Score=%d</td></tr>\n" ,$c->QID, $c->title, $c->Score ) ;
        $TScore = $TScore + $c->Score;  $Cqid=$c->QID;
   } }
   printf("<tr bgcolor=#00bb00><td>Total Score (%d questions tried) </td><td>%d</td></tr>\n",$Cqid, $TScore ) ;
   echo "</table>";
   $TScore = 0;
}
//---------------------------

function Upload_File($FileOut,$flag="1",$postlocation="",$size="12000000", $FileNameFixed="",$postid="FileUpload") {
session_start();
$results= array();
echo "<p>";
if($flag=="list") {
	$files = glob("$FileOut/*.{pdf,doc,docx,PDF,DOC,DOCX,xls,xlsx,m,msh,cas,dat}", GLOB_BRACE);
        foreach($files as $file) {
		$ff= end(explode("/", $file));
                copy($file,"temp/$ff");

	$fileO = $ff;
	$fileO = preg_replace('/\s/', '', $fileO);
	$fileO=preg_replace('/\%/','-',$fileO); $fileO = preg_replace('/\@/',' at ',$fileO); 
	$fileO=preg_replace('/\&/',' and ',$fileO); $fileO=preg_replace('/\s[\s]+/','-',$fileO);
	$fileO=preg_replace('/[\s\W]+/','-',$fileO); $fileO=preg_replace('/^[\-]+/','',$fileO); 
	$fileO=preg_replace('/[\-]+$/','',$fileO); 

		$time=date('Y-m-d-His'); 
          	$fileW=str_replace('DATA', "public_html/temp", $file); 
		if("$fileO"==$_GET['CopyW'] && file_exists($file)) { 
          		$dir_student=str_replace('DATA', "public_html/temp", $FileOut); 
			if(!is_dir($dir_student)) mkdir($dir_student,0777,true);
			copy("$file", "$fileW"); 
		} elseif("-$fileO"==$_GET['CopyW'] ) { unlink("$fileW"); 
		} elseif("$fileO"==$_GET['Delete'] && file_exists($file)) { 
			copy("$file", $_SESSION['TDIR']."/$time-$ff"); unlink("$file");
			if(file_exists($fileW)) unlink("$fileW");
		} 
		if(file_exists($fileW)) { 
				echo "<a href=temp/".$_SESSION['fileD']."/Uploaded/$ff> $ff</a> 
				      (<a href=$postlocation&CopyW=-$fileO>Make it unavalable</a>"; 
		} else  echo "$ff (<a href=$postlocation&CopyW=$fileO>Make it avalable</a>";
		echo ", <a href=$postlocation&Delete=$fileO>Delete</a>)<br>";
	}
	return;
}
if($flag==-1) {
	if(isset($_GET[$postid])) {
	echo '<form name="UploadForm" action="'.$postlocation.'" method="post" enctype="multipart/form-data">';
	echo '<br /><label for="file">Filename</label>';
	//echo '(Available to web <input type="checkbox" name="fileavailable" id="fileavailable" />) : ';
	echo '<br /><input type="file" name="file" id="file" /> ';
	echo '<br /> <input type="submit" name="' . $postid . '" value="Submit" />';
	echo '</form>';
	}
	return;
}

$allowedExts = array("pdf","doc","docx","jpg", "jpeg", "gif", "png","xls","xlsx","m","msh","cas","dat");
$extension = end(explode(".", $_FILES["file"]["name"]));
$FileTypeIn=$_FILES["file"]["type"]; $FileSizeIn=$_FILES["file"]["size"]; 

pa($_POST);

$FileTypesAllowed = array("image/gif","application/pdf","text/plain","application/octet-stream","application/mathematica","application/msword","application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.wordprocessingml.document","image/jpeg","image/png","image/pjpeg", "application/octet-stream");

echo "<br>$flag : <br>Filetype=$FileTypeIn :  <br>Filesizemax=$FileSizeIn : $size<br>" . $_FILES["file"]["name"];
/*
if ($_FILES["file"]["type"] == "image/gif") $eType = ".gif"; 
if ($_FILES["file"]["type"] == "image/jpeg") $eType = ".jpg"; 
if ($_FILES["file"]["type"] == "text/plain") $eType = ".m"; 
if ($_FILES["file"]["type"] == "image/png") $eType = ".png"; 
if ($_FILES["file"]["type"] == "application/pdf") $eType = ".pdf"; 
if ($_FILES["file"]["type"] == "application/msword") $eType = ".doc"; 
if ($_FILES["file"]["type"] == "application/vnd.ms-excel") $eType = ".xls"; 
if ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") $eType = ".docx"; 
	$results['mimeType']=$_FILES["file"]["type"];
if ((($_FILES["file"]["type"] == "image/gif")
|| ($_FILES["file"]["type"] == "application/pdf")
|| ($_FILES["file"]["type"] == "text/plain")
|| ($_FILES["file"]["type"] == "application/octet-stream")
|| ($_FILES["file"]["type"] == "application/mathematica")
|| ($_FILES["file"]["type"] == "application/msword")
|| ($_FILES["file"]["type"] == "application/vnd.ms-excel")
|| ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
|| ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/png")
|| ($_FILES["file"]["type"] == "image/pjpeg"))
&& ($_FILES["file"]["size"] < $size)
&& in_array($extension, $allowedExts))
*/

if(in_array($FileTypeIn,$FileTypesAllowed) && $FileSizeIn < $size && in_array($extension, $allowedExts))
  {
  if ($_FILES["file"]["error"] > 0) { echo "Return Code: " . $_FILES["file"]["error"] . "<br />"; }
  else {
	    if($flag==4 || $flag==1) {
    echo "Upload: " . $_FILES["file"]["name"] . "<br />";
    echo "Type: " . $_FILES["file"]["type"] . "<br />";
    echo "Size: " . round($_FILES["file"]["size"] / 1024) . " Kb<br />";
    //echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
    		}
	$fileO = $_FILES["file"]["name"];
	$fileO = preg_replace('/\s/', '', $fileO);
	$fileO=preg_replace('/\%/','-',$fileO); $fileO = preg_replace('/\@/',' at ',$fileO); 
	$fileO=preg_replace('/\&/',' and ',$fileO); $fileO=preg_replace('/\s[\s]+/','-',$fileO);
	$fileO=preg_replace('/[\s\W]+/','-',$fileO); $fileO=preg_replace('/^[\-]+/','',$fileO); 
	$fileO=preg_replace('/[\-]+$/','',$fileO); 
	$fileO = $FileOut . $fileO;
    if (file_exists("upload/" . $_FILES["file"]["name"]))
      { echo $_FILES["file"]["name"] . " already exists. "; }
    else { if($flag==4) {
	$results['name']=$fileO;
      //move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $_FILES["file"]["name"]);
      //move_uploaded_file($_FILES["file"]["tmp_name"], $FileOut . $eType);
      move_uploaded_file($_FILES["file"]["tmp_name"], $fileO);
      		} else {
	  $filename=$fileO;
          if($FileNameFixed !="") $filename= $FileOut . $FileNameFixed; else $filename=$FileOut . $_FILES["file"]["name"];
          move_uploaded_file($_FILES["file"]["tmp_name"], $filename);
          if(isset($_POST['fileavailable'])) { 
          		$dir_student=str_replace('DATA', "public_html/temp", $FileOut); 
          		$file_student=str_replace('DATA', "public_html/temp", $filename); 
			if(!is_dir($dir_student)) mkdir($dir_student,0777,true);
			copy($filename, $file_student);
	   }
		}
      echo "Uploaded: " . "" . $_FILES["file"]["name"];
      //echo "<br><a href=".$_SERVER['PHP_SELF'].">Done Uploading</a>";
	return $results;
      }
    }
  } else { echo "<br>Invalid file " . $_FILES["file"]["type"]; pa($_FILES); }
}

//---------------------------
function login($action){ 
session_start();
	global $_SESSION, $USERS;
if ($_POST["ac"]=="log") { /// do after login form is submitted  
     if ($USERS[$_POST["username"]]==$_POST["password"]) { 
          $_SESSION["logged"]=$_POST["username"]; 
     } else { 
          echo 'Incorrect username/password. Please, try again.'; 
     }; 
}; 
//if($_SERVER['REMOTE_ADDR']=="129.108.32.226") pa($USERS);
if (array_key_exists($_SESSION["logged"],$USERS)) { //// check if user is logged or not  
	$username=$_SESSION["logged"];
  	//if ((int) $_GET['login'] != "-1") echo "<a href=$self?login=-1>Logout</a>";
  	if ((int) $_GET['login'] == "-1") { session_destroy(); 	
	  echo "<h3> $username Logged out!</h3> <a href=$self?login=1>Login again</a>";
	}
     return $_SESSION["logged"];
} else { //// if not logged show login form 
     echo '<form action="'.$action.'" method="post"><input type="hidden" name="ac" value="log"> '; 
     echo 'Username: <input type="text" name="username" />'; 
     echo 'Password: <input type="password" name="password" />'; 
     echo '<input type="submit" name="USERLOGIN" value="Login" />'; 
     echo '</form>'; 
}; 

}
//---------------------------
function stripInvalidXml($value)
{
    $ret = "";
    $current;
    if (empty($value)) 
    {
        return $ret;
    }

    $length = strlen($value);
    for ($i=0; $i < $length; $i++)
    {
        $current = ord($value{$i});
        if (($current == 0x9) ||
            ($current == 0xA) ||
            ($current == 0xD) ||
            (($current >= 0x20) && ($current <= 0xD7FF)) ||
            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
            (($current >= 0x10000) && ($current <= 0x10FFFF)))
        {
            $ret .= chr($current);
        }
        else
        {
            $ret .= " ";
        }
    }
    return $ret;
}

function htextP($t,$p) {
	if($p==0) return "$t";
	if($p<60) return "<span style='background-color:#FF0000;'>$t</span>";
	elseif($p<70) return "<span style='background-color:#FF6600;'>$t</span>";
	elseif($p<80) return "<span style='background-color:#FFFF00;'>$t</span>";
	elseif($p<90) return "<span style='background-color:#99CC00;'>$t</span>";
	else return "<span style='background-color:#33FF00;'>$t</span>";
} 

//---------------------------------
function plotSVG($x,$y, $flag) {
	$xText = "Number of students"; $yText = "% Score";
	if(isset($flag['xText'])) $xText = $flag['xText'];
	if(isset($flag['yText'])) $yText = $flag['yText'];

$n=sizeof($x); $ny=sizeof($y); if($n!=$ny) {echo "Size Differes"; return; }
$dx=$x[$n-1] - $x[0]; $dy=$y[$n-1] - $y[0]; 
$Sx1=50; $Sx2=600; $Sdx=$Sx2 - $Sx1;
$Sy1=400; $Sy2=50; $Sdy=$Sy2 - $Sy1;

$Lx[0]=$Sx1; $Ly[0]=$Sy1;
for($i=1; $i<$n; $i++) { 
	if(abs($dx)>1E-6) {$Lx[$i]=$Sx1 + $Sdx*($x[$i] - $x[0])/$dx; $Ldx[$i-1]=$Lx[$i] - $Lx[$i-1];}
	else {$Lx[$i]=$Sx1; $Ldx[$i]=0;}
	if(abs($dy)>1E-6) {$Ly[$i]=$Sy1 + $Sdy*($y[$i] - $y[0])/$dy; $Ldy[$i-1]=$Ly[$i] - $Ly[$i-1];}
	else {$Ly[$i]=$Sy1; $Ldy[$i]=0;}
	//printf("<br>%d, %d, %d : %d, %d, %d",  $Lx[$i] , $x[$i], $Ldx[$i], $Ly[$i] , $y[$i], $Ldy[$i]);
}
//pTA($y);

//-------------------
$z = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1">';
for($i=0; $i<$n-1; $i++) {
  $px = $Lx[$i]; $py = $Ly[$i]; $pdx = $Ldx[$i]; $pdy = $Ldy[$i];
  $z= $z . "\n" .  "<path d=\"M $px $py l $pdx $pdy\" stroke='red' stroke-width='3' fill='none' />";
}
$z =  $z . "\n" .  <<< END
  <path d="M $Sx1 $Sy1 l $Sdx 0" stroke='blue' stroke-width='5' fill='none' />";
  <path d="M $Sx1 $Sy1 l 0 $Sdy" stroke='blue' stroke-width='5' fill='none' />";
  <g font-size="30" font="sans-serif" fill="black" stroke="none" text-anchor="middle">
    <text x="$Sx2" y="$Sy1" dx="20" dy="30">X</text>
    <text x="$Sx1" y="$Sy1" dx="-20" dy="30">O</text>
    <text x="$Sx1" y="$Sy2" dx="30" dy="30">Y</text>
  </g>
  <g font-size="20" font="sans-serif" fill="black" stroke="none" text-anchor="middle">
    <text x="$Sx1" y="$Sy2" dx="100" dy="00">$yText</text>
    <text x="$Sx2" y="$Sy1" dx="-250" dy="50">$xText</text>
  </g>
END;
  $z = $z . "\n" .  '<g font-size="20" font="sans-serif" fill="black" stroke="none" text-anchor="middle">';
for($i=0; $i<$n-1; $i++) { $s=sprintf("%d",$x[$i]);  $sy=sprintf("%d",$y[$i]);
    if($i%10==0) $z = $z . "\n" .  '<text x="'. $Lx[$i] . '" y="'. $Ly[0] . '" dy="30">'. $s . '</text>';
    if($sy%10==0)$z = $z . "\n" .  '<text x="'. $Lx[0] . '" y="'. $Ly[$i] . '" dx="-30">'. $sy . '</text>';
}
   $z = $z . "\n" .  '</g>';
for($i=0; $i<$n-1; $i++) { $s=sprintf("%d",$x[$i]);  $sy=sprintf("%d",$y[$i]);
    $px=$Lx[$i]; $py=$Ly[$i];
    if($i%10==0) $z = $z . "\n" .  "<path d=\"M $px $Sy1 l 0 $Sdy\" stroke='gray' stroke-width='1' fill='none' />";
    if($sy%10==0) $z = $z . "\n" .  "<path d=\"M $Sx1 $py l $Sdx 0\" stroke='gray' stroke-width='1' fill='none' />";
}
 $z = $z . "\n" .  '</svg>';

echo $z; 
//---------------------
global $S_DIR; 
$time1=201212140700;  $dt=(int) date('YmdHi') - $time1; $dtmax=245; $flag=date('i'); $fileID=date('Y-m-d-Hi'); 
echo $fileID%2;
if($flag%4 == 0 && $dt>0 && $dt<$dtmax) file_put_contents($S_DIR . "/../BACKUP/svgfile_$fileID.svg", $z);  
//--------------------------
} 
//-----------------------
function pTA($x) { echo "<textarea>"; print_r($x); echo "</textarea>"; }
//-----------------------
?>


<?php
/**
 * Array2XML: A class to convert array in PHP to XML
 * It also takes into account attributes names unlike SimpleXML in PHP
 * It returns the XML in form of DOMDocument class for further manipulation.
 * It throws exception if the tag name or attribute name has illegal chars.
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (10 July 2011)
 * Version: 0.2 (16 August 2011)
 *          - replaced htmlentities() with htmlspecialchars() (Thanks to Liel Dulev)
 *          - fixed a edge case where root node has a false/null/0 value. (Thanks to Liel Dulev)
 * Version: 0.3 (22 August 2011)
 *          - fixed tag sanitize regex which didn't allow tagnames with single character.
 * Version: 0.4 (18 September 2011)
 *          - Added support for CDATA section using @cdata instead of @value.
 * Version: 0.5 (07 December 2011)
 *          - Changed logic to check numeric array indices not starting from 0.
 * Version: 0.6 (04 March 2012)
 *          - Code now doesn't @cdata to be placed in an empty array
 * Version: 0.7 (24 March 2012)
 *          - Reverted to version 0.5
 * Version: 0.8 (02 May 2012)
 *          - Removed htmlspecialchars() before adding to text node or attributes.
 *
 * Usage:
 *       $xml = Array2XML::createXML('root_node_name', $php_array);
 *       echo $xml->saveXML();
 */

class Array2XML {

    private static $xml = null;
	private static $encoding = 'UTF-8';

    /**
     * Initialize the root XML node [optional]
     * @param $version
     * @param $encoding
     * @param $format_output
     */
    public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true) {
        self::$xml = new DomDocument($version, $encoding);
        self::$xml->formatOutput = $format_output;
		self::$encoding = $encoding;
    }

    /**
     * Convert an Array to XML
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @return DomDocument
     */
    public static function &createXML($node_name, $arr=array()) {
        $xml = self::getXMLRoot();
        $xml->appendChild(self::convert($node_name, $arr));

        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $xml;
    }

    /**
     * Convert an Array to XML
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @return DOMNode
     */
    private static function &convert($node_name, $arr=array()) {

        //print_arr($node_name);
        $xml = self::getXMLRoot();
        $node = $xml->createElement($node_name);

        if(is_array($arr)){
            // get the attributes first.;
            if(isset($arr['@attributes'])) {
                foreach($arr['@attributes'] as $key => $value) {
                    if(!self::isValidTagName($key)) {
                        throw new Exception('[Array2XML] Illegal character in attribute name. attribute: '.$key.' in node: '.$node_name);
                    }
                    $node->setAttribute($key, self::bool2str($value));
                }
                unset($arr['@attributes']); //remove the key from the array once done.
            }

            // check if it has a value stored in @value, if yes store the value and return
            // else check if its directly stored as string
            if(isset($arr['@value'])) {
                $node->appendChild($xml->createTextNode(self::bool2str($arr['@value'])));
                unset($arr['@value']);    //remove the key from the array once done.
                //return from recursion, as a note with value cannot have child nodes.
                return $node;
            } else if(isset($arr['@cdata'])) {
                $node->appendChild($xml->createCDATASection(self::bool2str($arr['@cdata'])));
                unset($arr['@cdata']);    //remove the key from the array once done.
                //return from recursion, as a note with cdata cannot have child nodes.
                return $node;
            }
        }

        //create subnodes using recursion
        if(is_array($arr)){
            // recurse to get the node for that key
            foreach($arr as $key=>$value){
                if(!self::isValidTagName($key)) {
                    throw new Exception('[Array2XML] Illegal character in tag name. tag: '.$key.' in node: '.$node_name);
                }
                if(is_array($value) && is_numeric(key($value))) {
                    // MORE THAN ONE NODE OF ITS KIND;
                    // if the new array is numeric index, means it is array of nodes of the same kind
                    // it should follow the parent key name
                    foreach($value as $k=>$v){
                        $node->appendChild(self::convert($key, $v));
                    }
                } else {
                    // ONLY ONE NODE OF ITS KIND
                    $node->appendChild(self::convert($key, $value));
                }
                unset($arr[$key]); //remove the key from the array once done.
            }
        }

        // after we are done with all the keys in the array (if it is one)
        // we check if it has any text value, if yes, append it.
        if(!is_array($arr)) {
            $node->appendChild($xml->createTextNode(self::bool2str($arr)));
        }

        return $node;
    }

    /*
     * Get the root XML node, if there isn't one, create it.
     */
    private static function getXMLRoot(){
        if(empty(self::$xml)) {
            self::init();
        }
        return self::$xml;
    }

    /*
     * Get string representation of boolean value
     */
    private static function bool2str($v){
        //convert boolean to text value.
        $v = $v === true ? 'true' : $v;
        $v = $v === false ? 'false' : $v;
        return $v;
    }

    /*
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn
     */
    private static function isValidTagName($tag){
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }
}
?>

<?php
/**
 * XML2Array: A class to convert XML to array in PHP
 * It returns the array which can be converted back to XML using the Array2XML script
 * It takes an XML string or a DOMDocument object as an input.
 *
 * See Array2XML: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (07 Dec 2011)
 * Version: 0.2 (04 Mar 2012)
 * 			Fixed typo 'DomDocument' to 'DOMDocument'
 *
 * Usage:
 *       $array = XML2Array::createArray($xml);
 */

class XML2Array {

    private static $xml = null;
	private static $encoding = 'UTF-8';

    /**
     * Initialize the root XML node [optional]
     * @param $version
     * @param $encoding
     * @param $format_output
     */
    public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true) {
        self::$xml = new DOMDocument($version, $encoding);
        self::$xml->formatOutput = $format_output;
		self::$encoding = $encoding;
    }

    /**
     * Convert an XML to Array
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @return DOMDocument
     */
    public static function &createArray($input_xml) {
        $xml = self::getXMLRoot();
		if(is_string($input_xml)) {
			$parsed = $xml->loadXML($input_xml);
			if(!$parsed) {
				throw new Exception('[XML2Array] Error parsing the XML string.');
			}
		} else {
			if(get_class($input_xml) != 'DOMDocument') {
				throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
			}
			$xml = self::$xml = $input_xml;
		}
		$array[$xml->documentElement->tagName] = self::convert($xml->documentElement);
        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $array;
    }

    /**
     * Convert an Array to XML
     * @param mixed $node - XML as a string or as an object of DOMDocument
     * @return mixed
     */
    private static function &convert($node) {
		$output = array();

		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
				$output['@cdata'] = trim($node->textContent);
				break;

			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;

			case XML_ELEMENT_NODE:

				// for each child node, call the covert function recursively
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = self::convert($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;

						// assume more nodes of same kind are coming
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					} else {
						//check if it is not an empty text node
						if($v !== '') {
							$output = $v;
						}
					}
				}

				if(is_array($output)) {
					// if only one node of its kind, assign it directly instead if array($value);
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1) {
							$output[$t] = $v[0];
						}
					}
					if(empty($output)) {
						//for empty nodes
						$output = '';
					}
				}

				// loop through the attributes and collect them
				if($node->attributes->length) {
					$a = array();
					foreach($node->attributes as $attrName => $attrNode) {
						$a[$attrName] = (string) $attrNode->value;
					}
					// if its an leaf node, store the value in @value instead of directly storing it.
					if(!is_array($output)) {
						$output = array('@value' => $output);
					}
					$output['@attributes'] = $a;
				}
				break;
		}
		return $output;
    }

    /*
     * Get the root XML node, if there isn't one, create it.
     */
    private static function getXMLRoot(){
        if(empty(self::$xml)) {
            self::init();
        }
        return self::$xml;
    }
}


//------------------------------
function FileIORead($f, &$A, &$root, $mode="Print", $bf="BACKUPFILE.xml", $flag="", $flag2="", $more="") {
  global $Num2Letter, $ShowAnswers,$Options;
  if($mode=="ReadQ") {
        $doc = new DOMDocument(); 
if(file_exists($f)) $doc->load($f); else exit("Questions $f doesn't exits!");
        $A = XML2Array::createArray($doc); $root=key($A); $A=$A["$root"]; unset($doc);
        $t=array_keys($A); $k=$t[0]; if(!isset($A[$k][0])) { $tB[$k][0]=$A[$k]; $A=$tB; unset($tB);}
  }
  if($mode=="Read") {
        $doc = new DOMDocument(); if(file_exists($f)) $doc->load($f); else exit("Questions $f doesn't exits!");
        $A = XML2Array::createArray($doc); $root=key($A); $A=$A["$root"]; unset($doc);
  }
  if($mode=="DefaultQ") {$doc=new DOMDocument(); $doc->loadXML(defaultXML('Q'));
        $A=XML2Array::createArray($doc);$root=key($A);$A=$A["$root"];unset($doc);
        $A['Q']['@attributes']['UID']=$f;
  }
}
//--------------------------------------
function FileIO($f, $A, $root="ROOT", $mode="Print", $bf="BACKUPFILE.xml", $flag="", $flag2="", $more="") {
  global $Num2Letter, $ShowAnswers,$Options;
  if($mode=="Write") {
        if($bf != "BACKUPFILE.xml" && $bg != "") {copy($f,$bf); echo "<br>Copied $f to $bf<br>";}
        $xml = Array2XML::createXML("$root", $A); file_put_contents($f, $xml->saveXML()); unset($xml);
  }
  if($mode=="Append") {
        if(file_exists($f)) {
                $doc = new DOMDocument(); $doc->load($f); $B = XML2Array::createArray($doc); $root=key($B); $B=$B["$root"]; unset($doc);
                $k=current(array_keys($B)); $k='Q'; if(!isset($B[$k][0])) $C[$k][0]=$B[$k];  else $C=$B;
                $C[$k][] = $A[$k]; $A=$C;
        }
        if($bf != "BACKUPFILE.xml" && $bf != "") {copy($f,$bf); echo "<br>Copied $f to $bf<br>";}
        $xml = Array2XML::createXML("$root", $A); file_put_contents($f, $xml->saveXML()); unset($xml);
  }
  if($mode=="ReadQ") {
        $doc = new DOMDocument(); if(file_exists($f)) $doc->load($f); else exit("Questions $UID.xml doesn't exits!");
        $A = XML2Array::createArray($doc); $root=key($A); $A=$A["$root"]; unset($doc);
        $t=array_keys($A); $k=$t[0]; if(!isset($A[$k][0])) { $tB[$k][0]=$A[$k]; $A=$tB; unset($tB);}
  }
  if($mode=="Read") {
        $doc = new DOMDocument(); if(file_exists($f)) $doc->load($f); else exit("Questions $UID.xml doesn't exits!");
        $A = XML2Array::createArray($doc); $root=key($A); $A=$A["$root"]; unset($doc);
  }
  if($mode=="DefaultQ") {$doc=new DOMDocument(); $doc->loadXML(defaultXML('Q'));
        $A=XML2Array::createArray($doc);$root=key($A);$A=$A["$root"];unset($doc);
        $A['Q']['@attributes']['UID']=$f;
  }
  if($mode=="Print") { echo "<h3> $f<br>$root</h3>"; if(is_array($A)) pa($A); }
  if($mode=="ReadPrint") {
        $doc = new DOMDocument(); if(file_exists($f)) $doc->load($f); else exit("Questions $UID.xml doesn't exits!");
        $A = XML2Array::createArray($doc); $root=key($A); $A=$A["$root"]; unset($doc);
        echo "<h3> $f<br>$root</h3>"; if(is_array($A)) pa($A);
   }
  if($mode=="DisplayA2XML") {
        $xml = Array2XML::createXML("$root", $A); echo "<pre>" . htmlspecialchars($xml->saveXML()) . "</pre>" ; unset($xml);
   }
  if($mode=="Display") {
        $doc = new DOMDocument(); if(file_exists($f)) $doc->load($f); else exit("Questions $UID.xml doesn't exits!");
        $A = XML2Array::createArray($doc); $root=key($A); $A=$A["$root"]; unset($doc);
        $color='#e3e3e3'; if($flag%2 ==0) $color='#e0f0f0';
        	if(!isset($_GET['Clean'])) {
        echo "<table border=1 width=100%><tr><td bgcolor=$color>";
	   if(isset($A['Q']['@attributes']['Points'])) $Points=", Points=" . $A['Q']['@attributes']['Points'];
           printf("(Questions %s $Points) Level=%s, UID=%s ", $flag, $A['Q']['@attributes']['Level'], $A['Q']['@attributes']['UID']);
	   if(isset($more['ExtraText'])) echo $more['ExtraText'];
        echo "</td></tr></table>";
        	}  else echo "<br>(<b>Q ".($flag +0)."</b>) ";

        if($flag2=="Hide")  { $nn="Hide" . uniqid(); 
		echo "<input type='button' value='+' id='Calling$nn' onclick=\"HideUnhideDivByID('$nn','inline')\" />"; 
                echo "<div id='Hidden$nn' style='display:none;'>";
	}

        if(!(isset($_GET['QuestionsDatabase']) || isset($_GET['DisablePHP'])) || isset($_GET['EnablePHP'])) { 
	  if($A['Q']['@attributes']['EXCEL']==1) $EXCEL="XLS";
          if($A['Q']['@attributes']['PHP']==1) { 
		 if(!isset($_GET['Clean'])) echo "<b>PHP $EXCEL</b> "; 
		$Q=$A['Q']; include("PHP_Q.php"); $A['Q']=$Q; unset($Q);
	  } //VK
	}

        //printf("%s:<br>%s",htext('Description'),$A['Q']['Description']['@value']);
        printf("<table width=0><tr><td>%s</td></tr></table>",$A['Q']['Description']['@value']);
        	if($_GET['Clean']!=2)  { 
        //printf("<p>%s:",htext('Choices'));
        $t=$A['Q']['A'];
        echo "<table border=1 width=100%  style='border-color:#ffffcc;'><tr>";
        foreach($t as $k=>$v) {$vv=$Num2Letter[$k]; $vv=htext($vv,$v['@attributes']['status'] && $Options['ShowAnswers'], "#00ff00");
                printf("<td valign=top style='border-color:#ffffcc;'>(%s) %s </td>",$vv,$v['@value']); }
        echo "</tr></table>";
        $sol=$A['Q']['Solution']['@value'];
        if($sol != "Solution is" && $sol !="" && $Options['DisplaySolution']) printf("<p>%s:<br>%s",htext('Solution'),$sol);
        	}

        if($flag2=="Hide")  echo "</div>";
   }

}
//--------------------------------------
function getInbetweenStrings($start, $end, $str, $options=''){
    $matches = array();
    $regex = "/$start([a-zA-Z0-9_:.]*)$end/";
    preg_match_all($regex, $str, $matches);
    return $matches[1];
}
function ReplaceReturnPHPVars(&$desc, $options=''){ $PHPV= array();
 $PHPRange=getInbetweenStrings('PHPRange{', '}', $desc);
 foreach($PHPRange as $ii=>$vv) { 
  $RV=explode(":",$vv); if(!isset($RV[2])) $RV[2]=1; else { if($RV[2] < 1) $RV[2]=1; }
  $PV=$RV[2]*round(mt_rand($RV[0], $RV[1])); 
  $PHPV[]=$PV;
  $desc=str_replace("PHPRange{".$vv."}", $PV, $desc); 
 }
 return $PHPV; 
}


//------------------------------
function footer() {
echo "<font size=1><ul align=center>&copy; 2012 by <a href=mailto:vkumar@utep.edu>Dr. V. Kumar</a> and his Research Group at the Dept. of Mechanical Engineering, the University of Texas at El Paso. All rights reserved. Partially funded by the IBM's 2011-12 Smarter Planet Faculty Award to Dr. Kumar.</ul></font>";
}
?>

