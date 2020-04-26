<?php
require_once("$COMMON/CLASSES/Questions.php");
require_once("$COMMON/common-old.php");

$PD=$_POST['send']; $attr = isset($PD['a'])?$PD['a']:[]; $uqid=uniqid(); 
$ContentDir="$DATA/Contents";  $QuestionDir="$DATA/Questions"; $CDir=$ContentDir; $QDir=$QuestionDir;

$db= isset($attr['db'])? $attr['db'] : 'COURSES'; $db= isset($PD['db'])? $PD['db'] : $db;  
$col=isset($PD['col'])?$PD['col']:'EA2'; $col=isset($attr['col'])?$attr['col']:$col; $col= isset($attr['q']['find'])?$attr['q']['find']:$col; $Table=$col;
$ucol="Student_$col"; 


$keys=[db=>$db, col=>$col, LoadPHP=>$LoadPHP, outputid=>$outputid]; 
$MDB = new \IO\MongoDB([url=>$url, db=>$db, LoadPHP=>$LoadPHP, collection=>$Table, col=>$col, outputid=>$outputid, admin=>$admin, uid=>$userid]);

if(isset($PD['SaveUserItem'])) { $MDB->SaveUserItem($PD['SaveUserItem'],$PD['data']);  return; }
if(isset($PD['UpdateItem'])) { $MDB->UpdateItem($PD['UpdateItem'],$PD['data']);  return; }
if(isset($PD['EditItem'])) { echo $MDB->EditItem($PD['EditItem']);  return; }

if(isset($PD['f2db'])) {$f=$PD['f2db']['f']; $MDB->f2db($PD['f2db'], $col); echo "added $f"; return; }
if(isset($PD['ListFiles'])) {   echo $MDB->ListFiles($PD['ListFiles']); return; }
if(isset($PD['a']['LoadItemsFrom'])) {  echo $MDB->LoadItems($PD['a']); return; }
$O = array('uwDir'=>"$uDATA/Assessments/$Aid", 'QDir' =>"$DATA/Questions", 'LoadPHP'=>"Questions.php", 'admin'=>$admin, 'outputid'=>$outputid);

if(isset($attr['ParameterList'])) { $q=[find=>$attr['ParameterList']]; $MDB->List2($MDB->ExecCMD($q), [Edit=>0]);   
    if($admin && !isset($attr['firstcall'])) echo '<br/>'.\IO\ApBButton($keys,[outputid=>'tableM',col=>$col,q=>$q],'KeyEdit');
    return; 
}
if(isset($attr['LoadFile'])) { $f = new \Q\Q($attr['LoadFile'], $O); echo $f->Read_Q2();  return; }
if(isset($attr['LoadCFile'])) {$fid=pathinfo($attr['LoadCFile'], PATHINFO_FILENAME);  
    $n=sizeof($MDB->ExecCMD([find=>'Contents', filter=>[id=>$fid], projection=>[id=>1, _id=>0] ])); 
    if($n>0) { echo $MDB->LoadItems([LoadItemsFrom=>'Contents', Items=>[$fid]]);
    } else { $f = new \IO\IO("$CDir/".$attr['LoadCFile'], $O); echo $f->html2(); }  
    return; 
}

if(isset($attr['File'])) { $Q = new \IO\IO("$CDir/".$attr['File'], $O); echo $Q->html2(); return; }
if(isset($PD['a']['LoadFilesFrom'])) { $files=$PD['a']['Files']; $dir=$PD['a']['LoadFilesFrom']; $s='';
    foreach($files as $k=>$v) {  $Q = new \Q\Q("$DATA/$dir/$v", $O); $s .= $Q->Read_Q2();  }
    echo $s;   
    return;
};
if(isset($PD['q']['insert'])) { $MDB->Insert2($PD['q']); echo "Inserted";  return; }
if(isset($PD['q']['update'])) { $MDB->Update2($PD['q']);  echo "Updated ";   return;  }
if(isset($PD['q']['delete'])) { $MDB->Delete2($PD['q']); echo "Deleted";  return;  }
if(isset($PD['q']['drop'])) { $MDB->ExecCMD($PD['q']); echo "Collection dropped";  return;  }
if(isset($PD['q']['find'])) { if(isset($PD['EditDB'])) $MDB->Edit( $MDB->ExecCMD($PD['q']) ); else $MDB->List2( $MDB->ExecCMD($PD['q']) ); return; }
if(isset($PD['q']['create'])) { $MDB->ExecCMD($PD['q']); echo "collection/table created"; return; }
if(isset($PD['q']['listCollections'])) { echo $MDB->ListCollections(); return; } 
//\IO\pa($PD); $MDB->Update2($PD['AddAssess']['q']);
//Other things
if (isset($attr['q'])) { $col=$attr['q']['find'];  
  $MDB->List2($MDB->ExecCMD($attr['q']),[Edit=>isset($attr['Edit'])?$attr['Edit']:0]);  
  if($admin && !isset($attr['firstcall'])) echo '<br/>'.\IO\ApBButton($keys,[outputid=>'tableM',col=>$col,q=>$attr['q']],'KeyEdit');
} else { $a=$PD['a']; $id=$PD['_id']; 
    $d = $MDB->ExecCMD([find=>$col, filter=>[_id=>$PD['_id']] ])[0];  
    //\IO\pa($PD);
    if(!isset($d->a->LoadA)) {$AInfo=uniqid(); $AddA = "<button onclick=\"
     var q={update:'$col', updates:[{q:{_id:'$id'},u:{'\$set':{'a.LoadA':'$AInfo'}}, upsert:true}]}; 
     dimag({outputid:'$outputid', LoadPHP:'$LoadPHP', db:'$db', q:q});
    \">AddA</button>";
    } else $AddA="ChangeA";
    
    
    $EditB=$MDB->EditItem([find=>$col, filter=>[_id=>$PD['_id']] ], [Button=>1,outputid=>$outputid]);
    if( $d->a->zip =='gzip') $d->v=gzuncompress($d->v->getData());
    $Math="<script>MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']);</script>";
    if($admin) echo "$EditB $AddA".'<br/>';
    echo $d->v.$Math;
    
    //\IO\pa($MDB->ExecCMD([find=>$col]) );
}
//$A = $MDB->ExecCMD( isset($attr['q'])?$attr['q']:['find'=>$Table]); $MDB->ignoreEdit=1; $MDB->List2($A);


if(!isset($attr['firstcall'])) return;
if(!$admin) return;

$sdb = "db:<input id=inpdb$uqid type=text size=6 value=$db />"; 
$scol = "Col:<input id=inpcol$uqid type=text size=4 value=$col />";
$sCreateT = "<button onclick=\" var db= $('#inpdb$uqid').val(), col= $('#inpcol$uqid').val();  
   dimag({'outputid':'tableM', 'db':db, 'LoadPHP':'$LoadPHP', 'q':{'create':col}}); 
\">Create Table</button>";
$sListT = "<button onclick=\" var db= $('#inpdb$uqid').val(), col= $('#inpcol$uqid').val();
   dimag({'outputid':'tableM', 'db':db, 'LoadPHP':'$LoadPHP', 'q':{'listCollections':1}});
\">List</button>";
$sListE = "<button onclick=\" var db= $('#inpdb$uqid').val(), col= $('#inpcol$uqid').val();
   dimag({'outputid':'tableM', 'db':db, 'col':col, 'LoadPHP':'$LoadPHP', 'q':{'find':col}});
\">List</button>";

$ListQs = "<button onclick=\"dimag({outputid:'tableM', LoadPHP:'$LoadPHP', db:'COURSES', col:'Q', ListFiles: '$DATA/Questions'});\">Qs</button>";
$ListCs = "<button onclick=\"dimag({outputid:'tableM', LoadPHP:'$LoadPHP', db:'COURSES', col:'Contents', ListFiles: '$DATA/Contents'});\">Cs</button>";


echo "<p/>$sdb $sListT <br/> $scol  $sListE <br/>Files: $ListQs $ListCs"; 

return;


?>