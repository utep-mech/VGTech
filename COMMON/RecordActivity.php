<?php
$DIR="$DATA/RecordActivity";  if(!is_dir($DIR)) { echo "Created directory $DIR"; mkdir("$DIR",0777); }
$SaveData=$_POST['send']; 
if(!isset($SaveData['COURSE']) || $admin ) return; 

$sDIR = "$DIR/$uid"; if(!is_dir($sDIR)) { mkdir("$sDIR",0777); }
$sfile = "$sDIR/".date('Y-m-d').'.json'; 

unset($SaveData['AInfo']); //$sDATA[time()] = $SaveData; 
//pa($GLOBALS);

AppendJSON_VK($sfile,$SaveData); 


//echo "<textarea>". file_get_contents($sfile)."</textarea>";
return; 


function AppendJSON_VK($filename,$SaveData,$O='') { //unlink($filename); 
  
  $SaveData['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR']; 
  if(!file_exists($filename)) { $SaveData['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT']; 
    $str = sprintf('"%s":%s',time(),json_encode($SaveData)); 
    file_put_contents($filename,"{ $str }"); 
    return; 
  }

  $str = sprintf('"%s":%s',time(),json_encode($SaveData)); 

  $handle = @fopen($filename, 'r+'); 
  //if ($handle === null) { $handle = fopen($filename, 'w+'); }
  if ($handle) { fseek($handle, 0, SEEK_END); 
     if (ftell($handle) > 0) { 
       fseek($handle, -1, SEEK_END); fwrite($handle, ',', 1); fwrite($handle, $str . '}');
     } else { fwrite($handle, $str); } 
     fclose($handle);
  }
}
?>
