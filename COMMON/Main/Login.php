<?php
require_once("$COMMON/CLASSES/USERS.php");

if(isset($_POST['send']['Logout'])) {
  echo 'Logged out';
  echo "<script>
    localStorage.clear();
    var bd = \$('#bodymain').data(); bd.logged = 0;
    setTimeout(function(){window.location.reload();}, 1000);
  </script>";
  return;
}


if(isset($PostData['login'])) { //$OPT['HOME']="$HOME"; 
    $MDB = new \IO\MongoDB([url=>$url, db=>'USERS', col=>'users']);
	\USERS\LoginPHP($DATA,$debug,"dimag.php",$logged, $mesg, $MDB); //LoginPHP($OPT,$debug,"dimag.php",$logged, $mesg); 
	echo $mesg; 
	return; 
} 
$UserData = $BodyData;
if($UserData['logged']) {  
  //echo "<br/><button id=logout onclick=\"dimag({'logout':1})\">Logout33</button>";
  //echo "<br/><button id=modifyuserinfo onclick=\"dimag({'LoadPHP':'UserModify.php'})\">Modify</button>";
} else { 
  $id='userextra'; 
  $sL = "<button id=login onclick=\" \$('#LoginButtons').toggle(); \">Login</button>";
  $s = "Userid:<input id=userid size=10 type=text /> Password:<input id=psswd size=10 type=password />";
 // $s .= "<button id=login onclick=\"Login({'login':1,'json':0})\">Submit</button>";
  $s .= "<button id=login onclick=\" var userid = $('#userid').val(), passwd = $('#psswd').val();
    if(userid=='') {alert('Blank userid is not allowed '); return;}
    if(passwd =='') {alert('Blank password is not allowed'); return;}
    dimag({'login':1,'userid':userid,'passwd':passwd,'json':0}); 
  \">Submit</button>";
  if(isset($_POST['send']['Login'])) echo "<span id='LoginButtons' style='display:inline;float:right;'>$s</span>"; 
  /*
echo <<<ENDB
<script>  
  function Login(send){ var userid = $('#userid').val(), passwd = $('#psswd').val(); 
    if(userid=='') {alert('Blank userid is not allowed '); return;}
    if(passwd =='') {alert('Blank password is not allowed'); return;}
    dimag({'login':1,'userid':userid,'passwd':passwd,'json':0}); 
  }
</script>
ENDB;
 */
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

echo "$output"; 

?>
