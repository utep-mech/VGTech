<?php
if(!$admin) return; 
include("$HOME/COMMON/common-old.php");  
$PData=$_POST['send']; $CourseID =$PData['COURSE']; $LoadPHP = $PData['LoadPHP'];  $outputid=$PData['outputid']; 
$CourseDir="$DATA/COURSES/$CourseID";  $D="$CourseDir/Codes/FEM"; $jf="$D/Input.json";
$id=uniqid();
if(!isset($PData['flag'])) { // Initial display
   $ThisID=$id; if(!is_dir("$D")) mkdir("$D",0777,true); 
   echo "<button onclick=\"dimag({'outputid':'$ThisID','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'List'}); \" >List</button>";
   echo '<span id=$ThisID></span><span id=LoadFEM>hh</span>'; 
   return; 
} 

?>
<script>
var first=1, d={}; 
if(first) {  
  function FEM_JSON(In,f) { var s=''; 
   var twod=['X','d0','ien']; 
   for (var k in In) {if (!In.hasOwnProperty(k)) continue; var id1='id_'+k, o = In[k], ss='';
     
    for (var kk in o) {if (!o.hasOwnProperty(kk)) continue;  var id2=id1+'_'+kk, sss='', oo=o[kk]; 
      if(!(typeof oo == 'object')) { 
         if(f.Get) In[k][kk]=FEM_JSON_Get(id2,'s'); else ss = ss +'<td>'+kk+'=<span id='+id2+ ' onclick="FEM_JSON_Edit(this);">'+o[kk]+'</span></td>';continue; 
      }
       
      for (var kkk in oo) {if(!oo.hasOwnProperty(kkk)) continue;  var id3=id2 + '_'+ kkk, ooo=oo[kkk];  
        if(! (typeof ooo == 'object')) {  var t=s; if(k=='ien') t='I'; if(k=='X' || k=='d0') t='f'; 
          if(f.Get) In[k][kk][kkk]=FEM_JSON_Get(id3,t); else {
           if(Array.isArray(oo)) sss = sss + '<span id='+id3+ ' onclick="FEM_JSON_Edit(this);">'+oo[kkk]+'</span><br/>'; else sss = sss + kkk+'='+oo[kkk]+'<br/>'; 
          }
        }
      }
      ss = ss + '<td>' + sss + '</td>';
    }
      s=s+ '<br/><u>'+k+'</u><table border=1><tr>' + ss + '</tr></table>';
   }
  if(f.Put) {$('#LoadFEM').html(s); }
  if(f.Get) {$('#TA'+f.id).val(JSON.stringify(In, null,2));  }
  first=0;
  }
  
  function FEM_JSON_Edit(e) {var id=$(e).attr('id'); $(e).attr('contenteditable',true);  }
  function FEM_JSON_Get(e,t) {var s=$('#'+e).text(); if(t=='f') s=parseFloat($('#'+e).text()); if(t=='I') s=parseInt($('#'+e).text());  return s; }

}
</script>
<?php
if($PData['flag']=='Save') { file_put_contents($jf,$PData['val']); echo 'Saved '.basename($jf); return; }
 if(!file_exists($jf)) file_put_contents($jf,'{}'); else $s=file_get_contents($jf); 
 //json2html_vk($s); 
//$s='{"A":1}'; 
echo <<<EOD
<button onclick='FEM_JSON($s,{"Put":1});' >ShowJson2</button>
<button onclick='FEM_JSON($s,{"Get":1,"id":"$id"});' >Get</button>

EOD;


echo "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP', 'COURSE':'$CourseID','flag':'Save','GetValID':'TA$id'}); \" >Save</button>";
echo "<br/><textarea id=TA$id cols=100 rows=20>$s</textarea>";


echo '<div>Complete</div>';  
return; 

function json2html_vk($s) {
 
 $s2A=json_decode($s,true); 
 foreach($s2A as $k=>$v) {
   echo "<br/>$k:"; 
   foreach($v as $kk=>$vv) {
     echo ", $kk"; 
     if($k=="Properties") echo "<input type=text value=$vv size=3></input>"; 
     if(in_array($k,array("d0","X","ien"))) {$v1=$vv[0]; $v2=$vv[1]; echo "(<input type=text value=$v1 size=3></input><input type=text value=$v2 size=3></input>)"; }

   }
 } 
}

?>