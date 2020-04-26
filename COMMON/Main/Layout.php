<?php
$PD=$_POST['send']; $thisPHP=$LoadPHP; 
if($BodyData['group']=='superadmin') $admin=1; 
//$Layout="$HOME/Layout"; if(!is_dir($Layout)) mkdir($Layout); 
//$f = "$Layout/bodymain.xml"; 
$db='COURSES'; $col='Layout'; $id='main'; $uqid=uniqid(); 
$MDB = new \IO\MongoDB([url=>$url, db=>$db]);

if(isset($PD['Updatedb'])) { $v=$PD['Updatedb']['v']; $id=$PD['Updatedb']['id']; $col=$PD['Updatedb']['col'];
    $MDB->ExecCMD([update=>$col, updates=>[ [q=>[id=>$id], u=>['$set'=>[v=>$v] ],upsert=>true] ] ]); 
    return; 
}

if(isset($PD['UpdatedbAllRaw'])) { $v=$PD['UpdatedbAllRaw']['v']; $col=$PD['UpdatedbAllRaw']['col'];
  $A=json_decode($v,true);  foreach($A as $k=>$v) $u[$k]=[q=>[id=>$v['id'] ], u=>['$set'=>$v], upsert=>true]; 
  $MDB->ExecCMD([update=>$col, updates=>$u]); echo 'Updated at '.date("Y-m-d H:i:s");
  return;
}

if(isset($PD['EditAlldb'])) { $col=$PD['EditAlldb']['col'];
  echo "<textarea cols=70 rows=20 id=TA$uqid>".json_encode($MDB->ExecCMD( [find=>$col, projection=>[_id=>0] ] ),JSON_PRETTY_PRINT)."</textarea>";
  echo "<br/><button onclick=\"dimag({outputid:'EAdb$uqid',LoadPHP:'$LoadPHP', UpdatedbAllRaw:{db:'$db', col:'$col', v: $('#TA$uqid').val() } });\">Save</button>";
  echo "<span id=EAdb$uqid></span>";
  return;
}

if(isset($PD['Editdb'])) { $id=$PD['Editdb']['id']; $col=$PD['Editdb']['col'];
 echo "<textarea cols=70 rows=20 id=TA$uqid>".$MDB->ExecCMD( [find=>$col,  filter=>[id=>$id] ] )[0]->v."</textarea>"; 
 echo "<br/><button onclick=\"dimag({outputid:'$outputid',LoadPHP:'$LoadPHP', Updatedb:{db:'$db', col:'$col', id:'$id', v: $('#TA$uqid').val() } });\">Save</button>";
 return; 
}

if(sizeof($MDB->ExecCMD( [find=>$col,  filter=>[id=>$id] ] )) < 1 ) { // populate with the default values
    $u=[a=>[Name=>'Main'], v=>file_get_contents("$HOME/Layout/bodymain.xml")];
    $MDB->ExecCMD([update=>$col,  updates=>[ [q=>[id=>$id], u=>['$set'=>$u], upsert=>true]   ]  ]); echo 'Updated';
}


$fmenu = "$DATA/MainMenu.json"; if(!file_exists($fmenu)) copy("$HOME/Layout/menu.json", $fmenu);  
//$mainMenu = json_decode(file_get_contents($fmenu),true);  
$mainMenu = $MDB->ExecCMD( [find=>'mainMenu',projection=>[_id=>0]] ); 

//\IO\pa($mainMenu);

if(sizeof($MDB->ExecCMD( [find=>'mainMenu',projection=>[_id=>1]] )) < 1 ) { // populate with the default values
    $i=0;  foreach(json_decode(file_get_contents($fmenu),true) as $k=>$v) {  $u[$i]=[q=>[id=>$k], u=>['$set'=>$v], upsert=>true]; $i++;  }
    $MDB->ExecCMD([update=>'mainMenu',  updates=> $u]); echo 'Updated';
}

//echo "<textarea>".json_encode($MDB->ExecCMD( [find=>'mainMenu',projection=>[_id=>0]] ),JSON_PRETTY_PRINT)."</textarea>"; 

//if(!file_exists($f)) { $u = new \IO\IO($f); $u->Edit(); unset($u); return;} else { $n = $BodyData['name']; }
$n = $BodyData['name'];

 // \IO\pa($BodyData); \IO\pa($mainMenu);
 $s = ''; $sguest='';
 //if(isset($mainMenu['Actions']['HideKeys'])) {  $hk=$mainMenu['Actions']['HideKeys']; echo "<script>var hk = $hk;</script>"; }  
 //else echo '<script>var hk = 0;</script>';
 $group = ($BodyData['group']=='superadmin' || $BodyData['group']=='admin') ? 'admin' : 'student';
 foreach($mainMenu as $k=>$v) { $v = is_object($v)?(array)$v:$v; 

    if(isset($v['groups']) && $v['group'] != 'all' && ($group != 'admin')) { if(!in_array($BodyData['group'],$v['groups'])) continue; }
    if(isset($v['Name'])) $name=$v['Name']; else $name=$k; 
    if(isset($v['LoadPHP'])) $LoadPHP=$v['LoadPHP']; else $LoadPHP="Main.php"; 
    $outputid = isset($v['outputid'])? $v['outputid'] : "tableL";
    if(isset($v['Dir'])) $Dir=$v['Dir']; 
    if(isset($v['a'])) $a=json_encode($v['a']); else $a='{}';  
 
    if(isset($v['group']) && $v['group'] == 'guest') {
        $sguest .= sprintf('<button onclick=\'dimag({LoadPHP:"%s",outputid:"%s",a:%s});\'>%s</button>',$LoadPHP,$outputid,$a,$name);
        continue;
    }
    
    if(isset($v['subdir'])) { $subdir = $v['subdir']; 
     $s .= "<button onclick=\"
        var bd = \$('#bodymain').data(); bd.subdir= '$subdir'; //if(hk) \$('#mainbuttons').hide(); 
	dimag({'LoadPHP':'$LoadPHP','outputid':'$outputid'});
       \">$name</button>";
    } else {
        if(isset($v['Dir'])) $s .= "<button onclick=\"dimag({'LoadPHP':'$LoadPHP','outputid':'$outputid','Dir':'$Dir'}); 
                                  //if(hk) \$('#mainbuttons').hide(); 
                              \">$name</button>";
        else $s .= <<<END
        <button onclick='
          dimag({LoadPHP:"$LoadPHP",outputid:"$outputid",a:$a}); 
          //if(hk) $("#mainbuttons").hide(); 
        '>$name</button>
END;
    }

 }
 echo $sguest; 
 if($BodyData['logged']) echo "<span id=mainbuttons>$s</span>"; 

 if($BodyData['group']=='superadmin') $sadmin = "
    <button onclick=\"dimag({'outputid':'tableM','EditFile':'$fmenu'});\">Menu</button>
    <button onclick=\"dimag({'outputid':'tableM','EditFile':'$HOME/setup.json'});\">setup</button>
    <button onclick=\"dimag({outputid:'tableM',LoadPHP:'$thisPHP',Editdb:{id:'main',col:'Layout'} });\">ELayout</button>
    <button onclick=\"dimag({outputid:'tableM',LoadPHP:'$thisPHP',EditAlldb:{col:'mainMenu'} });\">EmainMenu</button>
    <button onclick=\"dimag({'LoadPHP':'Manage.php','outputid':'middle'});\">Manage</button>
     | Edit <input class=adminkeys type=checkbox id=AdminEdit /> | PostedValue <input class=adminkeys type=checkbox id=PostedValue /> | 
     Debug <input type=checkbox class=adminkeys id=AdminDebug /> 
  ";
 if($BodyData['group']=='superadmin') echo \IO\toggle(uniqid(),$sadmin); 

 if($BodyData['group']=='admin' || $admin) echo " | Admin <input type=checkbox class=adminkeys id=studentkey onclick=\"
    var bd = \$('#bodymain').data(); if( \$('#studentkey').prop('checked')) bd.student = 1; else bd.student = 0; 
 \" /> "; 

 if($logged) echo " <span style='float:right;'> $n<button onclick=\"dimag({'Logout':'1'});\">Logout</button></span>";
 else echo " <span style='float:right;'> <button onclick=\"dimag({'Login':'1'});\">Login</button></span>";
 
 echo $MDB->ExecCMD( [find=>$col,  filter=>[id=>$id] ] )[0]->v;  //echo file_get_contents($f);
 
?>


