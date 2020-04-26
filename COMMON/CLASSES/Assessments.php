<?php
//----------------REMOVE THIS ONCE WORKING-------------------------
namespace Assessments { //Questions.............
  class A {
        public $outputid='none', $editor='Default';
        public function __construct($f, $O=array()) { 
          foreach($O as $k=>$v) {$this->$k = $v; }   

	      if(!file_exists($f)) { $file = "$f.html"; 
   	        if(file_exists("$f.json")) $file = "$f.json"; 
   	        if(file_exists("$f.xml")) $file = "$f.xml"; 
	        $f = $file; 
	      }
          if(!file_exists($f)) { if(!($this->admin)) return; echo "'$f' does't exists"; }

          $this->f=$f; $this->id = uniqid(); $this->tmpdir = '/tmp'; 
          if(is_dir($f)) die("'$f' is a directory"); 
          $this->fext=strtolower(pathinfo($f, PATHINFO_EXTENSION));  
          if($this->editor=='Default') {if($this->fext=='html') $this->editor='ckeditor'; }
        }
//----------------------------------
        function A2data() {
        }
//----------------------------------
       function A2html() {  
       }
//----------------------------------
       function Edit() {
           
       }
//----------------------------------------------------------
function ListQ($O = array()) {  
	$f = $this->f; $LoadPHP = $this->LoadPHP; $outputid = $this->outputid; 
	$QDir=$this->QDir; $id=$this->id; $LoadPHP=$this->LoadPHP; $s = ''; 
 if(file_exists($this->f))  $str = file_get_contents($this->f); else $str = '{}'; 
 $strA = json_decode($str,true);   
 if(isset($strA['Questions']) ) $Qstr = 'Questions';  else $Qstr = 'Q'; 
 foreach($strA[$Qstr] as $k=>$v) {  $vn=$v; if(is_array($v)) $vn = key($v); 
  $Type='xml'; 
  if($v[$vn]['a']['Type']=='json') $Type='json'; 
  if($v[$vn]['a']['Type']=='xml') $Type='xml'; 
  if($v[$vn]['a']['Type']=='html') $Type='html'; 
  $f = "$QDir/$vn.$Type"; 
  if(file_exists("$QDir/$vn.json")) $f = "$QDir/$vn.json";  
  if(file_exists("$QDir/$vn.html")) $f = "$QDir/$vn.html";  
  if(file_exists("$QDir/$vn.xml")) $f = "$QDir/$vn.xml";  
  $Qs[$vn]= $f; 
 }

 $kp1=0; $id=uniqid();  $s = ''; 
 foreach($Qs as $k=>$f) { $kp1 = $kp1+1; 
    $fid = pathinfo($f, PATHINFO_FILENAME); 
   $s .= "<button class=$id id='B-$fid' title='$fid' onclick=\"
	 \$('.$id').css('font-weight', 'normal'); \$('#B-$fid').css('font-weight', 'bold');
	 dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP','LoadQFile':'$f'});
   \">Q$kp1</button>";
 }
 return $s; 
}
//----------------------------------------------------------
function AddQ($O=array()) { $AFile = $this->f; $qid=$this->id; $an = basename($AFile); 
   if(file_exists($this->f))  $str = file_get_contents($this->f); else $str = '{}'; 
   $strA = json_decode($str,true);  
   if(isset($strA['Questions']) ) $Qstr = 'Questions';  else $Qstr = 'Q';
 
   if(sizeof($strA[$Qstr])<$this->maxQ) {
       $strA[$Qstr][]= $this->id; file_put_contents($this->f, json_encode($strA));  
     $s .= "Added in $anile Question id $qid"; 
   } else $s .= "Hmm! Max number of Qs allowed is $maxQ."; 

   return $s; 
}
//----------------------------------------------------------
function DelQ($O=array()) { $qid=$this->id; $an = basename($this->f); 
   $strA = json_decode(file_get_contents($this->f),true);  
   if(isset($strA['Questions']) ) $Qstr = 'Questions';  else $Qstr = 'Q';
   $strA[$Qstr] = array_merge(array_diff($strA[$Qstr], array("$qid"))); 
   file_put_contents($this->f, json_encode($strA));  
   return "Removed $qid from $an "; 
}
//----------------------------------------------------------
function ListAll($O=array()) { $QDir = $this->QDir; $s = '';  
  $AFile = $this->f; $LoadPHP = $this->LoadPHP; $outputid = $this->outputid; $id= $this->id; 
  $AllQFile = "$QDir/AllQs.json"; 
  if(file_exists($AllQFile)) $AllQ = json_decode(file_get_contents($AllQFile),true); 

  $UpdateQ="<button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'ListAll':'$QDir', 'AFile':'$AFile', 'UpdateAllQ':1, 'flag':1});\" >Update</button>";

  //---------------------------------
  if(isset($_POST['send']['Keywords'])) {  $keyid=$_POST['send']['id']; 
    $AllQ[$keyid]['Keywords']=$_POST['send']['Keywords']; file_put_contents($AllQFile,json_encode($AllQ)); 
     return; 
  } 
  //---------------------------------
  if(isset($_POST['send']['Duplicate'])) {  $qfile=$_POST['send']['Duplicate']; 
    $newid=uniqid(); $dname=dirname($qfile); $ext = pathinfo($qfile, PATHINFO_EXTENSION);  
    copy($qfile,"$dname/$newid.$ext"); 
    echo "$qfile duplicated to $newid.$ext<br/>"; 
  }
  //---------------------------------
  $strA = json_decode(file_get_contents($this->f),true);  
  if(isset($strA['Questions']) ) $Qstr = 'Questions';  else $Qstr = 'Q';
  
  $files = glob("$QDir/?????????????.{json,xml,html}", GLOB_BRACE); 
  $keys = array(); 
  foreach($files as $k=>$v) { $ss = ''; 
    $vid = pathinfo($v, PATHINFO_FILENAME); 

    $AllQ[$vid]['f']=basename($v);
    if(isset($_POST['send']['Keyword'])) { $cnted = 1; 
      if(isset($AllQ[$vid]['Keywords'])) { $thisKeys = explode(",", preg_replace('/\s+/', '', $AllQ[$vid]['Keywords']));
	if(in_array($_POST['send']['Keyword'], $thisKeys)) $cnted = 0;
      } 
      if($cnted) continue; 
    } 
    if(isset($AllQ[$vid]['Keywords'])) {$Keywords = $AllQ[$vid]['Keywords']; 
      $thisKeys = explode(",", preg_replace('/\s+/', '', $AllQ[$vid]['Keywords']));
      foreach($thisKeys as $thisk=>$thisv) if(!in_array($thisv,$keys)) $keys[] = $thisv; 
    } else $Keywords=''; 

    if(in_array($vid,$strA[$Qstr])) {
     $disabled='disabled'; $notdisabled=''; $color='#ffaaff'; 
    } else { $disabled='';  $notdisabled='disabled'; $color=''; }
    $ss .= "(".($k+1).") " . basename($v)."
      <button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'ListAll':'$QDir', 'AFile':'$AFile', 'Duplicate':'$v', 'flag':1});\" >Duplicate</button>
      <button onclick=\"dimag({'outputid':'msg-$vid-$k', 'LoadPHP':'$LoadPHP', 'AddQ':'$vid', 'AFile':'$AFile', 'flag':1});\" $disabled>Add</button>
      <button style='background-color:$color' onclick=\"dimag({'outputid':'msg-$vid-$k', 'LoadPHP':'$LoadPHP', 'DelQ':'$vid', 'AFile':'$AFile', 'flag':1});\" $notdisabled>Del</button>
      <button class='b-$id' onclick=\"
        var a = $('#msg-$vid-$k').attr('Loaded'); 
        if(a==1) { $('#msg-$vid-$k').attr('Loaded',0); $('#msg-$vid-$k').html(' '); $(this).css('background-color',''); 
        } else { dimag({'outputid':'msg-$vid-$k', 'LoadPHP':'$LoadPHP', 'LoadQFile':'$v'}); 
          $('#msg-$vid-$k').attr('Loaded',1); 
          $(this).css('background-color','yellow'); 
        }
      \" >Load</button>
      <button onclick=\" \$('#keyid-$k-$id').toggle(); \" >Keywords</button>
      <span id='keyid-$k-$id'  style='display:inline;'> <input id='keywords-$k-$id' type=text value='$Keywords' />
      <button onclick=\"
        var keywords = $('#keywords-$k-$id').val(); 
        dimag({'outputid':'msg-$vid-$k', 'LoadPHP':'$LoadPHP', 'Keywords':keywords, 'ListAll':'$QDir', 'id':'$vid', 'AFile':'$AFile', 'flag':1});
      \">Save</button></span>
      <span id='msg-$vid-$k'></span><br/>
     "; 
     $s .= $ss; 
  }
  $skeys='Keywords: '; 
  foreach($keys as $k=>$v) { 
   $skeys .= " <button onclick=\"
        dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'Keyword':'$v', 'ListAll':'$QDir', 'AFile':'$AFile', 'flag':1});
   \">$v</button> "; 
  }
  if(!file_exists($AllQFile) || isset($_POST['send']['UpdateAllQ'])) {file_put_contents($AllQFile, json_encode($AllQ)); echo "$AllQFile created"; }
  return "$UpdateQ $skeys <hr/> $s"; 
}
//----------------------------------------------------------

} // Class ends here----------------


} // namespace ends here

?>
