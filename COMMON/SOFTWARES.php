<?php
require_once("$COMMON/CLASSES/IO.php");
require_once("$COMMON/CLASSES/ParameterLists.php");
require_once("$COMMON/CLASSES/Defaults.php");
if(isset($_POST['send']['Save'])) { \IO\SavePOST(); return; }
//------------------------------------
$DIR="$DATA/SOFTWARES";  if(!is_dir($DIR)) mkdir("$DIR",0777); 
$LoadPHP = $_POST['send']['LoadPHP'];  $outputid = $_POST['send']['outputid'];
$O=array('LoadPHP'=>$LoadPHP, 'toggle'=>'-', 'outputid'=>$outputid,'admin'=>$admin,'DIR'=>$DIR); 
$Setup = new \IO\Setup($O);
 
 if($_POST['send']['id']=='Solve') {
   $Dir="/home/vkumar/Web-Pore-Network/RUN"; 
   $Exe="/home/vkumar/Web-Pore-Network/Pore-Network/a.out"; 
   $Exe="/home/vkumar/Web-Pore-Network/RUN/run.sh"; 
   echo "Running... <pre>cd $Dir; $Exe &>Output.log <br/><hr/>";
   $cwd=getcwd(); chdir($Dir); 
    $cmd="$Exe > Output.log  "; 
   //passthru ("$Exe &> Output.log  "); 
  //echo exec($cmd, $ret);
  //echo system("$Exe ", $ret);
   //$pid = shell_exec($cmd); echo $pid;
   //$pid=shell_exec("$Exe >Output.log  | at now & disown");
  $spec = [ 0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'] ];

  $pfile= "$Dir/running_proc.json"; 
 
   
  $running = 0; 
  if(file_exists($pfile)) {
    $proc_details = json_decode(file_get_contents($pfile),true);
    $pid = $proc_details['pid'];
    $running = posix_kill($pid,0);
  }
  if($running) {
    echo "Process id=$pid is running"; 
  } else {
    $proc = proc_open("$cmd", $spec, $pipes); $proc_details = proc_get_status($proc);
    file_put_contents($pfile,json_encode($proc_details));
  }

  $pid = $proc_details['pid'];
  pa($proc);
  pa($proc_details);

  echo $pid;


   echo "</pre>Completed <br/><hr/>$pid";  

  $f1= "$Dir/depth_at_diff_times.png"; 
  $f2= "$Dir/depth.png"; 
  $f3= "$Dir/rate.png"; 

  $src = 'data: '.mime_content_type($image).';base64,'.base64_encode(file_get_contents($f1));
  echo '<img width=400px src="' . $src . '">';

  $src = 'data: '.mime_content_type($image).';base64,'.base64_encode(file_get_contents($f2));
  echo '<img width=400px src="' . $src . '">';

  $src = 'data: '.mime_content_type($image).';base64,'.base64_encode(file_get_contents($f3));
  echo '<img width=400px src="' . $src . '">';

   chdir($cwd);

   exit();

 }

 if(isset($_POST['send']['EditFile'])) { $u = new \IO\IO($_POST['send']['EditFile'], $O); $u->Edit(); return; } 
 if(isset($_POST['send']['LoadFile']) && !\IO\CheckPOST('Level','Level0') ) {  
   $f=$_POST['send']['LoadFile']; if(!file_exists($f)) $f="$DIR/".$_POST['send']['LoadFile'];
   $u = new \IO\IO($f, $O); if($u->fext=='xml') $u->xml(); else  $u->html(); 
   return; 
 }
 
 
 //------------------------------------
$PL = new \ParameterLists\ParameterLists("$DIR/".$Setup->fMain); $PL->LoadPHP=$LoadPHP; 
if(!isset($_POST['send']['Level'])) { 
   echo $PL->ArrayToHTML('Level0');
   $Setup->outputid='tableM';    $Setup ->Initial(); 
} else {
  echo $PL->ArrayToHTML('All');
}

return; 

?>
 
