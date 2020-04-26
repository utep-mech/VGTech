<?php 

namespace Setup {
  //------------------
  class Setup {
   public $admin=0, $fMain='Main.xml',  $fLayout="Layout.xml", $fcss="CSS.css", $fjs = "Main.js"; 
   public $outputid='tableM', $menuid='tableL', $assid='tableR', $HOME = '/home/vkumar/DLF';
   public function __construct($O=array()) {   foreach($O as $k=>$v) {$this->$k = $v; }   }
   
   public function Initial() {  
    $outputid=$this->outputid; $LoadPHP=$this->LoadPHP; $DIR=$this->DIR;  
    $fMain = "$DIR/".$this->fMain; $fLayout = "$DIR/".$this->fLayout; $fcss = "$DIR/".$this->fcss; 
    
   $this->LoadCSS($fcss); 
   echo "<script> 
             dimag({'outputid':'tableL', 'LoadPHP':'$LoadPHP', 'LoadFile':'$fMain', 'Level':'Level0'});
             dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'LoadFile':'Intro.html', 'Level':'Level1'});
    </script>"; 
    	//$this->Button('Main', array('EditFile'=>"$fMain"));
    	//$this->Button('Layout', array('EditFile'=>"$fLayout"));
    	//$this->Button('CSS', array('EditFile'=>"$fcss"));
   }
   //-------------------------------
   public function LoadCSS($f, $id='cssInitial') { \Setup\LoadCSS($f, $id);  }
   public function Layout($f, $id='contentlayout') { \Setup\Layout($f, $id);  }
   //-------------------------------
   public function Button($n="Main", $O=array(),$ret='echo') {   
    if(!isset($O['LoadPHP'])) $LoadPHP=$this->LoadPHP; else $LoadPHP = $O['LoadPHP']; 
    if(!isset($O['outputid'])) $O['outputid']=$this->outputid;
    $s = "'LoadPHP':'$LoadPHP'"; // start with initial & separate others w/ ','
    foreach($O as $k=>$v) { $s .= ", '$k':'$v'"; } 
    $bstr = " <button onclick=\"dimag({".$s."});\">$n</button> ";
    if($ret=='str') return $bstr; else echo "$bstr";
   }
 }
//-------------
   function LoadCSS($f, $id='cssInitial') {   
     $css=str_replace(array("\n", "\t", "\r"), '', file_get_contents("$f") ); 
     echo  "
        <script> 
          if( \$('#$id').length == 0) { \$('head').append('<style id=$id></style>'); }
          \$('#$id').html('$css'); 
        </script>
     "; 
   }
 //-------------------------------
   function Layout($f,$id='contentlayout') {   
     $str = str_replace(array("\n", "\t", "\r"), '', file_get_contents("$f") ); 
     echo  "
        <script> 
          if( \$('#$id').length == 0) { \$('body').append('<style id=$id></style>'); }
          \$('#$id').html('$str'); 
        </script>
     "; 
   }
  //------------------

} //Namespcae "SETUP" ends here
?>
