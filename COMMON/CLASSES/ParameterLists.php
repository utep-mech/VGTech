<?php
namespace ParameterLists {
 	class ParameterLists { 
 	    public $file='file.xml', $dir='.', $rootdir='.', $mode='file', $type='xml', $admin=0;
            public $PathType='Relative', $menuid='tableL', $outputid='tableM', $outputid2='tableR';   
 	    public $astr='@attributes', $Pstr='Parameter', $PLstr='ParameterList';
 	    
        public function __construct($s=NULL, $type=NULL, $mode=NULL) {
          if(strtolower(pathinfo($s, PATHINFO_EXTENSION))=='json') $type='json'; 
          if(!($type==NULL)) $this->type=$type; 
          if(!($mode==NULL)) $this->mode=$mode; 
          if($type==strtolower('json')) { 
           $this->astr='a'; $this->Pstr='P'; $this->PLstr='PL'; 
          }
          
          if('string'==strtolower($mode)) {
            $this->data=$s; 
           } else {
             $this->file=$s; $this->data=file_get_contents($s); 
           }     
        }
        public function f2Cid($H,$d,$c) { $f2id= $this->f2id; $hid=bin2hex("$H/$d.html"); $f=$this->DIR."/$H/$d.html"; $tag=$this->tag; 
             if(isset($f2id[$hid])) $id=$f2id[$hid]['id']; else { $id=uniqid(); $f2id[$hid]=['f'=>"$H/$d.html", id=>$id];}
             //echo "<br/>$f $c/$id.html"; 
             
             //if(!file_exists("$c/$id.html") && file_exists($f) ) {
             if(file_exists($f) ) { 
                 copy($f,"$c/$id.html"); 
                 echo "Copied $f to $c/$id.html<br/>"; 
                 $tags=explode('/',$d);                   
                 if($this->dbins) {  
                     echo "Inserting $id into DBs<br/>";
                     $MDB = new \IO\MongoDB([url=>$this->url, db=>'COURSES', 'LoadPHP'=>$this->LoadPHP, 'collection'=>'Contents']);
                     $MDB->f2db([f=>"$c/$id.html",a=>[tag=>$tag, tags=>$tags]], 'Contents');
                 }
             }
             $this->f2id=$f2id; 
            return "$id.html"; 
        }
        public function f2id($f) {return pathinfo($f, PATHINFO_FILENAME);}
        public function AInfo2Assess($v,$H,$fid) { $col='Assessments'; $as="@attributes";
            $AFile = $this->DIR."/$H/".$v['LoadA'];  echo "<hr/>$AFile<br/>";
            if(file_exists($AFile)) { $A=json_decode(file_get_contents($AFile),true);
              $A['a']['Name']=$v['Name']; $A['a']['Type']='Assessment'; $A['a']['tag'] = $this->tag;  $A['id'] = $fid; 
              $q=[update=>$col,  updates=>[ [q=>[id=>$fid], u=>['$set'=> $A], upsert=>true]   ]  ] ;
              $MDB = new \IO\MongoDB([url=>$this->url, db=>'COURSES']); $MDB->ExecCMD($q);   //\IO\pa($A);
            }
        }
        public function A2db() { $A=$this->array; $B=[]; $i=0; $C="Contents"; $dir=$this->DIR; $CDir=$this->DATA."/Contents"; 
           if(!is_dir($CDir)) mkdir($CDir,0777); 
           $file2id="$CDir/file2id.json"; 
           if(file_exists($file2id)) $f2id=json_decode(file_get_contents($file2id),true); else $f2id=[]; 
           $this->f2id=$f2id; 
           
           $as="@attributes"; $Ps=$this->Pstr; $PLs=$this->PLstr; 
           $H=isset($A[$as]['Dir'])? $A[$as]['Dir']:'.'; //$A[$as]['id'];
           $HH= is_dir("$dir/$H/$H")?"$H/$H":$H; //$HH due to a bug,  
           
           if(isset($A[$Ps])) foreach($A[$Ps] as $k=>$v) { $d=$v[$as]['id'];  //Level 1 P
             $B[$i]['a']=$v[$as]; 
             if(!isset($v[$as][$PLs])) { 
               $Cid=$this->f2id($this->f2Cid($H,$d,$CDir));
               if(isset($v[$as]['LoadA'])) {$this->AInfo2Assess($v[$as],$HH,$Cid); $B[$i]['a']['LoadA']=$Cid;}
               $B[$i]['a']['LoadCFile']=$this->f2Cid($H,$d,$CDir);
             }
             $i++; 
           }
           if(isset($A[$PLs])) foreach($A[$PLs] as $k=>$v) {$d=$v[$as]['id'];  //Level 1 PL
            $B[$i]['a']=$v[$as]; $B[$i]['a']['LoadCFile']=$this->f2Cid($H,"$d/$d",$CDir); 
            $Cid=$this->f2id($this->f2Cid($H,"$d/$d",$CDir));  
            if(isset($v[$as]['LoadA'])) {$this->AInfo2Assess($v[$as],"$HH/$d",$Cid); $B[$i]['a']['LoadA']=$Cid;}
            $j=0;
            
            if(isset($v[$Ps])) foreach($v[$Ps] as $kk=>$vv) {  $dd=$vv[$as]['id'];  //Level 2 P
              $B[$i]['S'][$j]['a']=$vv[$as]; 
              if(!isset($vv[$as][$PLs])) {
                  $B[$i]['S'][$j]['a']['LoadCFile']=$this->f2Cid($H,"$d/$dd",$CDir); 
                  //echo "<p/>$dir/$HH/$d/$dd ".$this->f2Cid($H,"$d/$dd",$CDir);
                  $Cid=$this->f2id($this->f2Cid($H,"$d/$dd",$CDir));
                  if(isset($vv[$as]['LoadA'])) {$this->AInfo2Assess($vv[$as],"$HH/$d",$Cid); $B[$i]['S'][$j]['a']['LoadA']=$Cid;}
                  $j++; 
              }
            }
            if(isset($v[$PLs])) foreach($v[$PLs] as $kk=>$vv) { $dd=$vv[$as]['id'];  //Level 2 PL
              $B[$i]['S'][$j]['a']=$vv[$as]; 
              $B[$i]['S'][$j]['a']['LoadCFile']=$this->f2Cid($H,"$d/$dd",$CDir); 
              $Cid=$this->f2id($this->f2Cid($H,"$d/$dd",$CDir)); 
              if(isset($vv[$as]['LoadA'])) {$this->AInfo2Assess($vv[$as],"$HH/$d/$dd",$Cid); $B[$i]['S'][$j]['a']['LoadA']=$Cid;  }
              $m=0; 
              if(isset($vv[$Ps])) foreach($vv[$Ps] as $kkk=>$vvv) { $ddd=$vvv[$as]['id'];
                $B[$i]['S'][$j]['S'][$m]['a']=$vvv[$as]; 
                $B[$i]['S'][$j]['S'][$m]['a']['LoadCFile']=$this->f2Cid($H,"$d/$dd/$ddd",$CDir);
                $Cid=$this->f2id($this->f2Cid($H,"$d/$dd/$ddd",$CDir));
                if(isset($vvv[$as]['LoadA'])) {$this->AInfo2Assess($vvv[$as],"$HH/$d/$dd",$Cid); $B[$i]['S'][$j]['S'][$m]['a']['LoadA']=$Cid;}
                $m++;
              }
              $j++;
            }
            $i++; 
           }
           $BB=[a=>$A[$as],'S'=>$B];
           if($this->dbins) { // Save to DATABASE
             $q = [insert => $this->tag, documents =>  $B ];  \IO\pa($B);
             $MDB = new \IO\MongoDB([url=>$this->url, db=>'COURSES', 'LoadPHP'=>$this->LoadPHP, 'collection'=>$this->tag]);
             $MDB->Insert2($q); 
           }
           file_put_contents($file2id,json_encode($this->f2id,JSON_PRETTY_PRINT)); echo "Wrote $file2id...";
            return $BB; 
        }
       public function ToArray() { $this->array = json_decode($this->data,TRUE);
 	     if($this->type==strtolower('json')) {    
 	        $this->array = json_decode($this->data,TRUE);
 	     } else {
 	        $xml = simplexml_load_string($this->data); $Pstr=$this->Pstr; $PLstr=$this->PLstr;
 	        $PL=json_decode(json_encode($xml),TRUE); 
            if(isset($PL[$Pstr]) && !isset($PL[$Pstr][0])) {
	        $Ptmp=$PL[$Pstr]; unset($PL[$Pstr]); $PL[$Pstr][0]=$Ptmp;
            }	
            if(isset($PL[$PLstr]) && !isset($PL[$PLstr][0])) {
              $Ptmp=$PL[$PLstr]; unset($PL[$PLstr]); $PL[$PLstr][0]=$Ptmp;
            }
            
            foreach($PL[$PLstr] as $k=>$v) { 
              if(isset($v[$Pstr]) ) if(!isset($v[$Pstr][0])) {  
               $Ptmp=$v[$Pstr]; unset($PL[$PLstr][$k][$Pstr]); $PL[$PLstr][$k][$Pstr][0]=$Ptmp; 
              }
              if(isset($v[$PLstr]) && !isset($v[$PLstr][0])) {
               $Ptmp=$v[$PLstr]; unset($PL[$PLstr][$k][$PLstr]); $PL[$PLstr][$k][$PLstr][0]=$Ptmp;
              }
              if(isset($PL[$PLstr][$k][$PLstr])) foreach($PL[$PLstr][$k][$PLstr] as $kk=>$vv) {
                 if(isset($vv[$Pstr]) ) if(!isset($vv[$Pstr][0])) {  
                  $Ptmp=$vv[$Pstr]; unset($PL[$PLstr][$k][$PLstr][$kk][$Pstr]); $PL[$PLstr][$k][$PLstr][$kk][$Pstr][0]=$Ptmp; 
                 }
              }
            }
            $this->array = $PL; 
         }
        } 
       //--------------------
       public function countVK($a) {return count($a); }
       public function KeysButton($id,$n) { 
           $LoadPHP=$this->LoadPHP; $ffile=$this->ffile; $outputid=$this->outputid; 
           if(file_exists($ffile)) $Filter = json_decode(file_get_contents($ffile),true);
           
           if(in_array($id,$Filter)) $v=1; else $v=0; 
           if($v) $c='GreenYellow'; else $c=''; //$c='line-through'; text-decoration
           return  "<button id='$id' class='FilterKeys' style='background-color:$c;' v=$v onclick=\" 
           var c='', keys=[];        
             if( \$(this).attr('v') == 1) { \$(this).attr('v',0);  c=''; 
             } else { \$(this).attr('v',1); c='GreenYellow'; }
            \$(this).css('background-color', c); 
           \$('.FilterKeys').each(function() {if( \$(this).attr('v') == 1) keys.push( \$(this).attr('id') ); });
           dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'Filter':'$ffile','SaveFilter':keys});           
          \">$n</button>";
           
       }
       
       public function getKeys($O=[]) { if(!isset($this->array)) $this->ToArray(); 
         $astr=$this->astr; $Pstr=$this->Pstr; $PLstr=$this->PLstr;
         $PL=$this->array; $s = ''; 
         foreach($PL[$Pstr] as $kk=>$vv) $s .=  $this->KeysButton($vv[$astr]['id'],$vv[$astr]['Name']);
         foreach($PL[$PLstr] as $k=>$v) { $id=$v[$astr]['id']; $n=$v[$astr]['Name'];
             $s .=  "<br/>". $this->KeysButton($id,"<b>$n</b>"). ": "; 
             foreach($v[$Pstr] as $kk=>$vv) $s .=  $this->KeysButton($vv[$astr]['id'],$vv[$astr]['Name']); 
             foreach($v[$PLstr] as $kk=>$vv) { $id=$vv[$astr]['id']; $n=$vv[$astr]['Name'];
                 $s .=  "<br/>&nbsp;&nbsp;<b>". $this->KeysButton($id,"<b>$n</b>"). "</b>: ";
                 foreach($vv[$Pstr] as $kkk=>$vvv) $s .=  $this->KeysButton($vvv[$astr]['id'],$vvv[$astr]['Name']);
             }
         }
         echo "$s";
       }
       //--------------------
       public function ArrayToHTML($L='All') { $s=''; $this->auto = 0;  $admin = $this->admin;
         if(!isset($this->array)) $this->ToArray(); 
         //$this->pa($this->array); 
         $astr=$this->astr; $Pstr=$this->Pstr; $PLstr=$this->PLstr; 
         $PL=$this->array; $rootdir ='.';
        if(isset($PL[$astr]['Dir'])) { $rootdir = $PL[$astr]['Dir']; 
         $this->rootdir=$PL[$astr]['Dir'];  $this->dir=$PL[$astr]['Dir'];  
         $this->PathType='Full';
        }
        if($PL[$astr]['DefaultFile']=='Auto') { $this->auto = 1; $autotag='Ch'; } // auto generate dir structure
        if(isset($PL[$astr]['Tag'])) { $autotag = $PL[$astr]['Tag'];} else { $autotag='Ch'; } // auto generate dir structure

        if($L=='Level0') {return $this->Attributes($PL[$this->astr]); }
        
      	if(isset($PL[$Pstr]) ) {$s .= $this->Parameter($PL[$Pstr],'Level1P'); $nL1 = $this->countVK($PL[$Pstr]); }
        if(isset($PL[$PLstr])) $PL1=$PL[$PLstr]; 
        
        $s1=''; 
        foreach($PL1 as $k=>$v) { $id=uniqid(); $s2=''; 
          if(isset($this->Filter) && !$admin) if(!in_array($v[$this->astr]['id'],$this->Filter)) continue;
        
          if(isset($v[$astr]['toggle'])) $toggle=$v[$astr]['toggle']; else $toggle='-'; 
          if(!isset($v[$astr]['id']) && $this->auto) { $v[$astr]['id']="$autotag".($nL1 + $k); }
          if(isset($v[$astr]['id'])) {$dir=$v[$astr]['id']; $this->dir = "$rootdir/$dir"; }
          $s1 .= $this->Attributes($v[$astr], 'Level1PL');    
          if(isset($v[$Pstr])) {$s2 .= $this->Parameter($v[$Pstr], 'Level2P');  $nL2 = $this->countVK($PL[$Pstr]); }
          if(isset($v[$PLstr])) { $PL2=$v[$PLstr]; 
            foreach($PL2 as $kk=>$vv) { $s2tmp = ''; 
              if(isset($this->Filter) && !$admin) if(!in_array($vv[$this->astr]['id'],$this->Filter)) continue;
            
              if(isset($vv[$astr]['toggle'])) $toggle2=$vv[$astr]['toggle']; else $toggle2='-'; 
              if(!isset($vv[$astr]['id']) && $this->auto) { $vv[$astr]['id']=$autotag.($nL2+$kk + 1); }
              if(isset($vv[$astr]['id'])) {$this->dir = "$rootdir/$dir/".$vv[$astr]['id'];}
              $s2 .= $this->Attributes($vv[$astr],'Level2PL');    
              if(isset($vv[$Pstr])) $s2tmp .= $this->Parameter($vv[$Pstr], 'Level3P');
              $s2 .= $this->toggle("$id-$kk",$s2tmp,$toggle2); 
            }
          }       
          $s1 .= $this->toggle($id,$s2,$toggle); 
         }
         $s .= $s1; 
         return $s;            
       } 
       //--------------------
       public function Load() { echo $this->ArrayToHTML();          
 	      \IO\EditRaw($this->dir."/s.xml"); 
        }         
        
//Elements ................ 
    	public function Parameter($P,$L='Level0') {  $s=''; $id=uniqid(); 
    	 foreach($P as $k=>$v) { $n=$v[$this->astr]['Name'];  
	      if($this->auto && !isset($v[$this->astr]['id'])) $v[$this->astr]['id'] = (1+$k);
	      if(isset($this->Filter) && !$this->admin) if(!in_array($v[$this->astr]['id'],$this->Filter)) continue;
    	  $s .= $this->Attributes($v[$this->astr], $L);  
    	 }
    	 return "$s"; 
    	} 
    	
//Attributes ................     	
    	public function Attributes($a,$L='Level0', $br='<br/>') { if(isset($this->dir)) $dir=$this->dir; else $dir='';  
    	 $s=''; $n='Not Defined'; $id=uniqid(); $LoadPHP=$this->LoadPHP; $PathType=$this->PathType; $outputid2 = $this->outputid2; 
    	 if(isset($a['Name'])) $n=$a['Name']; 
    	 if(isset($a['name'])) $n=$a['name'];
    	 if(isset($a['id'])) $iid=$a['id']; else $iid=$id; 
    	 //$menuid='tableL';  if(isset($a['menuid'])) $menuid=$a['menuid']; 
    	 //$outputid='tableL';  if(isset($a['outputid'])) $outputid=$a['outputid']; 
    	 
    	 if($L=='Level0') { $outputid=$this->menuid; } else { $outputid='tableM';  }
	     $f='file.html';  $disabled='disabled';
	     if(isset($a['id'])) {$f=$a['id'].'.html'; $disabled='';} 
	     if(isset($a['File'])) {$f=$a['File']; $disabled=''; } 
    	 foreach($a as $k=>$v) { $s .= sprintf('(%s:%s), ',$k, $v);    }
         if(isset($a['ParameterList'])) {
           $LoadFile='ParamterList'; $file=$a['ParameterList']; $outputid = $this->menuid; 
         } else { $LoadFile='LoadFile'; $file="$dir/$f"; } 

         if(isset($a['LoadA'])) { 
           if(isset($this->wDIR)) $wDIR = $this->wDIR; else $wDIR='.'; 
	   $LoadA=$a['LoadA']; 
    	   $ss = "
    	       $br <button class='$L' onclick=\" 
	           $('button').css('background-color', ''); $(this).css('background-color', 'yellow');
    	          if(event.shiftKey) \$('#$id').toggle(); 
    	          else {
	     $.ajax({
                url: dimag2({'outputid':'$outputid', 'id':'$iid', 'LoadPHP':'$LoadPHP', '$LoadFile':'$file', 'Level':'$L', 'PathType':'$PathType'}),
                success:function(){ 
		  setTimeout(function(){
                    dimag2({'outputid':id2array('#$outputid form'), 'file':'$file', 'LoadPHP':'Questions.php'}); 
		  },500); // wait 100ms
                }
             });
    	    dimag2({'outputid':'$outputid2', 'id':'$outputid', 'LoadPHP':'Assessment.php', 'RefFile':'$wDIR/$file', 'LoadA':'$LoadA'});
    	          }
    	        \" $disabled>$n</button>
    	        <span id=$id style='display:none;'>$s</span>
    	   "; 
	 } else {
    	   $ss = "
    	       $br <button class='$L' onclick=\" 
	           $('button').css('background-color', ''); $(this).css('background-color', 'yellow');
    	          if(event.shiftKey) \$('#$id').toggle(); 
    	          else {
    	          dimag({'outputid':'$outputid', 'id':'$iid', 'LoadPHP':'$LoadPHP', '$LoadFile':'$file', 'Level':'$L', 'PathType':'$PathType'});
                  $('#$outputid2').html(' ');
    	          }
    	        \" $disabled>$n</button>
    	        <span id=$id style='display:none;'>$s</span>
    	   "; 
	 }
         return $ss; 
    	} 
//---------------
    	public function toggle($id,$s='None', $key='+', $showkey='+', $hidekey='-') {
    	 $s2=''; $display='none'; if($key=='-') $display='inline'; 
    	 $st = "<button onclick=\"     
    	   if( \$(this).text() == '$showkey') { 
    	     \$(this).text('$hidekey'); \$('#$id').show();
    	   } else { 
    	    \$(this).text('$showkey'); \$('#$id').hide();
    	   }
    	 \">$key</button>";  
    	 
    	 if($s != 'None') $s2 = "<span id=$id style='display:$display;'>$s</span>";
    	 return $st . $s2;  
    	
    	}
    	//---------------
       function  p(){$s=$this->data; echo "<textarea rows='10' cols='50'>$s</textarea>"; }
       function  pa($a=NULL){ echo "<pre>"; print_r($a); echo "</pre>"; } 
	} 
	
	
}

?>
