<?php
if($_SESSION['AdminLevel']=='SuperAdmin') {AdminDisplay(); echo "<br/>";}

echo <<<END
 <script>
  var info = $('#info').data(); info.initial=1;
 </script>
END;

if(!($_SESSION['AdminLevel']=='SuperAdmin') ) {
 $UInfoData=json_decode(file_get_contents("$DATA/USERS/$uid.json"),true); 
 foreach($UInfoData[$uid]['COURSES'] as $k=>$v)  echo "<script>Add2Info({'$v':'COURSES'}); </script>";
 if(isset($UInfoData[$uid]['JSONString'])) $UInfoJSON = json_decode($UInfoData[$uid]['JSONString'],true);  
 if(isset($UInfoJSON['Initial'])) $InitialString = json_encode($UInfoJSON['Initial']);  
 if(isset($InitialString)) { echo "<script>dimag($InitialString); </script>"; return; }
}

$file="$DATA/Menu.json";
if(file_exists($file)) { $contents = file_get_contents("$file"); $B = json_decode($contents,true);
  foreach($B as $k=>$v) { 
   if(isset($v['LoadPHP']))  $LoadPHP=$v['LoadPHP']; else $LoadPHP="$k.php"; 
   if(isset($v['outputid']))  $outputid=$v['outputid']; else $outputid="middle"; 
   if($v['Allowed']=="$priv" || $v['Allowed']=="All" || $v['Allowed']=="$uid") 
     echo "<button onclick=\"dimag({'outputid':'$outputid','LoadPHP':'$LoadPHP'})\">".$v['Name']."</button>"; 
  }
}

//---------------
function AdminDisplay(){
  $str = "<button onclick=\"dimag({'outputid':'middle','LoadPHP':'Menu.php'})\">Menu</button>"; 
  $str .= "<button onclick=\"dimag({'outputid':'middle','LoadPHP':'Users.php'})\">Users</button>"; 
  $str .= "<button onclick=\"dimag({'outputid':'message'})\">Info</button>"; 
  $str2 = "<button onclick=\"dimag({'serverinfo':1,'outputid':'message'})\">PHPInfo</button>"; 
  $str2 .= "<input type=radio name=info value=1 checked>POST</input>";
  $str2 .= "<input type=radio name=info value=4>Server</input>";
  $str2 .= "<input type=radio name=info value=5>phpinfo</input>";
  $str2 .= "<input type=checkbox name=debug value=1>debug</input>";
  $str2 .= "<input type=checkbox name=EditRawFile value=1>Edit</input>";
  $str2 .= " | <input type=text id=IDStudentAdminName size=4 value=" . $_SESSION['logged'] . "></input> ";
  $str2 .= " <input type=checkbox id=IDStudentAdmin value=1>Student</input>";
  $str2 .= " | <a href=?SESSION=1>Session</a>";
  $str2 .= " | <a href=?PHPINFO=1>PHPInfo</a>";
  togglePHP($str,'AdminDisplay','-','echo','More'); 
  togglePHP($str2,'AdminDisplay2','+','echo',''); 
}

function testsql(){
  $servername = "localhost"; $username = "vkumar"; $password = "Re=2300"; $dbname = "USERS";
  $conn = new mysqli($servername, $username, $password, $dbname);

  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); } 
  $result = mysqli_query($conn, 'show databases');
  while($row2 = mysqli_fetch_array($result)) $rowA[]=$row2; 
  echo '<pre>'; print_r($rowA); echo '</pre>';
  if ($conn->query('show databases')) { echo "successfully"; } else { echo "Failed: " . $conn->error; }

  $conn->close();
}
?>
