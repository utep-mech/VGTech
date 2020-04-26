<?php
$FirstTime = 1; 
if(isset($_POST['opt']['bodymaindata']['FirstTime'])) $FirstTime = $_POST['opt']['bodymaindata']['FirstTime']; 
if(isset($PostData['Dir'])) $DIR="$DATA/COURSES/".$PostData['Dir']; else $DIR="$DATA/COURSES";
if(isset($_POST['send']['ParamterList'])) { $DIR = $DIR."/".$_POST['send']['ParamterList']; }

if(isset($PostData['Filter'])) { $ffile=$PostData['Filter']; $dir = dirname($ffile); 
    if(isset($PostData['SaveFilter'])) {
        file_put_contents($ffile,json_encode($PostData['SaveFilter'])); 
        echo "Saved!"; 
        return; 
    }
    $PL = new \ParameterLists\ParameterLists("$dir/Main.xml");
    $PL->LoadPHP=$LoadPHP; $PL->ffile = $ffile; $PL->outputid="$outputid-filter";
    $PL->getKeys();
    echo "<hr/><button onclick=\"dimag({'outputid':'$outputid-filter', 'LoadPHP':'$LoadPHP', 'EditFile':'$ffile'});\">Filter Raw Edit</button>";
    echo "<div id=$outputid-filter></div>";
    return;
}
if(isset($PostData['PL2db'])) { $f=$PostData['PL2db'];  if(!file_exists($f)) {echo "file $f doesn't exists"; return; }
    if(isset($PostData['tag'])) $tag=$PostData['tag']; else $tag='tmp';
    
    $PL = new \ParameterLists\ParameterLists($f, $O);
    $PL->url= ($urldb=='VGTech')?"mongodb://vkumar:Villa#1066@vgtech.mu2com.com:27017/admin": 'mongodb://localhost:27017';
    if(isset($PostData['dbins'])) $PL->dbins = ($PostData['dbins']=='true'); else $PL->dbins=false;
    
    $PL->ToArray();  $PL->DIR=$DIR; $PL->DATA=$DATA; $PL->tag=$tag; 
    $A=$PL->A2db();  
    $oDir="$DATA/Contents"; if(!is_dir($oDir)) mkdir($oDir,0777); 
    file_put_contents("$oDir/$tag.json", json_encode($A,JSON_PRETTY_PRINT)); 
    echo "Wrote $oDir/$tag.json"; 
    echo "<br/><textarea cols=100 rows=20>".json_encode($A,JSON_PRETTY_PRINT)."</textarea>";
    
    // \IO\pa(json_encode($PL->A2db(),JSON_PRETTY_PRINT));
    return;
}

//----Initial setup (Load Layout, CSS) ------
\Setup\LoadCSS("$DIR/CSS.css"); //\Setup\Layout("$DIR/Layout.xml"); 
/*
if($FirstTime) {
  \Setup\LoadCSS("$DIR/CSS.css"); //\Setup\Layout("$DIR/Layout.xml"); 
  echo " <script> 
	  var bd = \$('#bodymain').data(); bd.FirstTime = 0; 
             dimag({'outputid':'tableL', 'LoadPHP':'$LoadPHP', 'LoadFile':'Main.xml', 'Level':'Level0','ParamterList':'.'});
      </script>
    ";
  
 return;
}
*/
//-------------------------------------------
if(!is_dir($DIR)) mkdir("$DIR",0777); 
$O=array('LoadPHP'=>$LoadPHP, 'toggle'=>'-', 'outputid'=>$outputid, 'admin'=>$admin,'DIR'=>$DIR); 

 if(isset($_POST['send']['LoadFile']) && !\IO\CheckPOST('Level','Level0') ) {  
   $f=$_POST['send']['LoadFile']; if(!file_exists($f)) $f="$DIR/".$_POST['send']['LoadFile'];
   $u = new \IO\IO($f, $O); if($u->fext=='xml') $u->xml(); else  $u->html(); 
   return; 
 }
 
 
 //------------------------------------
//-------------tmp------------
//if(isset($_POST['send']['Editxml']) ) {  $xml = new \IO\IO("$DIR/Main.xml", $O); $xml->xml(); return; }
/*
echo "<button onclick=\"
  $.ajax({
   url: dimag({'outputid':'tableM', 'LoadFile':'$DIR/tmp.html'}),
   success:function(){ dimag2({'outputid':id2array('#tableM form'), 'LoadPHP':'Questions.php'}); }
  });
  \">Load</button>";
echo "<button onclick=\"dimag2({'outputid':id2array('#tableM div'), 'LoadPHP':'Questions.php'}); \">R2</button>";
echo "<button onclick=\"dimag({'outputid':'tableM', 'EditFile':'$DIR/tmp.html'});\">Edit</button>";
*/
//-------------tmp------------

$O=['editor'=>'textarea','admin'=>$admin]; 
$PL = new \ParameterLists\ParameterLists("$DIR/Main.xml"); 
$PL->LoadPHP=$LoadPHP; $PL->wDIR = $DIR; $PL->admin = $admin; //$PL->menuid='middle';
if(file_exists("$DIR/Filter.json")) $PL->Filter = json_decode(file_get_contents("$DIR/Filter.json"),true); 

\Setup\LoadCSS("$DIR/CSS.css"); 
//if(!isset($_POST['send']['Level'])) { 
   //echo $PL->ArrayToHTML('Level0'); 
//} else {
  echo $PL->ArrayToHTML('All'); $tid=uniqid(); 
  if($admin) echo "<p/>Edit: <button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'EditFile':'$DIR/Main.xml'});\">Main</button>";
  if($admin) echo "<button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'Filter':'$DIR/Filter.json'});\">Filter</button>";
  if($group == 'superadmin' && $admin) {
   //echo "<button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'EditFile':'$DIR/Layout.xml'});\">Layout</button>";
   echo "<button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'EditFile':'$DIR/CSS.css'});\">CSS</button>";
 
   $col = isset($PL->array['@attributes']['Dir']) ? $PL->array['@attributes']['Dir'] : 'Grade06_Math'; 
   echo "<br/><input id='$tid' size=6 value=$col /> <input  type=checkbox id='db$tid' />db"; 
   echo "<br><button onclick=\"
     dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'PL2db':'$DIR/Main.xml',tag: \$('#$tid').val(), dbins: \$('#db$tid').is(':checked') });
   \">PL2db</button>";
   
  }
//}

?>
