<?php
include("$HOME/COMMON/common-old.php");  
$arrow = array('U'=>'&#8593;', 'D'=>'&#8595;', 'L'=>'&#8592;', 'R'=>'&#8594;'); 
$PData = $_POST['send']; $outputid = $PData['outputid']; $idtmp = 'inside-Monitor';  $CourseID= $PData['COURSE']; 
$LoadPHP = $PData['LoadPHP'];  $CDir = "$DATA/COURSES/$CourseID"; $AHome = "$CDir/ASSESSMENT"; 

$Roster = json_decode(file_get_contents("$CDir/Roster.json"), true); 

$InfoFile="$AHome/OverAllInfo.json";  if(file_exists($InfoFile)) $Table = json_decode(file_get_contents($InfoFile), true); 
if($PData['flag']=='toggle') { $AID=$PData['AID']; 
  if($Table['Students'][$AID]['View']=='hide') $Table['Students'][$AID]['View']='display'; else $Table['Students'][$AID]['View']='hide'; 
  file_put_contents($InfoFile, json_encode($Table));
}

echo "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'COURSES/Activity.php', 'COURSE':'$CourseID'}); \">Activity</button> | "; 

?>

<script> 
var plottype='line'; 
if(!highchartloaded) { var highchartloaded=1; 
 $(function () {
      $.getScript( "https://code.highcharts.com/highcharts.js");  
      $.getScript( "https://code.highcharts.com/modules/data.js"); 
      $.getScript( "https://code.highcharts.com/modules/exporting.js");  
      $.getScript( "https://code.highcharts.com/modules/drilldown.js");  
  });
}

//-----------------
function plotVK2(In, e) {   $('#'+In.id).show();   $('#'+In.id).highcharts({ credits: {enabled: false}, chart: { type: 'pie' },  series: [ {  data: In.data  }] });  } 
//----------------
function PlotColn(In, e) {    var pid='container2', series=[], it=0; title='Assessments';   $('#'+pid).show();  // alert(JSON.stringify(w));
  for (var j=In.js; j < In.je - 1; j++){   if( $('#PlotColn-'+j).is(':checked') ) {  var data={}, v=[]; 
       for (var i = In.is; i <= In.ie; i++){ var txt=$('#'+In.id+'-'+i+'-'+j).text();  if(txt =='') v[i-In.is]=0; else v[i-In.is]=Number(txt);   } 
       data.data=v;  data.name=$('#Head-'+j).text(); series[it]=data; it++;  // alert(JSON.stringify(series));
   }  } 
   $('#'+pid).highcharts({ credits: {enabled: false}, chart: { type: plottype }, title: {text: title}, series: series}); 
} 
</script>
<div style='display:none' id="container2" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

<script>
//-----------
function SortVKJS(In, e) { var d=$(e).data(); if(d.direction==-1) d.direction = 1; else d.direction = -1; //alert(d.direction); 
  if(d.direction == -1) $(e).html(d.text1); else $(e).html(d.text2); 
  var  v={}, vsorted={}; //var list = {"0": "C", "1": "R", "2": "", "3": "B"}; alert(Object.keys(list).length); 
  if(In.i=='0') { for (var i = In.is; i <= In.ie; i++){v[i-In.is]=  $('#'+In.id+'-'+i+'-'+In.j).text(); } }
  var knew= Object.keys(v).sort(function(a,b){ var valA = v[a], valB = v[b]; return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);  }); 
  //for (var i = 0; i < Object.keys(v).length; i++){vsorted[i]=v[knew[i]];}
  for (var j = In.js; j <= In.ie; j++) { 
     for (var i = In.is; i <= In.ie; i++){v[i-In.is]=  $('#'+In.id+'-'+i+'-'+j).text(); }
     for (var i = In.is; i <= In.ie; i++){var vnew=v[knew[i-In.is]]; if(d.direction == -1) vnew=v[knew[In.ie-i]];  $('#'+In.id+'-'+i+'-'+j).text(vnew); } 
  }
 //alert(JSON.stringify(vsorted));
}


</script>

<?php

if(!file_exists($InfoFile) || $PData['flag']=='Refresh') { 
 if(isset($PData['AID'])) $AList[0]="$AHome/".$PData['AID']; else $AList=glob("$AHome/A_?????????????"); 

$Table['Students']['Info']['Name'] = "Students";  
foreach($AList as $kk=>$vv) { $key=basename($vv);   $AInfo=json_decode( file_get_contents("$vv/AInfo.json"), true); $Table['Students'][$key]['Name'] =$AInfo['Name']; 
   $G[$kk]=array('A'=>0,'B'=>0,'C'=>0,'D'=>0,'F'=>0, 'N'=>0, 'T'=>0);  
}
  foreach($Roster as $k=>$v) {  $Table[$k]['Info']['Name'] =$k; 
    foreach($AList as $kk=>$vv) { $key=basename($vv);  $MaxScore=1e-6; $Score=0; $nfiles=0; unset($qscore); 
       $AInfo=json_decode( file_get_contents("$vv/AInfo.json"), true); 

       $fQsel="$CDir/STUDENTS/$k/$key/QSelected.json"; if(file_exists($fQsel)) { $AInfo['Questions']=json_decode( file_get_contents("$fQsel"), true); }

       foreach($AInfo['Questions'] as $itmp=>$iq) {  $qfile = "$CDir/STUDENTS/$k/$key/$iq.xml";  $MaxScore += 10; 
           if(file_exists($qfile)) { $nfiles++; FileIORead($qfile, $Q, $root, 'ReadQ');    for($it=0; $it<sizeof($Q['Q']); $it++) $qscore[$it]=$Q['Q'][$it]['@attributes']['Score']; 
             $ScoreThisQ = max($qscore); if(in_array($iq,$AInfo['ErrataQ'])) $ScoreThisQ=10; 
            $Score += $ScoreThisQ; 
            // echo "<br>$k:($Score)"; 
          } else $Score +=0; 
       }  
       $G[$kk]['T'] +=1;
       if($nfiles>0) { $pS = round(100*$Score/$MaxScore);  $Table[$k][$key]['Name']=$pS; 
         if($pS>89.5) $G[$kk]['A'] +=1;  elseif($pS>79.5) $G[$kk]['B'] +=1; elseif($pS>69.5) $G[$kk]['C'] +=1; elseif($pS>59.5) $G[$kk]['D'] +=1; else $G[$kk]['F'] +=1;
       } else { $Table[$k][$key]['Name'] =''; $G[$kk]['N'] +=1; }
    }
  }
  $Table['Overall']['Info']['Name'] = "Overall(%)";  
  foreach($AList as $kk=>$vv) { $key=basename($vv);     $nT=$G[$kk]['T']; $nA=$G[$kk]['A']; $nB=$G[$kk]['B']; $nC=$G[$kk]['C']; $nD=$G[$kk]['D']; $nF=$G[$kk]['F']; $nN=$G[$kk]['N']; 
   $Table['Overall'][$key]['Name'] =sprintf("A=%.0f, B=%.0f, C=%.0f, D=%.0f, F=%.0f, N=%.0f", 100*$nA/$nT, 100*$nB/$nT, 100*$nC/$nT, 100*$nD/$nT, 100*$nF/$nT, 100*$nN/$nT); 
   $Table['Overall'][$key]['Detailed'] =$G[$kk]; 
  }
  file_put_contents($InfoFile, json_encode($Table)); 
}

$i=0; //Calculate overall score
foreach($Table as $k=>$v) { $j=0; $sum=0; $num=0; 
   if($i>0 && !($k=='Overall') ) foreach($v as $kk=>$vv) { $sum += $Table[$k][$kk]['Name'];   $num++; }
   if($i=='0')  $Overall = 'Overall';  else $Overall=round($sum/$num); 
   if(!isset($Table[$k]['Overall']) ) { $Table[$k]['Overall']['Name']=$Overall; }; $i++; 
}

$i=0; $uqid=uniqid(); $ni = sizeof($Table)-2; $arrowu=$arrow['U'];  $arrowd=$arrow['D']; $HideA=""; $PlotA=""; $Hkey=array(); 
foreach($Table as $k=>$v) {  $s = "";  $j=0; $nj = sizeof($v); 
  foreach($v as $kk=>$vv) {   $extB="";   $strij = "";  
      if($i=="0") { $Hkey[$j]='display'; 
         if($j=="0") { 
            $extB="<button class='$uqid-C' style='display:none;' onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID',  'flag':'Refresh'}); \">Refresh</button>"; 
            $strij .= sprintf("<span onclick=\"  \$('.SNAMESMONITOR').toggle(); \">%s</span>", $vv['Name']);
            $strij .= "<button  onclick=\"  \$('.$uqid-C').toggle(); var t = \$(this); if(t.text() == '-') t.text('+'); else t.text('-'); \">+</button>$extB"; 
         } else { 
            if($kk != 'Overall') { if($vv['View']=='hide') { $Hkey[$j]='hide'; $checked='checked'; } else $checked=''; 

               $nA=$Table['Overall'][$kk]['Detailed']['A']; $nB=$Table['Overall'][$kk]['Detailed']['B'];  $nC=$Table['Overall'][$kk]['Detailed']['C'];  
               $nD=$Table['Overall'][$kk]['Detailed']['D']; $nF=$Table['Overall'][$kk]['Detailed']['F']; $nN=$Table['Overall'][$kk]['Detailed']['N']; 

               $HideA .= sprintf(" | <input type=checkbox onclick=\" dimag({'outputid':'None','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID',  'flag':'toggle', 'AID':'$kk'});  \"  $checked>%s</input>", $vv['Name']); 
               if($vv['View'] !='hide') {
               $PlotA .= sprintf(" | <input name='Plot-Tags' type=radio onclick=\" plotVK2({'id':'container2','data':[{name:'A', y:$nA},{name:'B',y:$nB},{name:'C', y:$nC},{name:'D',y:$nD},{name:'F', y:$nF},{name:'N', y:$nN}]}, this);  \" >%s</input>", $vv['Name']); 
               }
            }
            $strij .= sprintf("<span id='Head-$j' onclick=\"   \">%s</span>", $vv['Name']); 
            $strij .= "<input id='PlotColn-$j' class='PlotColn' style='display:none;' type=checkbox onclick=\"PlotColn({'id':'$uqid','is':1,'ie':$ni,'js':0,'je':$nj, 'i':0,'j':$j}, this); \"></input>"; 

            $extB = "<button data-direction=1 data-text1='$arrowu' data-text2='$arrowd' onclick=\"SortVKJS({'id':'$uqid','is':1,'ie':$ni,'js':0,'je':$nj, 'i':0,'j':$j}, this);  \">$arrowd</button>"; 
           if($kk != 'Overall') $extB .= "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID',  'flag':'Refresh', 'AID':'$kk'}); \">Refresh</button>"; 
            $strij .= "<span class='$uqid-C' style='display:none;'>". togglePHP("$extB","$uqid-T-$i-$j",'+','str'). "</span>"; 
         }       
      } else {      
         if($j=='0')  { $strij .= sprintf("%s <span class=SNAMESMONITOR style='display:none'>%s</span>", $i, $vv['Name']); 
         } else  { // if($vv['Name']=='') $vv['Name']=rand(1,100); 
            $strij .= $vv['Name'];   
         }
      }
      if($Hkey[$j]=='display') $s .= "<td id='$uqid-$i-$j'>$strij</td>"; 
      $j++; 
  }
  $str .= "<tr>$s </tr>"; 
  $i++; 
}

$HideA .= sprintf(" | <button onclick=\" dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID'});  \" >Done</button>"); 

$HideStr = togglePHP("<br/>$HideA",uniqid(),'+','str'); 
$PlotStr = togglePHP("<br/>$PlotA",uniqid(),'+','str'); 

$ColPlotStr = "<input type=checkbox onclick=\" \$('.PlotColn').toggle(); \" />"; 
$ColPlotStr .= "(<input name=PlotType type=radio onclick=\" plottype='column'; \" />Column | <input name=PlotType type=radio onclick=\" plottype='line'; \" checked />Line"; 
$ColPlotStr .= " | <input name=PlotType type=radio onclick=\" plottype='scatter'; \" />Scatter)"; 

$str = "<span>Inactive Assessments: $HideStr | Pie Chart: $PlotStr | Column Plot: $ColPlotStr</span><table width=100% border=1>$str</table>"; 
echo $str; 

$Aid =  $PData['AID']; 
$O = array('TEMP'=>"$TEMP", 'Qid'=>$Qid, 'Aid'=>$Aid, 'Submit'=>1, 'disabled'=>0, 'LoadPHP'=>$LoadPHP, 'CourseID'=>$CourseID, 'outputid'=>$outputid, 'idtmp'=>$idtmp, 'ADir'=>$ADir); 
//pa($PData); 




?>
