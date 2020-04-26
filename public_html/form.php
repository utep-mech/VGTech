<?php

$f = 'ttt.json'; $debug = 0; 
$fRef = $_POST['bodymaindata']['file']; if($debug) {$fRef = $f; file_put_contents($fRef,''); }
if(!file_exists($fRef)) {echo "{'error':1}"; return;}
$id = $_POST['id']; 

$wdir = dirname($fRef); $bname= basename($fRef,'.html'); 
$f = "$wdir/$bname-forms-$id.json";
$_POST['f']=$f;
$jsonstr = json_encode($_POST); 

if($_POST['form']['action']=='Load') $jsonstr = file_get_contents($f); 
if($_POST['form']['action']=='Save') file_put_contents($f, $jsonstr); 

  if($_POST['dataType']=='json') { 
    echo $jsonstr; 
  } else {
    //echo "<pre>"; print_r($_POST); echo "</pre>";
  }

?>
