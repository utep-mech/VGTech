<?php
include("$HOME/COMMON/common-old.php");  
$arrow = array('U'=>'&#8593;', 'D'=>'&#8595;', 'L'=>'&#8592;', 'R'=>'&#8594;'); 
$PData = $_POST['send']; $outputid = $PData['outputid']; $idtmp = 'inside-Monitor';  $CourseID= $PData['COURSE']; 
$LoadPHP = $PData['LoadPHP'];  $CDir = "$DATA/COURSES/$CourseID"; $AHome = "$CDir/ASSESSMENT"; 

if($PData['flag']=='ListFile') { Activity_ListFile($PData['File']);  return; }
if($PData['flag']=='LoadPlot') { LoadPlot($DATA, $PData['uid']);  return; }

echo "<style> .green { background-color: #00cc00; }; </style>"; 
$Roster = json_decode(file_get_contents("$CDir/Roster.json"), true); 

?>


<script> 
var plottype='line'; var highchartloaded=0; // Always load the scripts (temporary fix)
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

//-----------
function PlotTime(In, e) {   //var blob = new Blob([csv],{type: 'text/csv;charset=utf-8;'}); navigator.msSaveBlob(blob, "filename.csv"); 
    // if(In.datatype=='table') var data = {table: In.dataid, startRow:2, startColumn: In.sc, endColumn: In.ec}; else var data = {csv: document.getElementById(In.dataid).innerHTML, startRow:2, endColumn: 1};
     var data = {csv: Table2csv({id:In.dataid}), startColumn: 0}; 
    // alert(JSON.stringify(data));
    $('#'+In.pid).highcharts({credits: {enabled: false}, exporting: { enabled: false }, chart: {zoomType: 'x'}, xAxis: {type: 'datetime' }, data:data,  series: [{type: 'line'}]});
}

function Table2json(In) { var json = '{', otArr = [];
   var tbl2 = $('#'+In.id+' tr').each(function(i) { x = $(this).children();  var itArr = [];
      x.each(function() { itArr.push('"' + $(this).text() + '"'); });
      otArr.push('"' + i + '": [' + itArr.join(',') + ']');
   }); 
   json += otArr.join(",") + '}'; 
   return json;
}

function Table2csv(In) { var csv='', csvr='', tbl2 = $('#'+In.id+' tr');   
   tbl2.each(function(i) { var j=0, x = $(this).children(); csvr=''; 
      
         x.each(function() { 
          if( $('#'+In.id+'-'+j).is(':checked') ) {  if(csvr=='') csvr =$(this).text();  else csvr += ','+$(this).text();  }
           j++;  
        });
      if(csv=='') csv = csvr + '\n'; else  csv += csvr + '\n';
   }); 
   return csv;
}
</script>
 
<?php

$s = ''; 
foreach($Roster as $u=>$v) { $stmp=''; $name = $v['LastName']; $tmpid="$outputid-$u"; 
  foreach(glob("$DATA/RecordActivity/$u/*.json") as $k=>$w) { $n=basename($w,'.json'); 
   $stmp .= "<button onclick=\"dimag({'outputid':'$tmpid','LoadPHP':'COURSES/Activity.php', 'COURSE':'$CourseID', 'flag':'ListFile','File':'$w'}); \">$n</button>"; 
 }
  $PlotB = "<button onclick=\" dimag({'outputid':'$tmpid','LoadPHP':'COURSES/Activity.php', 'COURSE':'$CourseID', 'flag':'LoadPlot','uid':'$u'}); \">Load</button> | "; 
  $s .= "<tr><td>$name</td><td>$PlotB $stmp<div id='$tmpid'></div>  </td></tr>"; 
}
echo "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'COURSES/Activity.php', 'COURSE':'$CourseID'}); \">Activity</button> | "; 
echo "<table border=1 width=100%>$s</table>"; 

return; 

//--------------------------------
function Activity_ListFile($f) {   $sA = json_decode(file_get_contents($f),true); $s = ''; $uqid = uniqid(); 
  foreach($sA as $k=>$v) {$actionS=json_encode($v); $t = date('h:i:s a', $k); $s .= "<span onclick=\"  \$('#$uqid-$k').toggle();  \$(this).toggleClass('green'); \">$t</span> | <div  style='display:none' id='$uqid-$k'>$actionS</div>"; }
  
 echo $s; 
}
//--------------------------------
function LoadPlot($DATA, $u) {  $dttol=10*60; $files=glob("$DATA/RecordActivity/$u/*.json") ; $n=0; $uqid=uniqid(); 
  $s=sprintf("Time, Number of clicks");   $st = ""; $checkedA=array(1,2); 
  $th = array('Time', 'epoch Time', 'Number of clicks', 'total time(min)', 'dt(min)'); 
  foreach($th as $kk=>$vv) {if(in_array($kk,$checkedA)) $checked='checked'; else $checked='';  $st .="<th>$vv <input id='table-$uqid-$u-$kk' type='checkbox' $checked /> </th>"; }
  $st ="<tr>$st</tr>";  
  foreach($files as $k=>$f) { $dt=0; $t1=0; $t2=0;  foreach (json_decode(file_get_contents($f),true) as $t=>$v) {  $n++; if($t1==0) $t1=$t; 
      //$s .= sprintf("\n%s,%s",1000*$t,$n); 
      $t1=$t2; $t2=$t; $dtL=$t2-$t1; if($dtL > $dttol) $dtL=0; $dt += $dtL; 
      $st .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",date('Y-m-d:H:i',$t), 1000*$t, $n, round($dt/60,2), round($dtL/60,2) );  
   }   }
   
   $PlotPlace = "<div class='class-$uqid-$u' style='display:none' id='PlotPlace$u' style='min-width: 310px; height: 400px; margin: 0 auto'></div>"; 
   echo "$PlotPlace<button class='class-$uqid-$u'  style='display:none' onclick=\"PlotTime({dataid:'table-$uqid-$u', pid:'PlotPlace$u', sc:1, ec:2, datatype:'table'},this);  \">Plot</button> ";
   echo "<button onclick=\" \$('.class-$uqid-$u').toggle(); \$(this).toggleClass('green');  \$(this).text($(this).text() == 'Show' ? 'Hide' : 'Show'); \">Show</button> ";
   echo "<table class='class-$uqid-$u' id='table-$uqid-$u' style='display:none' border=1>$st</table>"; 

  //echo "<pre id='csv-$uqid-$u' style='display:none'>$s</pre>"; 
  //echo "<button onclick=\"PlotTime({dataid:'csv-$uqid-$u', pid:'PlotPlace$u', sc:0, ec:1, datatype:'csv'},this);  \">Plot</button> ";
  //echo "<button onclick=\" \$('#csv-$uqid-$u').toggle(); \$(this).toggleClass('green');  \$(this).text($(this).text() == 'Show' ? 'Hide' : 'Show'); \">Show</button> ";

 return;  

}


//-------------------

$InfoFile="$AHome/OverAllInfo.json";  if(file_exists($InfoFile)) $Table = json_decode(file_get_contents($InfoFile), true); 
if($PData['flag']=='toggle') { $AID=$PData['AID']; 
  if($Table['Students'][$AID]['View']=='hide') $Table['Students'][$AID]['View']='display'; else $Table['Students'][$AID]['View']='hide'; 
  file_put_contents($InfoFile, json_encode($Table));
}


if(!file_exists($InfoFile) || $PData['flag']=='Refresh') { 
 if(isset($PData['AID'])) $AList[0]="$AHome/".$PData['AID']; else $AList=glob("$AHome/A_?????????????"); 

$Table['Students']['Info']['Name'] = "Students";  
foreach($AList as $kk=>$vv) { $key=basename($vv);   $AInfo=json_decode( file_get_contents("$vv/AInfo.json"), true); $Table['Students'][$key]['Name'] =$AInfo['Name']; 
   $G[$kk]=array('A'=>0,'B'=>0,'C'=>0,'D'=>0,'F'=>0, 'N'=>0, 'T'=>0);  
}
  foreach($Roster as $k=>$v) {  $Table[$k]['Info']['Name'] =$k; 
    foreach($AList as $kk=>$vv) { $key=basename($vv);  $MaxScore=1e-6; $Score=0; $nfiles=0; unset($qscore); 
       $AInfo=json_decode( file_get_contents("$vv/AInfo.json"), true); 
       foreach($AInfo['Questions'] as $itmp=>$iq) {  $qfile = "$CDir/STUDENTS/$k/$key/$iq.xml";  $MaxScore += 10; 
           if(file_exists($qfile)) { $nfiles++; FileIORead($qfile, $Q, $root, 'ReadQ');    for($it=0; $it<sizeof($Q['Q']); $it++) $qscore[$it]=$Q['Q'][$it]['@attributes']['Score']; 
            $Score += max($qscore); 
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
