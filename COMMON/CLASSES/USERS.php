<?php
namespace USERS {
  class Main {      
      public $table = 'Menu', $outputid='tableM';
      public function __construct($O=array()) {
            foreach($O as $k=>$v) $this->$k = $v; 
            $this->id = uniqid();
      }
      public function NewUser($O=array()) { 
          $UserJSON = \Defaults\UserJSON(); $LoadPHP = $this->LoadPHP;  
          $classid = 'Class'.$this->id;
          $dA=json_decode($UserJSON,true); $s = '<br/>'; unset($dA['UserID']); 
          foreach($dA as $k=>$v) {
              if($k=="Password") { $type='Password'; } else $type='text';
              if(!is_array($v)) { 
                  $s .= "$k <input type='$type' class='$classid' name='$k' value='$v' $disabled /> <br/>";
              } else {
                  //if($k=='groups' || $k == 'COURSES') { 
                      $type='checkbox'; $s .= "<br/>$k ";
                  foreach($v as $kk=>$vv) $s .= " | <input  type='$type' class='$classid' name='$k' value='$vv' /> $vv";
                  //}
              }
          }
          $s .= <<<END
  <br/><button onclick="
     var u = {};
    \$('.$classid').each(function(i, obj) {
       var n = \$(this).attr('name'), t = \$(this).attr('type');
       if(t=='checkbox') {
         if( \$(this).prop('checked') ) { if( ! \$.isArray(u[n])) u[n]=[];  u[n].push( \$(this).attr('value') );  }
       } else { v = \$(this).val(); u[n]=v; }
    });
    var re = /\S+@\S+\.\S+/;
    if( !re.test(u.Email) ) { alert('Enter valid email'); return; }
    dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'NewUsersDB':1, 'return':1, 'UserInfo':u});
   ">Submit</button>
END;
          
          return $s;
      }
      //----------------------
      public function Update($UserJSON,  $O=array()) { 
          $outputid=$this-outputid; $LoadPHP=$this->LoadPHP; $outputid=$this->outputid; 
          $id = $this->id; 
          $dAD=json_decode(\Defaults\UserJSON(),true);  // \IO\pa($dAD);
          $classid = 'Class'.$this->id;
          $dA=json_decode($UserJSON,true); $s = '<br/>'; $UserID=$dA['UserID'];
          foreach($dAD as $k=>$v) {
              if($k=="Password") {$type='Password'; $pass=$dA[$k]; } else $type='text';
              if(!is_array($v)) { $w = $dA[$k]; 
              if($k=='Email' || $k=='UserID') $disabled= "disabled"; else $disabled='';
              $s .= "$k <input type='$type' class='$classid' name='$k' value='$w' $disabled /> <br/>";
              } else {
                  //if($k=='groups' || $k == 'COURSES') { 
                      $type='checkbox'; $s .= "<br/>$k ";
                  foreach($v as $kk=>$vv) { if(in_array($vv,$dA[$k])) $checked='checked'; else $checked='';
                  $s .= " | <input  type='$type' class='$classid' name='$k' value='$vv' $checked /> $vv";
                  //}
                  }
              }
          }
          
          $s .= <<<END
     <br/><button onclick="
     var u = {}, email, store=1;
    \$('.$classid').each(function(i, obj) {
       var n = \$(this).attr('name'), t = \$(this).attr('type');
       if(t=='checkbox') {  
         if( \$(this).prop('checked') ) {if(!\$.isArray(u[n])) u[n]=[]; u[n].push( \$(this).attr('value') );  }
       } else { 
        v = \$(this).val(); store=1;
        if(n=='Email') { email=v; store=0; }
        if(n=='Password') {store=0; if(v != '$pass') store=1; }
        if(store) u[n]=v;
       }
    });
    //alert(JSON.stringify(u));
    dimag({'outputid':'$id-b', 'LoadPHP':'$LoadPHP', 'UpdateDB':'$UserID', 'UserInfo':u});
   ">Submit</button>
END;
       return "<div id='$id-b' style='border-style: solid;'>".$s."</div>";
      } 
      
  }
  //------------------
  //----------------
  function Update($UserJSON, $LoadPHP, $O=array()) { // DELETE
      
      if(isset($O['outputid'])) $outputid=$O['outputid']; else $outputid='tableM';
      
      if(isset($O['default'])) $default=$O['default']; else $default =  \Defaults\UserJSON(); 
      $dAD=json_decode($default,true);  // \IO\pa($dAD);
      
      $classid = 'Class'.uniqid();
      $dA=json_decode($UserJSON,true); $s = '<br/>'; 
      foreach($dAD as $k=>$v) { 
          if($k=="Password") {$type='Password'; $pass=$dA[$k]; } else $type='text';
      if(!is_array($v)) { $w = $dA[$k];
      if($k=='Email') $disabled= "disabled"; else $disabled='';
      $s .= "$k <input type='$type' class='$classid' name='$k' value='$w' $disabled /> <br/>";
      } else {
          if($k=='groups' || $k == 'COURSES') { $type='checkbox'; $s .= "<br/>$k ";
          foreach($v as $kk=>$vv) { if(in_array($vv,$dA[$k])) $checked='checked'; else $checked='';
             $s .= " | <input  type='$type' class='$classid' name='$k' value='$vv' $checked /> $vv";
          }
          }
      }
      }
      
      $s .= <<<END
    <button onclick="
     var u = {}, email, store=1;
    \$('.$classid').each(function(i, obj) {
       var n = \$(this).attr('name'), t = \$(this).attr('type');
       if(t=='checkbox') {
         if( \$(this).prop('checked') ) { if( ! \$.isArray(u[n])) u[n]=[];  u[n].push( \$(this).attr('value') );  }
       } else { v = \$(this).val(); store=1; 
        if(n=='Email') { email=v; store=0; } 
        if(n=='Password') {store=0; if(v != '$pass') store=1; } 
        if(store) u[n]=v;     
       }
    });
alert(JSON.stringify(u));
    dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'Update':email, 'return':1, 'UserInfo':u});
   ">Submit</button>
END;
      
      return $s;
  } 
  
  //----------------
  function Edit($UserJSON, $LoadPHP, $O=array()) { // DELETE - OBSELETE
      
      if(isset($O['outputid'])) $outputid=$O['outputid']; else $outputid='tableM';
      if(isset($O['Update'])) $Update=$O['Update']; else $Update=0;
      
      $classid = 'Class'.uniqid();
      $dA=json_decode($UserJSON,true); $s = '<br/>';
      foreach($dA as $k=>$v) {
          if($k=="Password") { $type='Password'; } else $type='text';
          if(!is_array($v)) { if($Update && $k=='Email') $disabled= "disabled"; else $disabled='';
          $s .= "$k <input type='$type' class='$classid' name='$k' value='$v' $disabled /> <br/>";
          } else {
              if($k=='groups' || $k == 'COURSES') { $type='checkbox'; $s .= "<br/>$k ";
              foreach($v as $kk=>$vv) $s .= " | <input  type='$type' class='$classid' name='$k' value='$vv' /> $vv";
              }
          }
      }
      
      
      $s .= <<<END
  <br/><button onclick="
     var u = {};
    \$('.$classid').each(function(i, obj) {
       var n = \$(this).attr('name'), t = \$(this).attr('type');
       if(t=='checkbox') {
         if( \$(this).prop('checked') ) { if( ! \$.isArray(u[n])) u[n]=[];  u[n].push( \$(this).attr('value') );  }
       } else { v = \$(this).val(); u[n]=v; }
    });
    var re = /\S+@\S+\.\S+/;
    if( !re.test(u.Email) ) { alert('Enter valid email'); return; }
    dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'AddUsers':1, 'return':1, 'UserInfo':u});
   ">Submit</button>
END;
      
      return $s;
  }
  
  //-------------------
  function LoginPHP($DATA,$debug,$PHPSELF,$logged,&$msg, $MDB='') {  
  $logged = 0;  $output=''; $msg = 'Unsuccessful login attempt';
  $UserDIR=$DATA."/USERS"; 
  //---------Default Users---------------
  $USERSTR='{
     "vkumar":{"UserID":"vkumar","Password":"$1$p4SqwFEe$q8zATOjXI7nfBwxmlwU6//","LastName":"Kumar","FirstName":"V.","group":"superadmin"},
     "vkumar1":{"Password":"123","name":"V. Kumar1","group":"instructor"},
     "skm":{"Password":"Chandi#123","name":"S. Mehra","group":"admin"},
     "rprem":{"Password":"Chandi#123","name":"Randheer","group":"admin"},
     "sandeepkumarmehra123@gmail.com":{"Password":"Chandi#123","name":"S. Mehra","group":"admin"},
     "rprem31@gmail.com":{"Password":"Chandi#123","name":"Randheer","group":"admin"},
     "vkumar2":{"Password":"123","name":"V. Kumar2","group":"EA2"}
  }';
  $USERS=json_decode($USERSTR,true);  
  $userid=$_POST['send']['userid'];  $passwd=$_POST['send']['passwd'];
  
  if(!isset($USERS[$userid])) { $AdminLevel = 'Basic';
   if(sizeof($MDB->ExecCMD([find=>$MDB->col, filter=>[UserID=>$userid],projection=>[UserID=>1] ]))>0) { $AdminLevel='RegisteredUsers'; //Check db
   $USERS[$userid]=(array) $MDB->ExecCMD([find=>$MDB->col, filter=>[UserID=>$userid],projection=>[_id=>0] ])[0];
   //} elseif(file_exists("$UserDIR/$userid.json")) { $AdminLevel='RegisteredUsers';
    // $USERS=json_decode(file_get_contents("$UserDIR/$userid.json"),true);
   } else { $msg="Userid '$userid' doesn't exists! $retry"; return; }
  }
  
  $passS =  $USERS[$userid]["Password"];  $Type =  $USERS[$userid]["Type"];
  if (!(hash_equals($passS, crypt($passwd, $passS)) || $passwd == $passS)) {
      $msg = "Enter correct password or contact the instructor $retry";
      return;
  }
  
  // else password and userid is correct. Set the session with correct information
  $name=$USERS[$userid]['FirstName']. " " . $USERS[$userid]['LastName'];
  if($USERS[$userid]['FirstName']=="" && $USERS[$userid]['LastName']=="") $name=$userid;
  if(isset($USERS[$userid]['name'])) $name=$USERS[$userid]['name'];
  ini_set('date.timezone', 'America/Denver');
  $time=$_SERVER['REQUEST_TIME']; $timeReadable =  date('Y-m-d H:i:s',$time);
  if(isset($USERS[$userid]['group'])) $group = $USERS[$userid]['group']; else $group=$userid;
  $logged = "userid"; $msg = "Contratulations $name! Logined at $timeReadable ";
  echo "
  <script>
    var bd = \$('#bodymain').data(); bd.logged = 1; bd.userid = '$userid'; bd.group = '$group';   bd.since= '$time';
    localStorage.setItem('logged',1);  localStorage.setItem('uid','$userid');
    localStorage.setItem('name','$name');  localStorage.setItem('group','$group');
    localStorage.setItem('since','$time');
    setTimeout(function(){window.location.reload();}, 1000);
  </script>
";
  
  }
} //Namespace ends here

?>
