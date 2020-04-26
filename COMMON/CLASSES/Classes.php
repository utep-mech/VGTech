<?php
//----------------REMOVE THIS ONCE WORKING-------------------------
namespace Classes {
  class Classes {
        public $LoadPHP='index.php', $outputid='none', $toggle='-', $editor='Default';
        public function __construct($f, $O=array()) { 
          foreach($O as $k=>$v) {$this->$k = $v; }   
          $this->f=$f; $this->id = uniqid();
          if(is_dir($f)) die("'$f' is a directory"); 
          if(!file_exists($f)) { if(!($this->admin)) return; echo "'$f' does't exists"; }
          $this->fext=strtolower(pathinfo($f, PATHINFO_EXTENSION));  
          if($this->editor=='Default') {if($this->fext=='html') $this->editor='ckeditor'; }
        }
   function html() {  $f=$this->f; $oid=$this->outputid; $LoadPHP=$this->LoadPHP; $s = ''; 
    if($this->admin) $s .= "<button onclick=\" dimag({'outputid':'$oid', 'LoadPHP':'$LoadPHP', 'EditFile':'$f'});\">Edit</button><br/>"; 
    if($this->fext == 'html') {
      $s .= file_get_contents($f); 
      $s .= "<script>
	MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']);
	\$('#bodymain').data()['file']='$f'; 
	</script>";
    } else {$s .= '<pre>'.htmlentities(file_get_contents($f)).'</pre>';}
     
    echo $s; 
   }
   //----------------------------
   function xml() {  $f=$this->f; $oid=$this->outputid; $LoadPHP=$this->LoadPHP;  
       $s=''; $ss=''; $Save=0; 
       $xml = simplexml_load_file($f); $i=0; $id=uniqid(); 
       if(isset($_POST['send']['SaveData'])) {$Save=1; $d=$_POST['send']['SaveData']; $id=$d['id']; };
       foreach($xml as $k=>$v) { $a=$v->attributes(); 
	 $n=$a['name']; if(isset($a['Name'])) $n=$a['Name']; 
         $i++; 
         $val=$a['value']; if($Save) {$val=$d["id$id-$i"]; $v['value']=$val; }
         if(isset($a['Description'])) $desc=$a['Description']; else $desc=''; 
         if($k !='ParameterList') $ss .= "<tr><td>$n</td><td><input value='$val' id='id$id-$i' class='c$id' /></td><td>$desc</td></tr>"; 
         $sss = ''; $j=0; 
         if($k=='ParameterList') {
          foreach($v as $kk=>$vv) { $j++; 
           $a=$vv->attributes(); 
	   $nn=$a['name']; if(isset($a['Name'])) $nn=$a['Name']; 
           $val=$a['value']; if($Save) {$val=$d["id$id-$i-$j"]; $vv['value']=$val; }
           if(isset($a['Description'])) $desc=$a['Description']; else $desc=''; 
           $sss .= "<tr><td>$nn</td><td><input value='$val' id='id$id-$i-$j' class='c$id' /></td><td>$desc</td></tr>"; 
          }
          $ss .= "<tr><td>$n</td><td colspan=2><table border=1>$sss</table></td></tr>";
         }
       }
       //echo '<textarea>'.$xml->asXml().'</textarea>'; 
       if($Save) {$xml->asXml($f);     echo "Saved $f at " . date('Y-m-d h:m:s') . "<br/>";}
       $s .= "<table border=1>$ss</table>"; 
    
    if($this->admin) { $s .='<br/>'; 
      $s .= "<button onclick=\" var o={'id':'$id'};
        \$('.c$id').each(function(){ var id= \$(this).attr('id'); o[id] = \$(this).val();  });
        dimag({'outputid':'$oid', 'LoadPHP':'$LoadPHP', 'LoadFile':'$f', 'SaveData': o});
      \">Save</button>"; 
      $s .= "<button onclick=\" dimag({'outputid':'$oid', 'LoadPHP':'$LoadPHP', 'EditFile':'$f'});\">Raw</button>"; 
    }
    echo $s; 
   }
   //----------------------------
   function Edit($LoadPHP='index.php', $oid='none', $etype='Default', $toggle='+') {   
    $f = $this->f; $uqid=$this->id; $LoadPHP=$this->LoadPHP; $editor=$this->editor; $toggle=$this->toggle; 
    $name="id".$uqid; $id=$name; 
    
    $data=file_get_contents($f); if(!($editor=='ckeditor')) $data=htmlentities($data);  
    $s = "<div id='msg_$uqid'></div>
     <br/><textarea rows=20 cols=130 name='$name' id='$id' data-file='$f' data-editor='$editor'>$data</textarea>
     <br/><button onclick=\"
      var einfo= \$('#$id').data(); var f = \$('#f2_$id').val();  einfo.force = \$('#cb_$id').is(':checked')?1:0; 
      if(einfo.editor=='ckeditor') var d=CKEDITOR.instances.$name.getData(); else var d = $('#$id').val(); 
      dimag({'outputid':'msg_$uqid', 'LoadPHP':'$LoadPHP', 'einfo':einfo, 'Save':f,'data':d});
      \">Submit</button>  
      <input type='text' id='f2_$id' size=50 value='$f' name='file'> 
      | Force <input type='checkbox' id='cb_$id'>          
    "; 
     if($editor=='ckeditor') { 
       $s .= "<script>
	 CKEDITOR.replace('$name', { 
		mathJaxLib: mathJaxLib
	});
       </script>"; 
     }
     if($toggle=='+') { 
          $s = "<button class=BEdit onclick=\" 
                var o = \$('#toggle_$uqid'), t = \$(this); 
                \$('.'+t.attr('class')).css('background-color', ''); t.css('background-color', 'yellow');
                if(o.css('display')=='none') t.text('-'); else t.text('+'); 
                o.toggle(); 
               \">+</button>" 
               ."<div id='toggle_$uqid' style='display:none;'>".$s."</div>"; 
     }
     echo $s; 
     
   } 	 
   //----------------------------
   function EditButton() {
      $f = $this->f; $LoadPHP=$this->LoadPHP; $outputid=$this->outputid; $fn=basename($f); 
      echo "<button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'EditFile':'$f'});\">$fn</button>";
   }
  }
  //------------------
  class Setup_Delete {
   public $outputid='tableM', $admin=0, $fMain='Main.xml',  $fLayout="Layout.xml", $fcss="CSS.css"; 
   public function __construct($O=array()) {   foreach($O as $k=>$v) {$this->$k = $v; }   }
   
   public function Initial() {  
    $outputid=$this->outputid; $LoadPHP=$this->LoadPHP; $DIR=$this->DIR;  
    $fMain = "$DIR/".$this->fMain; $fLayout = "$DIR/".$this->fLayout; $fcss = "$DIR/".$this->fcss; 
    if($this->admin) $s = "<br/>Edit:
      <button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'EditFile':'$fMain'});\">Main</button>
      <button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'EditFile':'$fLayout'});\">Layout</button>
      <button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'EditFile':'$fcss'});\">CSS</button>
    "; 
    
   $css=str_replace(array("\n", "\t", "\r"), '', file_get_contents("$fcss") ); 
   $Layout=str_replace(array("\n", "\t", "\r"), '', file_get_contents("$fLayout") ); 
   $s .= "<script> 
        if($('#cssInitial').length == 0) {
             \$('head').append('<style id=cssInitial>$css</style>'); 
             dimag({'outputid':'tableL', 'LoadPHP':'$LoadPHP', 'LoadFile':'$fMain', 'Level':'Level0'});
             dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'LoadFile':'Intro.html', 'Level':'Level1'});
        }
    </script>"; 
    echo $s; 
   }
 }
  //------------------
  function SaveRaw($f,$data, $force=0) {  $dir=dirname($f); 
    if($force) {if(mkdir($dir, 0777, true)) echo "Dir '$dir' created<br/>";  } 
    file_put_contents($f,$data);   
    if(!is_dir($dir)) die("Error! Couldn't save because $dir doesn't exists. Check force to force it."); 
    echo "<br/>Saved $f at " . date('Y-m-d h:m:s') . "<br/>";
  }  
  function SavePOST() {  
     $force=false; if(isset($_POST['send']['einfo']['force'])) $force=$_POST['send']['einfo']['force'];
    \IO\SaveRaw($_POST['send']['Save'],$_POST['send']['data'], $force); 
   } 
  function Check($A,$k,$v) { $flag=0; 
   if(isset($A[$k])) { if(($A[$k]==$v)) $flag=1;   }
   return $flag; 
  }
  function CheckPOST($k,$v) { $flag=0;  
   if(isset($_POST['send'][$k])) { if(($_POST['send'][$k]==$v)) $flag=1;   }
   return $flag; 
  }
   
  function  p(){$s=$this->data; echo "<textarea rows='10' cols='50'>$s</textarea>"; }
  function  pa($a=NULL){ echo "<pre>"; print_r($a); echo "</pre>"; } 
}

?>
