<?php
//----------------REMOVE THIS ONCE WORKING-------------------------
namespace Q { //Questions.............
  class Q {
        public $outputid='none', $editor='Default', $tmp='/tmp';
        public function __construct($f, $O=array()) { 
          foreach($O as $k=>$v) {$this->$k = $v; }   

	      if(!file_exists($f)) { $file = "$f.html"; 
   	        if(file_exists("$f.json")) $file = "$f.json"; 
   	        if(file_exists("$f.xml")) $file = "$f.xml"; 
	        $f = $file; 
	      } 
          if(!file_exists($f)) { if(!($this->admin)) $f="Error!"; }

          $this->f=$f; $this->id = uniqid(); $this->tmpdir = '/tmp'; 
          $this->fn = basename($f); $this->fd = dirname($f); 
          if(is_dir($f)) die("'$f' is a directory"); 
          $this->fext=strtolower(pathinfo($f, PATHINFO_EXTENSION));  
          if($this->editor=='Default') {if($this->fext=='html') $this->editor='ckeditor'; }
        }

//----------------------------------
  /*
  function LoadQFile($O=array()) {  $s = ''; 
   $f=$this->f; $id=$this->id; $LoadPHP=$this->LoadPHP; $Layout=$this->Layout; 
   if(!file_exists($f) ) {
     $s .= "<br />$f does not exits!<br/> "; 
     $s .= "<button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP','Copy':'$LayoutDir/Q.xml', 'LoadQFile':'$f'});\">Copy Default</button>";
  } else {
     $s .= $this->Read_Q2(); 
  }
  return $s; 

}
 */
//----------------------------------
       function Q2html() {  
	$f=$this->f; $id=$this->id; $LoadPHP=$this->LoadPHP; $s = ''; 

$O = array('TEMP'=>"/tmp", 'Qid'=>$this->outputid, 'Aid'=>$Aid, 'Submit'=>1, 'disabled'=>0, 'LoadPHP'=>$LoadPHP, 'CourseID'=>$CourseID, 'outputid'=>$outputid, 'idtmp'=>$idtmp); 
$O['Attempt']=0; 

 	if(file_exists($f)) { 
    	  FileIORead($f, $A, $root, "Read");
    	  $desc = $A["Q"]["Description"]["@value"]; 
    	  $s = " <textarea id='$id'>$desc</textarea>"; 
    	  $s .= "<br/><input type=button onclick=\"
	    	var val = \$('#$id').val(); 
		dimag2({'outputid':'msg-$id', 'LoadPHP':'$LoadPHP', 'val': val, 'QSave':'$f'}); 
	  \" value=Submit /><span id='msg-$id'></span>"; 
 	} else $s = $f; 
	//return $s . $this->Read_Q($f); 
	return $this->Read_Q('/tmp', $O); 
	//return $this->Question_EditQ($O); 
       }
//----------------------------------------------------------
function Read_Q2($O = array()) { 
    if($this->fext == 'html') return $this->Read_Q2html(); // $this->html2html();
   if($this->fext == 'xml') return $this->xml2html(); 
   if($this->fext == 'json') return $this->json2html(); 
}
//----------------------------------------
function json2html() { $ic=0;
    if(isset($_POST['send']['id'])) $this->id = $_POST['send']['id']; 
    $id = $this->id; $file = $this->f;  $TEMP = $this->tmp; $qid = pathinfo($file, PATHINFO_FILENAME); 
    $LoadPHP = $this->LoadPHP; $CourseID= $this->CourseID; $outputid= $this->outputid;  

  $Q = json_decode(file_get_contents($this->f),true);  // GET DATA

  $s = \IO\CameraPlaceHolder($id,2*320,2*240); //$s = \IO\CameraPlaceHolder($id,320,240); 
    
  if(isset($_POST['send']['data']["$ic$id"]))  $Q['Desc']['Val'] = $_POST['send']['data']["$ic$id"]; 
  $s .="<span class=$id name=Submit id=$ic$id>". $Q['Desc']['Val'] . "</span>"; 
  if(isset($Q['Desc']['Attr']['Camera'])?$Q['Desc']['Attr']['Camera']:0) 
	  $s .= \IO\CameraOpen($id,$ic) .  \IO\CameraCapture($id,$ic,"$ic$id"); 

  foreach($Q['Q'] as $k=>&$v) { $ic = $ic+1; $kp1=$k+1;
      if(isset($_POST['send']['data']["$ic$id"]))  $v['Desc']['Val'] = $_POST['send']['data']["$ic$id"]; 

      $desc = "<span class=$id name=Submit id=$ic$id>" . $v['Desc']['Val'] . "</span>"; 
      $s .= " <span class=$id><hr/>(Q$kp1)<br/> $desc</span>";  
      if(isset($v['Desc']['Attr']['Camera'])?$v['Desc']['Attr']['Camera']:0) 
	  $s .= \IO\CameraOpen($id,$ic) .  \IO\CameraCapture($id,$ic,"$ic$id"); 
  }

  $Submit = "<button onclick=\"
    var i=0, vo={};  
   \$('.$id'+'[name=Submit]').each(function(){ i = i+1; var id = \$(this).attr('id');  vo[id]= \$(this).html(); });
    dimag2({LoadQFile:'$file', LoadPHP:'$LoadPHP',outputid:'$outputid',data:vo, id:'$id', Submit:1}); 
   \">Submit</button>
  ";

   if(isset($_POST['send']['Submit'])) { file_put_contents($this->f, json_encode($Q)); } //WRITE DATA

  return "$s <br/>$Submit <hr/>"; 
}
//======================

function xml2html($O = array()) { 
    $file = $this->f;  $TEMP = $this->tmp; $qid = pathinfo($file, PATHINFO_FILENAME); 
    $LoadPHP = $this->LoadPHP; $CourseID= $this->CourseID; $outputid= $this->outputid;  
    $smath = "<script> MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']); </script>";
    $Time = time(); $class = "C$qid"; $submitted=0;  
    FileIORead("$file", $QQ, $root, 'ReadQ');   $strAll='';

    if(isset($_POST['send']['ValClass'])) { $PValue = $_POST['send']['ValClass']; 
     for($iq=0; $iq<sizeof($QQ['Q']); $iq++) {
      $Q=$QQ['Q'][$iq]; $Q['AO'] = $Q['A']; $Type = $Q['@attributes']['Type'];    
      if ($Type == 1) { $Q['A'][0]['@value'] = $PValue["FillIn-$iq-$class"];  
      } else {
        foreach ($Q['A'] as $j => $w) { $val = $w['@value'];
	  $Q['A'][$j]['@attributes']['status'] = $PValue["MC-$j-$iq-$class"];  
        }
      }
      $QQ['Q'][$iq] = $Q; 
     }
      FileIO("$file", $QQ, $root, 'Write');   
      echo "Answer(s) saved  " . date('Y-m-d H:m:s'); 
      return; 
    }
  for($iq=0; $iq<sizeof($QQ['Q']); $iq++) {
    $Q=$QQ['Q'][$iq]; 

    if ($O['PHP_Q'] == 1)  { $Q = PHP_Q($Q, $TEMP); } //include the PHP code
    $str  = ''; $desc = $Q['Description']['@value']; $Type = $Q['@attributes']['Type'];    

    if(isset($Q['AO'])) { $disabled='disabled'; $submitted=1; }

    if ($Type == 1) { if($submitted) $value = $Q['A'][0]['@value']; else $value=''; 
      $subStr= "<br/><input class='$class' type=text id='FillIn-$iq-$class' value='$value' $disabled></input>";
    } else {
      $strA = ""; 
      foreach ($Q['A'] as $j => $w) { $val = $w['@value'];
	    if($w['@attributes']['status'] && $submitted) $checked='checked'; else $checked=''; 
	    if($Q['AO'][$j]['@attributes']['status'] && $submitted) $color='green'; else $color='';
	    
        $strA .= "<tr><td><span style='background-color:$color;'><input type=checkbox class='$class' id='MC-$j-$iq-$class' value=$j $checked $disabled></input></span></td><td>$val</td></tr>";
      }
      $str .= "<table border=1>$strA</table>";  
      $subStr = "<p/><u><b>Choices</b></u>$str"; 
    }
    $strAll .= "$desc $subStr <p/>"; 
  }
    $strS = "<br/><input id='Button-$class' type=button value=Submit onclick=\"
        var ValClass={}; $('#Button-$class').prop('disabled', true);
	$('.$class').each(function(){var id = $(this).prop('id');  
           if( $(this).prop('type') == 'checkbox') {if( $(this).prop('checked')) $(this).val(1); else $(this).val(0); } //alert(id);
            ValClass[id] = $(this).prop('value');  
	}); 

	    //alert(JSON.stringify(ValClass));
	    dimag2({LoadQFile:'$file', Type:'$Type', LoadPHP:'$LoadPHP',outputid:'msg-$qid',ValClass:ValClass, Submit:1}); 
    \" $disabled></input><span id='msg-$qid'></span>$solved";  

    return "$smath <div style='border:1px solid blue;'>$strAll $strS</div>";

}
//----------------------------------------------------------
function Read_Q2html2($O = array()) { $uwDir=$this->uwDir; $fn=basename($this->f);  
require_once('simple_html_dom.php');
    $file = $this->f;  $TEMP = $this->tmp; $qid = pathinfo($file, PATHINFO_FILENAME); 
    $LoadPHP = $this->LoadPHP; $CourseID= $this->CourseID; $outputid= $this->outputid;  
    $smath = "<script> MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']); </script>";
    $class = "Chtml$qid"; 
    $s = 'hh';

$html = file_get_html($file);
echo "<textarea>$html</textarea>";
foreach($html->find('div') as $e) {
       echo $e->id. '<br>';
}
/*
$xml=simplexml_load_string($str);
print_r($xml);


$html = file_get_html('http://www.google.com/');
   $str = '<html></html>';
$dom = new DOMDocument('1.0');
$doc = new DOMDocument();
   $DOM = new DOMDocument();
$html = str_get_html('<html><body>Hello!</body></html>');
   $DOM->loadHTML($str);
  $items = $DOM->getElementsByTagName('h1');

   for ($i = 0; $i < $items->length; $i++)
        $s .= $items->item($i)->nodeValue . "<br/>";
*/

    return $s; 
}

//----------------------------------------------------------
function html2html($O = []) { // NOT USED 
    $uwDir=$this->uwDir; $fn=basename($this->f);  $QDir=$this->QDir; $iSolution=0;
if(isset($_POST['send']['Solution'])) { $this->f = $_POST['send']['Solution']; $iSolution=1;}
$file = $this->f;  $TEMP = $this->tmp; $qid = pathinfo($file, PATHINFO_FILENAME); $SolnFile="$QDir/Solutions/$fn";
$LoadPHP = $this->LoadPHP; $CourseID= $this->CourseID; $outputid= $this->outputid;
//$smath = "<script> MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']); </script>";
$class = "Chtml$qid"; if($iSolution) $class = "SolnChtml$qid";
$smath = "<script> MathJax.Hub.Queue(['Typeset',MathJax.Hub,'Contents$class']); </script>";
if(isset($_POST['send']['ResetFrom'])) { $mfile = $_POST['send']['ResetFrom']."/$fn";
  if(file_exists($mfile)) {copy($mfile,$file); return 'Reset completed, reload again.';  }
}
if(isset($_POST['send']['Submit'])) {
    if($_POST['send']['InsSoln']) { $file =  "$QDir/Solutions/$fn";
      if(!is_dir("$QDir/Solutions"))  mkdir("$QDir/Solutions",0777);
      copy("$QDir/$fn",$file);
    }
    file_put_contents($file,$_POST['send']['Val']);     //file_put_contents($file,$_POST['send']['Val'], FILE_APPEND | LOCK_EX);
    return " Saved at " . date('Y-m-d H:i:s');
}

$strS = "<br/><input id='Button-$class' type=button value=Submit onclick=\"
      var i=0, h='';
      var InsSoln = $('#InstructorSolution$class').prop('checked')  ? 1 : 0;
      $('#Contents$class :input').each(function(){ i = i+1; var val, id = '$qid'+'inputs'+i;
        var tpe = $(this).attr('type'); 
        if(tpe=='checkbox') { $(this).attr('checked', $(this).is(':checked') ); 
        } else { 
           if ( $(this).is('textarea')) $(this).html($(this).val() );  else $(this).attr('value', $(this).val() ); 
        } 
      });
     $('#Button-$class').prop('disabled', true);  $('#Contents$class :input').prop('disabled', true);
      dimag2({LoadQFile:'$file', LoadPHP:'$LoadPHP',outputid:'msg-$class',Val: $('#Contents$class').html(), Submit:1, InsSoln:InsSoln});
    \" $disabled></input></span>";

if(($this->admin)) {
    $mainDir=$this->QDir;
    $strS .= "(<input type=checkbox id='InstructorSolution$class' dir='$mainDir' onclick=\" var i=0;
          $('#Contents$class :input').each(function(){ i = i+1;
            $(this).prop('disabled', false); $('#Button-$class').prop('disabled', false);
          });
      \" /> SaveSolution)";
    $strS .= "<input type=button value=Reset onclick=\"dimag({'outputid':'msg-$class', 'LoadPHP':'$LoadPHP', 'LoadQFile':'$file', 'ResetFrom':'$mainDir'});\"></input>";
    $strS .= "<input type=button value=SeeSolution onclick=\"dimag({'outputid':'msg-$class', 'LoadPHP':'Questions.php', 'LoadQFile':'$SolnFile','Solution':'$SolnFile'});\"></input>";
}
$strC = "<span id='Contents$class'>".file_get_contents($this->f)."</span>";
// $strS .= $this->OverlaySolution($class,$mainDir);
// if(($this->admin) && file_exists($SolnFile)) $Soln = "<span id='InsContents$class' style='display:none;'>".file_get_contents($SolnFile)."</span>";

$msg = "<span id='msg-$qid'><span id='msg-$class'>";
if($iSolution) return " <div style='border:1px solid red;'><u>Instructor Solution</u><br/>$strC $sc</div> $smath";

return " <div style='border:1px solid blue;'>$strC $Soln $strS $msg </div> $smath";
}

//----------------------------------------------------------
function Read_Q2html($O = array()) { 
    $uwDir=$this->uwDir; $fn=$this->fn; $fd=$this->fd;  $QDir=$this->QDir; $iSolution=0;   $iSolS=0;   
    if(isset($_POST['send']['Solution'])) { $iSolution=1;}
    $file = $this->f;  $TEMP = $this->tmp; $qid = pathinfo($file, PATHINFO_FILENAME); $SolnFile = "$fd/Soln-$qid.json";
    $LoadPHP = $this->LoadPHP; $CourseID= $this->CourseID; $outputid= $this->outputid;  
    //$smath = "<script> MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']); </script>";
    $class = "Chtml$qid"; if($iSolution) $class = "SolnChtml$qid";
    $smath = "<script> MathJax.Hub.Queue(['Typeset',MathJax.Hub,'Contents$class']); </script>";
    if(isset($_POST['send']['Grade'])) {echo $this->Grade("$QDir/Soln-$qid.json","$fd/Soln-$qid.json"); return;}
    if(isset($_POST['send']['ResetFrom'])) { 
        $mfile = $_POST['send']['ResetFrom']."/$fn";  
        if(file_exists($mfile)) {copy($mfile,$file); return 'Reset completed, reload again.';  }
    } 
    if(isset($_POST['send']['Submit'])) { if($_POST['send']['InsSoln']) $SolnFile = "$QDir/Soln-$qid.json";
        file_put_contents($SolnFile, json_encode($_POST['send']['Val']));  
        return " Saved at " . date('Y-m-d H:i:s'); 
    }
    if(file_exists($SolnFile)) { $Soln = file_get_contents($SolnFile); $iSolS=1; } else {$Soln="[]"; $iSolnS=0; }
    $strS = "<br/><input id='Button-$class' type=button value=Submit onclick=\"
      var i=0, h='', v={Soln:[]}; 
     if( $('#InstructorSolution$class').prop('checked') ) var InsSoln = 1; else var InsSoln = 0;
      $('#Contents$class :input').each(function(){ i = i+1; var val, id = '$qid'+'inputs'+i; 
        var tpe = $(this).is('textarea') ? 'textarea' : tpe = $(this).attr('type'); 
        if(tpe=='checkbox') {val = $(this).is(':checked'); } else { val = $(this).val();  }
        v['Soln'].push({'type':tpe, 'value':val}); 
      }); 
     $('#Button-$class').prop('disabled', true);  $('#Contents$class :input').prop('disabled', true); 
      dimag2({LoadQFile:'$file', LoadPHP:'$LoadPHP',outputid:'msg-$class',Val:v, Submit:1, InsSoln:InsSoln}); 
    \" $disabled></input></span>";  

   $sc = "<script> var i=0, isol = $iSolS; var sol = $Soln; 
    $('#Contents$class :input').each(function(){ i = i+1; var id = '$qid'+'inputs'+i; 
      if( isol )  { var val = sol['Soln'][i-1]['value']; //$('#Contents$class .'+id).html(); 
        var tpe = $(this).is('textarea') ? 'textarea' : tpe = $(this).attr('type'); 
        if(tpe=='checkbox') { $(this).prop('checked',val=='true');} else $(this).val( val ); 
        $('#Contents$class .'+id).hide(); $('#Button-$class').prop('disabled', true); $(this).prop('disabled', true); 
      } 
    });
    if( $('table #metadata').length) $('table #metadata').hide();
    </script>"; 
    if(($this->admin)) {
	  $mainDir=$this->QDir; 
	  $strS .= "(<input type=checkbox id='InstructorSolution$class' dir='$mainDir' onclick=\" var i=0;
          $('#Contents$class :input').each(function(){ i = i+1;
            var flag = $('#InstructorSolution$class').is(':checked')? false:true; 
            $(this).prop('disabled', flag); $('#Button-$class').prop('disabled', flag);
          }); 
      \" /> SaveSolution)";
	  $strS .= "<input type=button value=Reset onclick=\"dimag({'outputid':'msg-$class', 'LoadPHP':'$LoadPHP', 'LoadQFile':'$file', 'ResetFrom':'$mainDir'});\"></input>";
	  $strS .= "<input type=button value=SeeSolution onclick=\"dimag({'outputid':'msg-$class', 'LoadPHP':'Questions.php', 'LoadQFile':'$QDir/$fn','Solution':'$SolnFile'});\"></input>";
	  $strS .= "<button onclick=\"dimag({'outputid':'msg-$class', 'LoadPHP':'Questions.php', 'LoadQFile':'$file', 'Grade':'$QDir/Soln-$qid.json'});\">Grade</button>";
    }
    $strC = "<span id='Contents$class'>".file_get_contents($this->f)."</span>"; 
    // $strS .= $this->OverlaySolution($class,$mainDir);
    // if(($this->admin) && file_exists($SolnFile)) $Soln = "<span id='InsContents$class' style='display:none;'>".file_get_contents($SolnFile)."</span>";
        
    $msg = "<span id='msg-$qid'><span id='msg-$class'>"; 
    if($iSolution) return " <div style='border:1px solid red;'><u>Instructor Solution</u><br/>$strC $sc</div> $smath";
    
    return " <div style='border:1px solid blue;'>$strC $strS $msg $sc</div> $smath";
}
//----------------------------------------------------------
function Grade($f1,$f2, $O = []) { $eps = \IO\eps(); $error=0.03;  // 3% error margine allowed
    //$qid=pathinfo($f1, PATHINFO_FILENAME); $sf1 = dirname($f1)."/Soln-$qid.json"; $sf2 = dirname($f2)."/Soln-$qid.json";
    $sf1=$f1; $sf2=$f2; 
    $uqid = uniqid(); 
    if(!file_exists($sf1) || !file_exists($sf2) ) { return "File doesn't exists! Q->Grade"; }
    $sSoln = json_decode(file_get_contents($sf2),true);     $Soln = json_decode(file_get_contents($sf1),true);
    $st = "<tr><th>Instructor's Answer</th><th>Max Points</th><th>Student's Answer</th><th>Student's Points</th></tr>";
    $OverallPoints = 0; $sOverallPoints = 0; $isSPoints=isset($sSoln['Points']); 
    foreach($Soln['Soln'] as $k=>$v) { 
        $v1 = $Soln['Soln'][$k]['value']; $v2 = $sSoln['Soln'][$k]['value']; $type=$Soln['Soln'][$k]['type']; 
        $Points=0; $sPoints=0;
        if (isset($Soln['Points'])) $Points = $Soln['Points'][$k]; else $Points=10;
        if($type=='checkbox' && $v1==$v2) $sPoints=$Points;
        if (!$isSPoints) $sSoln['Points'][$k]=$sPoints; else $sPoints = $sSoln['Points'][$k];
        if($type=='text' && is_numeric($v1) && is_numeric($v2) ) if(abs($v1-$v2)/(abs($v1)+$eps) < $error) $sPoints=$Points;
        $Inp = "<input class=Inp$uqid id=Inp$uqid-$k size=2 value=$Points />"; 
        $sInp = "<input class=sInp$uqid id=sInp$uqid-$k size=2 value=$sPoints />"; 
        $arrowb = "<button onclick=\" $('#sInp$uqid-$k').val( $('#Inp$uqid-$k').val() );  \">". \IO\B('ra')."</button>"; 
        $st .= "<tr><td>$v1</td> <td>$Inp $arrowb</td> <td>$v2</td> <td>$sInp</td> </tr>"; 
        $OverallPoints += $Points; $sOverallPoints += $sPoints;
    }
    if (!$isSPoints) {$sSoln['OverallPoints'] =$sOverallPoints; file_put_contents($sf2,json_encode($sSoln)); }
    $OS = !($Soln['OverallPoints']==$OverallPoints)? $Soln['OverallPoints'] : ''; 
    $sOS = !($sSoln['OverallPoints']==$sOverallPoints)? $sSoln['OverallPoints'] : '';
    
    $InpO = "<input id=Inp$uqid-all size=2 value=$OverallPoints /> $OS";
    $sInpO = "<input id=sInp$uqid-all size=2 value=$sOverallPoints />$sOS";
    $arrowO = "<button onclick=\" $('#sInp$uqid-all').val( $('#Inp$uqid-all').val() );  \">". \IO\B('ra')."</button>";
    
    $b= "<button id=msg-$uqid onclick=\" var v={file:'$sf1',Keys:{Points:[]}};
     $('.Inp$uqid').each(function(){v['Keys']['Points'].push( $(this).val() ); }); 
     v['Keys']['OverallPoints'] = $('#Inp$uqid-all').val(); 
     dimag({'outputid':'msg-$uqid', 'UpdateJSON':v});
    \">Save</button>";
    
    $sb= "<button id=msg2-$uqid onclick=\" var v={file:'$sf2',Keys:{Points:[]}};
     $('.sInp$uqid').each(function(){v['Keys']['Points'].push( $(this).val() ); });
     v['Keys']['OverallPoints'] = $('#sInp$uqid-all').val(); 
     dimag({'outputid':'msg2-$uqid', 'UpdateJSON':v});
    \">Save</button>";
    
    $st .= "<tr><th>Overall</th><th>$InpO $arrowO $b</th><th> </th><th>$sInpO $sb</th></tr>";
    
    return "<table border=1 width=100%>$st</table>";
 }
//----------------------------------------------------------
function OverlaySolution($class,$mainDir, $O = []) { // NOT USED
    return "(<input type=checkbox id='InstructorSolution$class' dir='$mainDir' onclick=\" var i=0;
           $('#Contents$class :input').each(function(){ i = i+1;
           if ( $('#InstructorSolution$class').prop('checked') ) {
            var id = '#InsContents$class .'+'$class'+'inputs'+i; $(this).prop('disabled', false); $('#Button-$class').prop('disabled', false);
           } else {
            var id = '#Contents$class .'+'$class'+'inputs'+i;
            if( ! $(id).length )  {
              $(this).prop('disabled', false); $('#Button-$class').prop('disabled', false);
              if( $(this).attr('type') =='checkbox') { $(this).prop('checked',false);} else $(this).val('');
            } else { $(this).prop('disabled', true); $('#Button-$class').prop('disabled', true); }
           }
            if( $(id).length )  { var val = $(id).html(), tpe = $(this).attr('type');
              if(tpe=='checkbox') { $(this).prop('checked',val=='true');} else $(this).val( val );
            }
           });
         \" /> Solution) ";
}
//----------------------------------------------------------
function Read_Q2old($O = array()) { 
    $file = $this->f;  $TEMP = $this->tmp; $qid = pathinfo($file, PATHINFO_FILENAME); 
    $LoadPHP = $this->LoadPHP; $CourseID= $this->CourseID; $outputid= $this->outputid;  
    $smath = "<script> MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']); </script>";

    FileIORead("$file", $QQ, $root, 'ReadQ');   
    $Q=$QQ['Q'][0]; 
    if ($O['PHP_Q'] == 1)  { $Q = PHP_Q($Q, $TEMP); } //include the PHP code
    $str  = ''; $desc = $Q['Description']['@value']; $Type = $Q['@attributes']['Type'];    $Time = time();

    $class = "C$qid"; $submitted=0;  

    if(isset($Q['AO'])) { $disabled='disabled'; $submitted=1; }

    if(isset($_POST['send']['ValClass'])) { 
      $Q['AO'] = $Q['A']; 
      $PValue = $_POST['send']['ValClass']; 
      if ($Type == 1) { 
	$Q['A'][0]['@value'] = $PValue["FillIn-$class"];  
      } else {
        foreach ($Q['A'] as $j => $w) { $val = $w['@value'];
	  $Q['A'][$j]['@attributes']['status'] = $PValue["MC-$j-$class"];  
        }
      }
      $QQ['Q'][0] = $Q; FileIO("$file", $QQ, $root, 'Write');   
      echo "Answer(s) saved  " . date('Y-m-d H:m:s'); 
      return; 
    }

    if ($Type == 1) { if($submitted) $value = $Q['A'][0]['@value']; else $value=''; 
      $subStr= "<p/><input class='$class' type=text id='FillIn-$class' value='$value' $disabled></input>";
    } else {
      $strA = ""; 
      foreach ($Q['A'] as $j => $w) { $val = $w['@value'];
	if($w['@attributes']['status'] && $submitted) $checked='checked'; else $checked=''; 
        $strA .= "<tr><td><input type=checkbox class='$class' id='MC-$j-$class' value=$j $checked $disabled></input></td><td>$val</td></tr>";
      }
      $str .= "<table border=1>$strA</table>";  
      $subStr = "<p/><u><b>Choices</b></u>$str"; 
    }

    $strS = "<br/><input id='Button-$class' type=button value=Submit onclick=\"
        var ValClass={}; 
	$('.$class').each(function(){var id = $(this).prop('id');  
           if( $(this).prop('type') == 'checkbox') {if( $(this).prop('checked')) $(this).val(1); else $(this).val(0); } //alert(id);
            ValClass[id] = $(this).prop('value');  
	}); 
        $('#Button-$class').prop('disabled', true);
	    //alert(JSON.stringify(ValClass));
	dimag2({LoadQFile:'$file', Type:'$Type', LoadPHP:'$LoadPHP',outputid:'msg-$qid',ValClass:ValClass, Submit:1}); 
    \" $disabled></input><span id='msg-$qid'></span>$solved";  

    return "$smath <div style='border:1px solid blue;'>$desc $subStr $strS</div>";

}
//-------------------REMOVE AFTER TESTED--------------------
function Read_Q($TEMP='/tmp', $O = "", $AInfo="") { 
    $file = $this->f;  
    $Attempt = 0; 
    $status=$AInfo['status'];  //echo "$D/$file"; pa($O); 
    FileIORead("$file", $QQ, $root, 'ReadQ');   
    $nAttempt = sizeof($QQ['Q']);  if($nAttempt>1 && !isset($_POST['send']['Attempt']) ) $Attempt=$nAttempt - 1;  
    $SeenSolution=0; $TryAgain=1; 
    $smath = "<script> MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']); </script>";


    if(isset($QQ['Q'][0]['@attributes']['SeenSolution'])) { $O['disabled']=1; $SeenSolution=1; $TryAgain=0;  $solcolor='#0099ee';}
    $Q=$QQ['Q'][$Attempt]; 
    if ($O['PHP_Q'] == 1)  { $Q = PHP_Q($Q, $TEMP); }
    if(isset($Q['AO'])) {$O['checked']=1; $O['disabled']=1;  }
    $str  = ''; 
    $desc = $Q['Description']['@value'];
    $Type = $Q['@attributes']['Type'];    
    

    $Time = time();
    $LoadPHP = $O['LoadPHP']; $CourseID= $O['CourseID']; $outputid= $O['outputid']; $idtmp= $O['idtmp'];  $Qid = $O['Qid']; $Aid = $O['Aid']; 
    if ($O['disabled'])         $disabled = 'disabled';
    if ($O['Submit']){ //$stime = "<script>Fun_TrackTime('sTime');</script>"; 
       $strS = "<br/><input class='MC-$Qid' type=button value=Submit onclick=\"QSubmit({id:'$Qid',Aid:'$Aid', Type:'$Type', LoadPHP:'$LoadPHP',CourseID:'$CourseID',outputid:'$outputid', 'Attempt':'$Attempt'})\" $disabled></input>$solved";  
     }
       if($nAttempt>0) { $stmp = ""; 
         for($iattempt = 0; $iattempt <$nAttempt; $iattempt++)  { $atr=$QQ['Q'][$iattempt]['@attributes'];  $color = "";  $hcolor = ""; 
           if($Attempt == $iattempt) {$hcolor  = 'yellow'; $sattempt = sprintf("<b>%s</b>", $iattempt +1); } else            $sattempt = (1+$iattempt); 
           if(isset($atr['Score'])) { if($atr['Score']) {$color='#00ff00'; $TryAgain=0; } else $color='#DDA0DD'; } 
           if($status==2) $color=""; 
           $stmp .= "<button style='background-color:$color' onclick=\"Questions_Load_Attempt({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Attempt':$iattempt});\">$sattempt </button>";  
         }
         //$strS .= " | Attempts $stmp | "; 
      }
   if ($O['disabled'] && $TryAgain)     { 
       if($nAttempt<$AInfo['maxattempts']) $strS .= "<button onclick=\"Questions_Load_Attempt({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Attempt':$nAttempt, 'TryAgain':1});\">Try again</button>";  
     }
     if($nAttempt >= $AInfo['showsolution'] && !($AInfo['showsolution'] < 0) && isset($AInfo['showsolution']) ) { 
        $strS .= "<button style='background-color:$solcolor' onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'Question':{'id':'$Qid'},'COURSE':'$CourseID', 'AID':'$Aid', 'Submit':'SeeSolution'}); \" >Solution </button>";  
     }
    if($nAttempt >= $AInfo['showanswers'] && !($AInfo['showanswers'] < 0) && isset($AInfo['showanswers']) ) { $O['ShowAnswers'] = 1; $O['checked'] = 1; }
    if ($Type == 1) {
        if ($O['checked'] || $O['disabled']) { $value = $Q['A'][0]['@value'];      if($O['ShowAnswers']) $valueAns = $Q['AO'][0]['@value'];   }
        if ($O['Submit']) {
            $submitinput = "$desc<p/><input class='MC-$Qid' type=text id='FillIn-$Qid' value=$value $disabled>$valueAns</input>$strS $solved $debugstr";
        } else { $submitinput = "$desc<p/>$strS "; }
        return "$smath <div style='border:1px solid blue;'> $submitinput </div>"; 
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


    return "$smath <div style='border:1px solid blue;'>$desc $subStr</div>";

}

//-------------------REMOVE AFTER TESTED--------------------
function PHP_Q($Q, $TEMP) {  $phpxml  = $Q['PHPXML']; $tmpfile = "$TEMP/" . uniqid() . ".php";
    file_put_contents("$tmpfile", '<?php ' . $phpxml . ' ?>');  include("$tmpfile");   unlink("$tmpfile");
    return $Q;
}
//-------------------REMOVE AFTER TESTED--------------------
function Question_EditQ($O=array()) { 
   $ignoreKeys = array('UID', 'GQID'); $strE = "";  $class = uniqid();
   $LoadPHP = $O['LoadPHP']; $outputid= $O['outputid']; 

   $LoadPHP= $this->LoadPHP; 
   $outputid = $this->outputid; 
   $qfile = $this->f; 
   $TEMP = $this->tmpdir; 

   $CKEditorRemoveButtons="'Save,NewPage,Print,Templates,Language,Image,Flash,Uploadcare,gg,About'"; 

   $AddAttr = array("Points"=>10, 'Type'=>0); 
    
   FileIORead("$qfile", $QQ, $root, 'ReadQ'); $Q=$QQ['Q'][0]; 
   foreach($AddAttr as $k=>$v) { if(!isset($Q['@attributes'][$k])) $Q['@attributes'][$k] = $v;  }
   $attr = $Q['@attributes']; 
   if($_POST['send']['EditQ']=='Save') {$QData = $_POST['send']['QData']; 
     $class = $_POST['send']['classdata'];
     foreach($Q['@attributes'] as $k=>$v) { if(isset($QData["$class-$k"])) { $Q['@attributes'][$k]=$_POST['send']['QData']["$class-$k"];  }} 
     foreach(array("Description", "Solution") as $i=>$k) if(isset($QData["$class-$k"])) $Q[$k]['@value'] = $QData["$class-$k"]; 
     if(isset($QData["$class-PHPXML"])) $Q['PHPXML'] = $QData["$class-PHPXML"]; 
     foreach($Q['A'] as $k=>$v) { $Q['A'][$k]['@attributes'] ['status'] = $QData["$class-Astatus-$k"]; $Q['A'][$k]['@value'] = $QData["$class-Avalue-$k"];}
     $Q['Modifier']['@attributes'] = array('uid'=>$GLOBALS['uid'],'time'=>time()); 
     $QQ['Q'][0] = $Q; 
     //\IO\pa($_POST);
     //FileIO("$qfile", $QQ, $root, 'DisplayA2XML');
     FileIO("$qfile", $QQ, $root, 'Write');
     echo "Saved file at " . date('Y-m-d H:m:s'); return; 
  }


  foreach($Q['@attributes'] as $k=>$v) {$size=1; if($k=='Ch') $size=20; 
   if(!in_array($k,$ignoreKeys)) { $strE .= " | $k<input id='$class-$k' size=$size class=$class value='$v'></input>"; }
  }
  $desc = $Q['Description']['@value']; $soln = $Q['Solution']['@value']; $PHPXML = $Q['PHPXML']; 
  $strE = togglePHP("$strE <br/><textarea id='$class-Description' class=$class>$desc</textarea><script>CKEDITOR.replace('$class-Description',{mathJaxLib: mathJaxLib, removeButtons:$CKEditorRemoveButtons});</script>", uniqid(),'-','str');
  $strA = ""; 
  foreach($Q['A'] as $k=>$v) {  $valA = $v['@value']; $status=$v['@attributes'] ['status']; if($status) $checkedA = 'checked'; else $checkedA = ""; 
       $strA .= " | <input id='$class-Astatus-$k' class=$class value=$status type='checkbox' $checkedA />"; 
        $strA .= togglePHP("<textarea id='$class-Avalue-$k' cols=8 class=$class ondblclick=\"CKEDITOR.replace('$class-Avalue-$k',{mathJaxLib: mathJaxLib, removeButtons:$CKEditorRemoveButtons});\" cols=30 rows=1>$valA</textarea>",  'ChoicesA'.uniqid(),'-','str',''); 
   }
  $strE .=  togglePHP("$strA<br/>", uniqid(),'-','str', 'Choices'); 
  $strE .= togglePHP("<br/><textarea id='$class-Solution' class=$class>$soln</textarea><script>CKEDITOR.replace('$class-Solution',{mathJaxLib: mathJaxLib, removeButtons:$CKEditorRemoveButtons});</script>", uniqid(),'+','str', 'Solution');
  if($attr['PHP']==1) { 
      echo "<textarea style='display:none' id=$class-defaultPHP>".Questions_DefaultPHP()."</textarea>"; 
      $defaultPHP = "<button onclick=\"document.getElementById('$class-PHPXML').value=document.getElementById('$class-defaultPHP').value; \">DefaultPHP</button>"; 
      $strE .= togglePHP("<br/><textarea id='$class-PHPXML' class=$class cols=100 rows=20>$PHPXML</textarea><br/>$defaultPHP", uniqid(),'+','str', 'PHPXML');
  }
   
   $sfinal = "<span>$strE</span>"; 

   $sfinal .=  "<button onclick=\"
        var ValClass={}; 
	$('.$class').each(function(){var id = $(this).prop('id');  
           if( $(this).prop('type') == 'checkbox') {if( $(this).prop('checked')) $(this).val(1); else $(this).val(0); } //alert(id);
            if(CKEDITOR.instances[id]) ValClass[id] = CKEDITOR.instances[id].getData(); else ValClass[id] = $(this).prop('value');  
        }); 
  dimag({'outputid':'msg-$class','LoadPHP':'$LoadPHP', 'EditQFile':'$qfile', 'EditQ':'Save','QData':ValClass, 'classdata':'$class'});
\">Save</button><span id='msg-$class'></span>";  

   return "$sfinal"; 

}
//--------------------------

 } // Class ends here----------------
 
//-----------------------
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

    //alert( ($("[name='Matlab']").val()) ); return;


    dimag({'outputid':In.outputid,'LoadPHP':In.LoadPHP, 'Question':{'Type':In.Type,'id':In.id, 'Time':In.Time, 'values': In.values},'COURSE':In.CourseID, 'AID':In.Aid, 'Submit':'SubmitQ', 'Attempt':In.Attempt, 'AInfo':AInfo}); 
      $('.MC-'+In.id).prop('disabled',true); 
     // alert(JSON.stringify(In)); 
    return; 

   }

   function isNumber(n) { return !isNaN(parseFloat(n)) && isFinite(n); }
   function Fun_TrackTime(n) { var d = new Date(); TrackTime[n]= d.getTime(); }
 </script>
END;
}
//.....
//-------------------OUTSIDE of the class--------------------
function COPYVK($f,$ufile, $TEMP="/tmp") {   
    if(file_exists($ufile)) return; 
    if(pathinfo($f, PATHINFO_EXTENSION)=='xml') {
        FileIORead($f, $Q, $root, 'ReadQ');
        if($Q['Q'][0]['@attributes']['Random']==1 && $Q['Q'][0]['@attributes']['Type']==0) shuffle($Q['Q'][0]['A']); 
        $Q['Q'][0] = \Q\Q::PHP_Q($Q['Q'][0], $TEMP);         
        FileIO($ufile, $Q, $root, 'Write');
    } else copy($f,$ufile);
  
}


//-----------------------
function Questions_DefaultPHP() {
    return '
$A["PHP_A"]=mt_rand(25,50);
$A["PHP_B"]=mt_rand(25,50);  
  
$A["PHP_C"]=$A["PHP_A"] + $A["PHP_B"]; 
$A["PHP_D"]=$A["PHP_A"] - $A["PHP_B"]; 
$A["PHP_E"]=$A["PHP_A"]; 
$A["PHP_F"]=$A["PHP_B"]; 



foreach($A as $qk=>$qv) {
 $Q["Description"]["@value"] = str_replace("$qk",$qv,$Q["Description"]["@value"]);
  foreach($Q["A"] as $ai=>$av) $Q["A"][$ai]["@value"] = str_replace("$qk",$qv,$Q["A"][$ai]["@value"]);
}
        
';
    
}

//---------END NAME SPACE---------------
}

?>
