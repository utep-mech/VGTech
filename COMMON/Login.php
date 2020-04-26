<?php
if(isset($PostData['login'])) { $OPT['HOME']="$HOME"; LoginPHP($OPT,$debug,$PHPSELF,$logged, $mesg); echo $mesg; return; } 

if($OPT['logged']) {  
  echo "<br/><button id=logout onclick=\"dimag({'logout':1})\">Logout</button>";
  echo "<br/><button id=modifyuserinfo onclick=\"dimag({'LoadPHP':'UserModify.php'})\">Modify</button>";
} else { $id='userextra'; 
  echo "Userid:<input id=userid size=10 type=text /> Password:<input id=psswd size=10 type=password />";
  //togglePHP("<input id=usertype type=checkbox />Instructor",'usrextra','+');  
  echo "<button id=login onclick=\"Login({'login':1,'json':0})\">Login</button>";
  if($RegisterNewUserEnabled) echo "<button onclick=\"dimag({'RegisterNewUser':1,'LoadPHP':'Register.php'})\">Register</button>";
echo <<<ENDB
<script>  
  function Login(send){ var userid = $('#userid').val(), passwd = $('#psswd').val(); 
    if(userid=='') {alert('Blank userid is not allowed '); return;}
    if(passwd =='') {alert('Blank password is not allowed'); return;}
    dimag({'login':1,'userid':userid,'passwd':passwd,'json':0}); 
  }
</script>
ENDB;
}


if(isset($PostData['logout'])) { $LMsg="$uid has logged out"; 
echo <<<ENDB
<script>
  $.post("$PHPSELF", {'logout':1,'debug':$debug}, function(recv){  $('#Linfo').html('$LMsg'+recv); setTimeout(function(){window.location.reload();}, 1000); })
</script>
ENDB;
return;
}

//------------------------------------------
function LoginPHP($OPT,$debug,$PHPSELF,&$logged,&$msg) { global $DefaultUsersStr;

  $logged = 0;  $output=''; $msg = 'Unsuccessful login attempt'; 
  $UserDIR=$OPT['HOME']."/DATA/USERS"; 
  //---------Default Users---------------
  //$USERSTR='{ 
     //"vkumar":{"UserID":"vkumar","Password":"$1$p4SqwFEe$q8zATOjXI7nfBwxmlwU6//","LastName":"Kumar","FirstName":"V.","Privilege":"Admin"},
     //"vkumar1":{"password":"123","name":"V. Kumar","priv":"Admin"} 
  //}'; 
  //$USERS=json_decode($USERSTR,true);  $retry="<p/><a href='.'>Retry</a>"; 
  $USERS=json_decode($DefaultUsersStr,true); $DefaultUsers=$USERS; $retry="<p/><a href='.'>Retry</a>"; $AdminLevel='DefaultUsers'; 
  //---------Otherwise Read The Users from a file ---------------
  if($debug) $output .= "<br/>Server:" . json_encode($_POST) . '<br/>';  
  $Re=$OPT['skey']; $fkey=-1.8*log(6.9/$Re); $userid=$_POST['send']['userid'];  $passwd=$_POST['send']['passwd']; 

  if(!isset($USERS[$userid])) { $AdminLevel = 'Basic'; 
    if(file_exists("$UserDIR/$userid.json")) { $AdminLevel='RegisteredUsers'; 
     $USERS=json_decode(file_get_contents("$UserDIR/$userid.json"),true); 
    } else { $msg="Userid '$userid' doesn't exists! $retry"; return; } 
  }

  $passS =  $USERS[$userid]["Password"];  $Type =  $USERS[$userid]["Type"];
  if (!(hash_equalsVK($passS, crypt($passwd, $passS)) || $passwd == $passS)) { 
      $msg = "Enter correct password or contact the instructor $retry"; return; 
  }
  if($USERS[$userid]['Privilege']=='SuperAdmin') {$AdminLevel='SuperAdmin'; $USERS[$userid]['Privilege']='Admin'; } //TEMPORARY FIX FOR SUPERADMIN
  // else password and userid is correct. Set the session with correct information
  $name=$USERS[$userid]['FirstName']. " " . $USERS[$userid]['LastName']; 
  if($USERS[$userid]['FirstName']=="" && $USERS[$userid]['LastName']=="") $name=$userid; 
  ini_set('date.timezone', 'America/Denver');
  $time=$_SERVER['REQUEST_TIME']; $timeReadable =  date('Y-m-d H:i:s',$time);
  if(isset($USERS[$userid]['Privilege'])) $priv = $USERS[$userid]['Privilege']; else $priv='Student';  
  $logged = "userid"; $msg = "Contratulations $name! Logined at $timeReadable "; 
  
echo <<<ENDB
<script>  
          var SetSessions = {'logged':'$userid','name':'$name','priv':'$priv','since':'$time', 'AdminLevel':'$AdminLevel'}; 
          $.post('$PHPSELF', {'SetSession':SetSessions,'fkey':'$fkey','debug':$debug}, 
            function(recv){  $('#Linfo').html(recv); setTimeout(function(){window.location.reload();}, 1000); }
          )
</script>
ENDB;
 
}
echo "$output"; 
?>
