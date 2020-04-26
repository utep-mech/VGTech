<?php
include("$HOME/COMMON/common-old.php");  
$arrow = array('U'=>'&#8593;', 'D'=>'&#8595;', 'L'=>'&#8592;', 'R'=>'&#8594;'); 
$PData = $_POST['send']; $outputid = $PData['outputid']; $idtmp = 'inside-Monitor';  $CourseID= $PData['COURSE']; 
$LoadPHP = $PData['LoadPHP'];  $CDir = "$DATA/COURSES/$CourseID"; $AHome = "$CDir/ASSESSMENT"; 
$filecsv = "$AHome/AllAssessmentAvg.csv";
//-----------------
if($PData['Save']=='SaveCSV') { //echo "$AHome/AllAssessmentAvg.csv"; 
 file_put_contents($filecsv, $PData['val']);  
 return; 
}

//-----------------
$Roster = json_decode(file_get_contents("$CDir/Roster.json"), true); 
if(file_exists($filecsv))    $csv = array_map('str_getcsv', file($filecsv)); 

$InfoFile="$AHome/OverAllInfo.json";  if(file_exists($InfoFile)) $Table = json_decode(file_get_contents($InfoFile), true); 

if($admin) { 
    echo "<table border=1><tr><td>uid </td><td>sI </td><td>sO</td><td>sE</td><td>sAll </td><td>Grade</td></tr>"; 
    foreach($Roster as $k=>$v) Info_Admin($Table, $csv, $k); 
    echo "</table>"; 
}

$Table1['Students'] = $Table['Students']; 
$Table1[$uid] = $Table[$uid]; 

unset($Table); $Table=$Table1; 
$strI='<tr><td>In class</td><td>% Score</td></tr>'; 
$strO='<tr><td>Out-of-class (Homework)</td><td>% Score</td></tr>'; 
$strE='<tr><td>Mid-terms</td><td>% Score</td></tr>'; 
$TI=0; $TO=0; $nI=0; $nO=0; 
foreach($Table['Students'] as $k=>$v) {
   $n=$v['Name'];  $score=$Table[$uid][$k]['Name']; 
   if(strpos($n, 'I') === 0)   {   $strI .= "<tr><td>$n</td><td>$score</td></tr>";  $TI = $TI + $score + 0; $IScore[$nI]=$score + 0; $nI++; }
   if(strpos($n, 'O') === 0) {   $strO .= "<tr><td>$n</td><td>$score</td></tr>"; $TO = $TO + $score + 0; $OScore[$nO]=$score + 0; $nO++; }
}

$sI8=0; $sO8=0; $sI9=0; $sO9=0; $sE=0; 
foreach(array(0,6,8,9) as $k=>$v) { 
  if($v==8) { $sI8 = round(SumArrayI2toI2($IScore,$v, $nI, 'sort')/($nI-$v) ); $sO8=round(SumArrayI2toI2($OScore,$v, $nO, 'sort')/($nO-$v) ); }
  if($v==9) { $sI9 = round(SumArrayI2toI2($IScore,$v, $nI, 'sort')/($nI-$v) ); $sO9=round(SumArrayI2toI2($OScore,$v, $nO, 'sort')/($nO-$v) ); }
  $strI .= "<tr><th>Avg score after $v drop)</th><th>".round(SumArrayI2toI2($IScore,$v, $nI, 'sort')/($nI-$v) )."</th></tr>"; 
  $strO .= "<tr><th>Avg score after $v drop)</th><th>".round(SumArrayI2toI2($OScore,$v, $nO, 'sort')/($nO-$v) )."</th></tr>"; 
}

//Exam scores
if(file_exists($filecsv)) { 
  //$csv = array_map('str_getcsv', file($filecsv)); 
  foreach($csv as $k=>$v) {
    if($k===0) {foreach($v as $kk=>$vv) { if($vv=='Email') $emailkey=$kk; if($vv=='Mid-terms') $Ekey=$kk; }  continue; }
    $EmailA=explode('@',$v[$emailkey]); 
    if($EmailA[0]==$uid) { $sE=$v[$Ekey];  $strE .= "<tr><td>Avg mid-term exam score (after dropping one)</td><td>".$v[$Ekey]."</td></tr>"; }
  }
  
}

$strI = "<table border=1>$strI</table>"; $strO = "<table border=1>$strO</table>"; 
$strE = "<table border=1>$strE</table>"; 
echo "$uid<br><table border=1><tr valign=top><td>   $strI  </td> <td bgcolor='#e3e3e3'> $strO  </td> <td> $strE</td> </tr> </table>"; 

if($CourseID=='EA1') { $sAll =round(($sI8 * 20 + $sO8*20 + $sE*30)/(20+20+30)); } else $sAll=round(($sI9 * 20 + $sO9*20 + $sE*30)/(20+20+30)); 
if($sE>10) { //dummy blocking
  echo "Overall score=$sAll %."; 
  if($sAll > 90) echo 'You do not need to take the final'; else if($sAll > 80) echo 'You may want to take the final'; else echo 'You must take the final'; 
}

  echo "<br/>Note:<br>Engineering Analysis I: we have agreed to drop 8 in-class and 8 out-of-class assessments."; 
  echo "<br>Engineering Analysis II: we have agreed to drop 9 in-class and 9 out-of-class assessments. "; 
  echo "<br>Please use these as a guideline to calculate your overall score and decide whether you need/want to take the final exam."; 

if($admin) { $uqid=uniqid(); 
  echo "<p/><span id=$uqid><button onclick=\"dimag({'outputid':'$uqid','COURSE':'$CourseID', 'LoadPHP':'$LoadPHP','Save':'SaveCSV','GetValID':'$uqid-TA'})\">Save</button></span><p/>";
  echo "<textarea id=$uqid-TA> </textarea>"; 

}
//-----------------------------------------------
function SumArrayI2toI2($A,$i1, $i2, $flag) { if($flag=='sort') sort($A); $sum=0; for($i=$i1; $i<$i2; $i++) {$sum = $sum + $A[$i]; } return $sum; } 

function Info_Admin($Table,$csv, $uid, $flag='') { 

$TI=0; $TO=0; $nI=0; $nO=0; 
foreach($Table['Students'] as $k=>$v) {
   $n=$v['Name'];  $score=$Table[$uid][$k]['Name']; 
   if(strpos($n, 'I') === 0)   {   $TI = $TI + $score + 0; $IScore[$nI]=$score + 0; $nI++; }
   if(strpos($n, 'O') === 0) {   $TO = $TO + $score + 0; $OScore[$nO]=$score + 0; $nO++; }
}

$sI8=0; $sO8=0; $sI9=0; $sO9=0; $sE=0; 
foreach(array(0,6,8,9) as $k=>$v) { 
  if($v==8) { $sI8 = round(SumArrayI2toI2($IScore,$v, $nI, 'sort')/($nI-$v) ); $sO8=round(SumArrayI2toI2($OScore,$v, $nO, 'sort')/($nO-$v) ); }
  if($v==9) { $sI9 = round(SumArrayI2toI2($IScore,$v, $nI, 'sort')/($nI-$v) ); $sO9=round(SumArrayI2toI2($OScore,$v, $nO, 'sort')/($nO-$v) ); }
}
  
  foreach($csv as $k=>$v) {
    if($k===0) {foreach($v as $kk=>$vv) { if($vv=='Email') $emailkey=$kk; if($vv=='Mid-terms') $Ekey=$kk; }  continue; }
    $EmailA=explode('@',$v[$emailkey]); 
    if($EmailA[0]==$uid) { $sE=$v[$Ekey];   }
  }
  if($CourseID=='EA1') { $sI=$sI8; $sO=$sO8;   } else { $sI=$sI9; $sO=$sO9;   }
  $sAll =round(($sI * 20 + $sO*20 + $sE*30)/(20+20+30));  
  if($sAll>89.4) $Grade='A'; elseif($sAll>79.4) $Grade='B'; elseif($sAll>69.4) $Grade='C'; elseif($sAll>59.4) $Grade='D'; else $Grade='F'; 
  echo "<tr><td>$uid </td><td>$sI </td><td>$sO</td><td>$sE</td><td>$sAll </td><td>$Grade</td></tr>"; 

} 
?>
