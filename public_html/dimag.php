<?php 
    session_start();
    $DataServer = 'Local'; //User server from same host-server for web, remote not working for some browser
    global $HOME,$WebHome,$COMMON,$DATA, $TEMP, $admin,$priv, $logged, $uid, $EditButtonText, $DefaultUsersStr; 

    $WebHOME=dirname($_SERVER['SCRIPT_FILENAME']); 
    if(!file_exists("$WebHOME/setup.php")) exit("copy setup_default.php to setup.php & setup paths"); else include("$WebHOME/setup.php"); 
    //$HOME="/home/www/DLF2"; $WebHOME="/home/www/DLF2/public_html"; $COMMON="$HOME/COMMON"; $DATA="$HOME/DATA"; 
    
    $TEMP="$DATA/TEMP"; $BACKUP="$DATA/BACKUP"; 
    foreach(array("DATA"=>$DATA, "TEMP"=>$TEMP, "BACKUP"=>$BACKUP) as $k=>$v) { if(!is_dir($v)) {if(!mkdir($v,0777)) die("Failed to create $v");} }

    $EditButtonText="Edit"; if(file_exists("$WebHOME/images/Edit.png")) $EditButtonText="<img src=images/Edit16x16.png />";
    if(file_exists("$COMMON/Include.php")) include("$COMMON/Include.php");
    
    //echo '.......'.pa($SuperAdmins); pa($_SESSION);
    if($_SESSION['priv']=='Admin') { 
     if(isset($_GET['LoadPHP']) && file_exists($_GET['LoadPHP']) )  {include($_GET['LoadPHP']);  exit('<p/>Directly accessing | <a href=.>DONE</a>'); }
    }

    $OPT=$_POST['opt']; $PostData = $_POST['send']; 
    if(isset($OPT['debug'])) $debug=$OPT['debug']; else $debug=0; 
    
    $url=$OPT['url']; $PHPSELF=$OPT['PHPSELF'];  
    $uid=$OPT['uid']; $logged = $OPT['logged'];  $name= $OPT['info']['name'];  $admin=$OPT['admin']; $priv= $OPT['priv'];  
    if($OPT['StudentAdmin'] && $admin) { $admin = 0; $uid = $OPT['StudentAdminName'];  }
    if($name=="") $name=$uid;


    if($logged) {
	    if(!file_exists("$DATA/USERS/$uid.json") && !($_SESSION['AdminLevel']=='SuperAdmin' || $_SESSION['AdminLevel']=='DefaultUsers' )) {
		    session_destroy(); exit("Hmm! User '$uid' does not exist or you are logged out. <a href=.>Try again</a>");
	    } 
            if(isset($_POST['opt']['info']['RecordActivity'])) { $RecordActivity=$_POST['opt']['info']['RecordActivity']; 
	      if($RecordActivity != 'None' ) include("$COMMON/RecordActivity.php"); 
	    }
    }

    if($DataServer == 'Remote') {
      $allowed_domains = array("http://dynamic-learning-framework.org","http://129.108.32.227","http://108.59.87.17","http://129.108.33.160"); 
      $http_origin = $_SERVER['HTTP_ORIGIN'];
      if (in_array($http_origin,$allowed_domains)) {  header("Access-Control-Allow-Origin: $http_origin"); } else return;
      header('Access-Control-Allow-Methods: GET, POST'); //header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
      header('Access-Control-Max-Age: 1728000');
      header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization, X-Requested-With');
      header('Access-Control-Allow-Credentials: true');
    } else {
     if($logged) {if(!isset($_SESSION['logged'])) echo "<script>window.location.reload(); </script>"; }
    } 

    if($debug) {echo "<br/>POST(in main):<br/><pre>"; print_r($_POST); echo "</pre>";}
    if($OPT['EditRawFile']  && !($EditRawDisable=='Disabled')) {include("$COMMON/EditRawFile.php");  return; }

    if(isset($_POST['send']['serverinfo'])) { ServerInfo($_POST['send']['serverinfo']); return; }

    if(isset($_POST['send']['LoadPHP'])){$f="$COMMON/".$_POST['send']['LoadPHP'];if(file_exists($f))include($f);else echo "Loading $f failed"; return;}
    if(isset($_POST['send']['outputid'])) {return; }

    if($OPT['info']['initial']=='0' && $logged) {include("$COMMON/Initial.php"); }
    if($logged) echo "<span class=FloatRight>$name<button id=BLoginToggle onclick=\"toggleVK('LoginToggle')\">+</button><span id=LoginToggle style='display:none'>";
    include("$COMMON/Login.php"); 
    if($logged) echo "</span></span>";
   
    



//----------------------------------------------
/*
function pa($v) { echo '<pre>'; print_r($v); echo '</pre>'; }
function ht($s,$c='yellow',$flag=1,$t='') {if($flag) return "<span title=$t style='background-color:$c;'>$s</span>"; else return $s; }

function ServerInfo($v) { 
 if($v==1) print_r($_POST); if($v==2) print_r($_GET); if($v==3) print_r($_SESSION); if($v==4) print_r($_SERVER); if($v==5) phpinfo(); 
}

function togglePHP($s,$id, $O='+', $ret='echo',$bmsg='',$amsg='') { $str="<button id=B$id>$O</button>";
  if($O=='+') $str .= "<span id=$id style='display:none'>$s</span>";
  if($O=='-') $str .= "<span id=$id>$s</span>";
  $str = "$bmsg $str $amsg";
  if($ret == 'str') return $str; else echo $str;
echo <<<ENDB
<script>
   $('#B$id').click(function() { $('#$id').toggle(); if($('#B$id').text()=='-') $('#B$id').text('+'); else $('#B$id').text('-');});
</script>
ENDB;
}

*/

?>

