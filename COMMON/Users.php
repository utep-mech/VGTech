<?php
if(!$admin) return; 
$id='NewUserID';  $UserDIR="$DATA/USERS"; if(!is_dir($UserDIR)) mkdir($UserDIR,0777); 
if(!isset($_POST['send']['Users'])) {
 echo "<button onclick=\"dimag({'outputid':'$id','LoadPHP':'Users.php','Users':'ListUsers'})\">List</button>";
 echo "<button onclick=\"dimag({'outputid':'$id','LoadPHP':'Users.php','Users':'NewUser'})\">New User</button>";
 echo "<span id=$id></span><span id=mout-$id></span>"; 
 return; 
} 

if($_POST['send']['Users']=='NewUser') Users_CreateNewUser($UserDIR,$id);  
if($_POST['send']['Users']=='ListUsers') Users_ListUsers($UserDIR,$id); 
if($_POST['send']['Users']=='ModifyUser') Users_ModifyUser($UserDIR,$id, $_POST['send']['UserID']); 

return; 

//--------------------------------------
function Users_ListUsers($UserDIR=".", $id='NewUserID', $uid="", $pid="", $eid="", $fn="", $ln="", $filter="", $O="") { 
  $strH = "<p/>Users: <button onclick=\"dimag({'outputid':'$id','LoadPHP':'Users.php','Users':'ListUsers','Type':'Detailed'})\">Detailed</button>"; $str = ""; 
  foreach(glob("$UserDIR/*.json") as $i=>$f) { $uid=basename($f,'.json'); $strU= "<td>$uid</td>"; 
     if($_POST['send']['Type']=='Detailed') {$U=json_decode(file_get_contents("$f"),true); 
       $name=$U[$uid]['FirstName'] . ' ' . $U[$uid]['LastName']; if($U[$uid]['Privilege']=='Admin') $name="<font color=green>$name</font>"; $strU .= "<td>$name</td>";   
     }
     $strU .= "<td><button onclick=\"dimag({'outputid':'$id','LoadPHP':'Users.php','Users':'ModifyUser', 'UserID':'$uid'})\">Modify</button></td>";
     $str .= "<tr>$strU</tr>";
  }
  echo "$strH<table border=1 width=100%>$str</table>"; 
}
//--------------------------------------
function Users_ModifyUser($UserDIR=".", $id='NewUserID', $uid="", $pid="", $eid="", $fn="", $ln="", $O="") { $UserInfoA=json_decode(file_get_contents("$UserDIR/$uid.json"),true);
  if($_POST['send']['Type']=='CheckPass')  {$uid = $_POST['send']['uid']; $pass = $_POST['send']['val'];  
     $passS=$UserInfoA[$uid]['Password'] ; if (hash_equalsVK($passS, crypt($pass, $passS) )) echo "Correct"; else echo "Incorrect";  
     return; 
  }
  if($_POST['send']['Type']=='Save')  {  $U = $_POST['send']['ValClass']; $uid=$U['UserID']; $uidfile = "$UserDIR/$uid.json";  
    if($U['Password'] =="") { $U['Password']=$UserInfoA[$uid]['Password'] ; } else $U['Password'] = crypt($U['Password'] );
    $W[$uid] = $U;  file_put_contents("$uidfile",json_encode($W)); echo 'Modified'; //echo json_encode($W); 
    return; 
  } 
  $U=$UserInfoA[$uid]; $eid=$U['Email']; $ln=$U['LastName'];  $fn=$U['FirstName'];  $Priv=$U['Privilege']; $JSONString=$U['JSONString']; 

    echo "<p/>Userid*:<input id=UserID class=CM$id size=5 type=text value=$uid disabled />, Password*:<input id=Password  placeholder='Empty for no change' class=CM$id size=15 type=password />";
    $str = ""; 
    $str .="<br/> Email:<input id=Email class=CM$id value='$eid' type=text />"; 
    $str .= "<br/> LastName:<input id=LastName  value='$ln' class=CM$id size=10 type=text/>, FirstName:<input id=FirstName  value='$fn' class=CM$id size=10 type=text/>";
    $str .= "<br/> Privilege<input id=Privilege value='$Priv' size=10  class=CM$id type=text/>";
    $str .= "(<input type='radio' onclick=\"document.getElementById('Privilege').value='Admin';  \" />Admin, <input type='radio' onclick=\"document.getElementById('Privilege').value='Instructor';\" />Instructor)";
    echo "$str"; 
    echo "<br/> JSONString <textarea id=JSONString class=CM$id>$JSONString </textarea>"; 
    echo "<br/> <button onclick=\"dimag({'outputid':'mout-NewUserID','LoadPHP':'Users.php','Users':'ModifyUser', 'Type':'Save','GetValClassID':'CM$id'});\">Submit</button>";
    echo "<button onclick=\"dimag({'outputid':'mout-NewUserID','LoadPHP':'Users.php','Users':'ModifyUser', 'Type':'CheckPass','GetValID':'Password', 'uid':'$uid'});\">Check Passwd</button>";

}
//--------------------------------------
function Users_CreateNewUser($UserDIR=".", $id='NewUserID', $uid="", $pid="", $eid="", $fn="", $ln="", $O="") {

  if($_POST['send']['Type']=='Save')  { $uid=$_POST['send']['Value']['UserID']; $uidfile = "$UserDIR/$uid.json"; if(file_exists($uidfile)) {echo "$uidfile already exists";  return; }
    $U[$uid] = $_POST['send']['Value'];  $U[$uid]['Password'] = crypt($U[$uid]['Password'] ); file_put_contents("$uidfile",json_encode($U)); pa(json_encode($U)); 
    return; 
  } 
echo <<<END
<script>function NewUser(id) {
   var v={}, w={};     $(".C"+id).each(function(){  v[$(this).prop("id")]=$(this).prop("value");  }); 
   if(! ( /^[a-zA-Z0-9]+$/.test(v['UserID']) )) {alert('Userid must have letters or numbers only '); return; }
   if(v['Password']=='') {alert('Password cannot be empty'); return;}
    dimag({'middle':'mout-NewUserID','LoadPHP':'Users.php','Users':'NewUser', 'Type':'Save','Value':v}); 
    //alert(JSON.stringify(v));
}
</script>
END;
    echo "<p/>Userid*:<input id=UserID class=C$id size=10 type=text />, Password*:<input id=Password  class=C$id size=10 type=password />";
    $str = ""; 
    $str .="<br/>Email:<input id=Email class=C$id size=10 type=text />(<input type='checkbox' onclick=\"document.getElementById('UserID').value=document.getElementById('Email').value;  \" />Change Userid same as email)"; 
    $str .= "<br/> LastName:<input id=LastName  class=C$id size=10 type=text/>, FirstName:<input id=FirstName  class=C$id size=10 type=text/>";
    $str .= "<br/> Privilege<input id=Privilege size=10  class=C$id type=text/>";
    $str .= "(<input type='radio' onclick=\"document.getElementById('Privilege').value='Admin';  \" />Admin, <input type='radio' onclick=\"document.getElementById('Privilege').value='Instructor';\" />Instructor)";
    togglePHP("$str<p/>","toggle$id",'+');
    echo "<button onclick=\" NewUser('$id'); \">Submit</button>";

}
return; 
//--------------------
$conn = mysqli_connect('localhost', 'vkumar', 'Re=2300', $dbname);   if (!$conn) { die("<p/>Connection failed: " . mysqli_connect_error()); }
//^^^^^^^^^^^^^^^^^^^^^
if($_POST['send']['Users']=='CheckPass') { $UserID=$_POST['send']['UserID']; 
 if($result = mysqli_query($conn,"SELECT  Password FROM $Table WHERE UserID='$UserID'")) { $row = SQLR2A($result); } else die("<p/>Error: " . mysqli_connect_error());
 if (CheckLoginStatus($_POST['send']['val'], $row[0]['Password'])) {BGColor($_POST['send']['outputid'],'Valid','green'); } else {    BGColor($_POST['send']['outputid'],'Invalid','red'); }
} elseif($_POST['send']['Users']=='ChangePass') { 
 $UserID=$_POST['send']['UserID']; $pwd = $_POST['send']['val']; 
 if (strlen($pwd) < 6) { echo "pwd must be  >5 char"; return; }
 if(mysqli_query($conn,sprintf("UPDATE $Table SET Password='%s' WHERE UserID='%s'",crypt($pwd),$UserID))) echo 'Updated'; else echo 'Error'; 
} elseif($_POST['send']['Users']=='AddCol') {   $Name=$_POST['send']['Value'];    if($Name=='Name') {echo 'Change Name'; return; }
    if(mysqli_query($conn,sprintf("ALTER TABLE $Table ADD $Name %s;", $_POST['send']['Extra'] )))  echo 'Updated'; else echo 'Error';  
} elseif($_POST['send']['Users']=='DelCol') {  
  if(mysqli_query($conn,sprintf("ALTER TABLE $Table DROP %s ;", $_POST['send']['Value'])))  echo 'Updated'; else echo 'Error';  
} elseif($_POST['send']['Users']=='NewTable') {  $q = $_POST['send']['val']; if(!($uid == 'vkumar')) die('not allowed');  
  if($result = mysqli_query($conn,"$q")) { echo "Created New Table"; } else die("<p/>Error: " . mysqli_connect_error());
} elseif($_POST['send']['Users']=='SetTable') {  $Table=$_POST['send']['Table']; echo "<script> opt.info.Table = '$Table'; </script>"; 
} elseif($_POST['send']['Users']=='ListTables') {  $q = "SHOW TABLES;"; 
  if($result = mysqli_query($conn,$q)) { 
    foreach(SQLR2A($result) as $k0=>$v0) foreach($v0 as $k=>$v) {
       if($Table=="$v") echo $v; else echo "<button onclick=\"dimag({'outputid':'UsersID','LoadPHP':'Users.php','Users':'SetTable', 'Table':'$v'})\">$v</button>";
    }
  } else die("<p/>Error: " . mysqli_connect_error());
} elseif($_POST['send']['Users']=='AddRow') {    
  if($_POST['send']['Value']['UserID']=='UserID') {echo 'Error: Enter different UserID'; return; }
  $i=0; $SkipKeys = array('id', 'Reg_Date');  $UniqID=uniqid();  $_POST['send']['Value']['UniqID'] = $UniqID; 
  if($_POST['send']['Value']['Password']=='') $_POST['send']['Value']['Password'] = $UniqID;
  foreach($_POST['send']['Value'] as $k=>$v) { if(in_array($k,$SkipKeys)) continue; 
      if($i=='0') {$Ks = "$k"; $Vs = "'$v'"; } else {$Ks .= ", $k"; $Vs .= ", '$v'";}
      $i++; 
  }
   //echo "INSERT INTO $Table ($Ks) VALUES ($Vs)"; 
   if(mysqli_query($conn,"INSERT INTO $Table ($Ks) VALUES ($Vs)"))  echo 'Updated'; else echo 'Error' . mysqli_connect_error();  
} elseif($_POST['send']['Users']=='DelRow') { $UniqID = $_POST['send']['Value']; 
  if(mysqli_query($conn,"DELETE FROM $Table WHERE UniqID='$UniqID'"))  echo 'Updated'; else echo 'Error';  
}
//--------------------
if($_POST['send']['Users']=='List') {
  SortableTable(); 
  if($result = mysqli_query($conn,"SELECT * FROM $Table")) { A2Table(SQLR2A($result)); } else die("<p/>Error: " . mysqli_connect_error()); 
}

//--------------------------------
mysqli_close($conn); 
return;
//^^^^^^^^^^^^^^^^^^^^^
function SQLQRunVK($q,$dbname, $flag='list') { global $uid; 
  $conn = mysqli_connect('localhost', 'vkumar', 'Re=2300', $dbname);   if (!$conn) { die("<p/>Connection failed: " . mysqli_connect_error()); }
  if($result = mysqli_query($conn,$q)) { echo 'Successful'; if($flag=='list') pa(SQLR2A($result)); } else die("<p/>Error: " . mysqli_connect_error());
  mysqli_close($conn); 
  return;
}

//MySQLTablePHP2($sql); 
//MySQLTablePHP2("SHOW TABLES", "USERS", "show"); 

$i=0; 
foreach(array('UniqID'=>$UniqID,'UserID'=>'test3','FirstName'=>'FT','LastName'=>'LT') as $k=>$v) { 
  if($i=='0') {$Ks = "$k"; $Vs = "'$v'"; } else {$Ks .= ", $k"; $Vs .= ", '$v'";}
  $i++; 
}

//MySQLTablePHP2("INSERT INTO $Table (UniqID,UserID,FirstName,LastName) VALUES ('$UniqID','test1','FT','LT')", "USERS", "show"); 
MySQLTablePHP2("INSERT INTO $Table ($Ks) VALUES ($Vs)", "USERS", "show"); 
//MySQLTablePHP2("DELETE FROM $Table WHERE UniqID='55a57f7f4afb9'", "USERS"); 

//MySQLTablePHP2("SELECT * FROM $Table", "USERS", "showtable"); 
//MySQLTablePHP2("SELECT UniqID,UserID,FirstName FROM $Table", "USERS", "showtable"); 

//MySQLTablePHP2("SHOW columns FROM $Table", "USERS", "show"); 
//MySQLTablePHP2("SHOW databases", "USERS", "show"); 

//-----------
function CheckLoginStatus($Password, $Pass0) {if (hash_equalsVK($Pass0, crypt($Password, $Pass0) ))  return 1;  else  return 0; } 
function ChangePass($Password, $Pass0) {if (hash_equalsVK($Pass0, crypt($Password, $Pass0) ))  return 1;  else  return 0; } 
function TestValuesSQL($Value, $Table, $dbname="USERS", $flag='') { $logged=0; 
  $servername = "localhost"; $username = "vkumar"; $password = "Re=2300";
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  if (!$conn) { die("<p/>Connection failed: " . mysqli_connect_error()); }
  $q = "ALTER TABLE `$Table` ADD COLUMN Password VARCHAR(255)"; mysqli_query($conn,$q); 


$hashed_password = crypt('hello'); $upass='hello'; $UserID = 'test3'; 
//mysqli_query($conn,"UPDATE $Table SET Password='$hashed_password' WHERE UserID='$UserID'");

if($result = mysqli_query($conn,"SELECT  Password FROM $Table WHERE UserID='$UserID'")) {   $row = mysqli_fetch_assoc($result); 
  if(isset($row['Password'])) { $hashed_password = $row['Password']; if (hash_equalsVK($hashed_password, crypt($upass, $hashed_password) )) {   $logged=1;  }   } 
}
if ($logged) {   echo '<p/>Password is valid!'; } else {    echo 'Invalid password.';}
//$result = mysqli_query($conn,"SELECT COUNT(*) $Table WHERE LastName='$data2' AND UniqID='55a58341e72a7'"); 
//if($result) echo '<h1>Logged</h1>'; else '<h1>failed</h1>'; 
  mysqli_close($conn);
}


function ModifyValuesSQL($Value, $Table, $dbname="USERS", $flag='') {
  $servername = "localhost"; $username = "vkumar"; $password = "Re=2300";
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  if (!$conn) { die("<p/>Connection failed: " . mysqli_connect_error()); }
  $Skip=array('UniqID','UserID');  
  for($i=0; $i<sizeof($Value); $i++) {$UID=$Value[$i]['UniqID']; 
   foreach($Value[$i] as $k=>$v) if(!in_array($k,$Skip)) mysqli_query($conn,"UPDATE $Table SET $k='$v' WHERE UniqID='$UID'");
  }
  mysqli_close($conn);
}

//-------------------------
function MySQLTablePHP2($sql, $dbname="USERS", $flag='') { 
  $servername = "localhost"; $username = "vkumar"; $password = "Re=2300";
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  if (!$conn) { die("<p/>Connection failed: " . mysqli_connect_error()); }
  if ($result = mysqli_query($conn, $sql)) {
      if(in_array($flag,array('show','showtable'))) { 
         while ($row = mysqli_fetch_assoc($result)) {if(!isset($TA)) {$TA[0]=$row; } else {$TA[]=$row; } }
      } 
      if($flag=='showtable') A2Table($TA); else pa($TA);
      mysqli_free_result($result);
  }
  if ($result) { echo "<p/>successfully"; } else { echo "<p/>Error:" . mysqli_error($conn); }
  mysqli_close($conn);
}
//-------------------------
//function hash_equalsVK($a, $b) {$ret = strlen($a) ^ strlen($b);      $ret |= array_sum(unpack("C*", $a^$b));  return !$ret;     }
//function SQLR2A($result) {while ($row = mysqli_fetch_assoc($result)) {if(!isset($TA)) {$TA[0]=$row; } else {$TA[]=$row; } }; return $TA;     }
//-------------------------
function A2Table($A, $tid="TableinUsers"){
   $NotEditable = array('id', 'UniqID', 'UserID', 'Password','Reg_Date');  
   $Addable= array('UserID', 'FirstName', 'LastName', 'Email',  'COURSES', 'Password'); 
   $ProtectedKeys= array('id', 'UniqID', 'UserID', 'Password'); 
  $n = sizeof($A); $m = sizeof($A[0]); 
  $A[] = $A[0];  foreach($A[0] as $k=>$v) {if(in_array($k,$Addable)) $A[$n][$k]="$k"; else $A[$n][$k]=""; }
  $vStr = '';  $nrows=sizeof($A)+1; $ncols=sizeof($A[0])+1;  
 
 foreach($A as $k0=>$v0) { $ip1=($k0+1); $j=0; $UserID = $v0['UserID']; $LastRow=''; $UniqID= $v0['UniqID'];
   if($k0==$n) { $s =  "<td><button onclick=\"TableManagement('$tid','$nrows','$ncols','AddRow', {})\">Save</button></td>";
   } else { 
     $s = "<td id=$tid-$ip1-0>$ip1 <button class=EditButtons style='display:none;' onclick=\"TableManagement('$tid','$nrows','$ncols','DelRow', {'UniqID':'$UniqID'})\">x</button> </td>"; 
   } 
   if($k0=='0') {$hStr  = "<th id=$tid-0-0><button id='B-.EditButtons' onclick=\"toggleVK2('.EditButtons',this)\">+</button></th>";  }
   foreach($v0 as $k=>$v) { $j++; $w=''; $id="$tid-$ip1-$j"; if(!in_array($k,$NotEditable)) $Class='Editable'; else $Class='None';  if($k0 == ($nrows -2) && $k=="UserID") $Class='Editable'; 
       if($k=='Password') {  $w.= "<input type=password id=$tid-$ip1-$j size=3 value='***'></input>";  
        $w.= "<button id='check$id' onclick=\"dimag({'outputid':'check$id','LoadPHP':'Users.php','Users':'CheckPass', 'GetValID':'$id', 'UserID':'$UserID'})\">Check</button>"; 
        $w.= "<button id='change$id' onclick=\"dimag({'outputid':'change$id','LoadPHP':'Users.php','Users':'ChangePass', 'GetValID':'$id', 'UserID':'$UserID'})\">Change</button>"; 
         $s .= "<td>$w</td>"; 
       } else { 
          $s .= "<td><span id=$tid-$ip1-$j class=$Class>$v</span>$w</td>";  
       }
       if($k0=='0') { if(!in_array($k,$ProtectedKeys)) $b = "<button class=EditButtons style='display:none;' onclick=\"TableManagement('$tid','$nrows','$ncols','DelCol',{'Name':'$k'})\">x</button>"; else $b=''; 
           $hStr .= "<th sortable=true><span  id=$tid-0-$j>$k</span>  $b </th>";  
       }
   } 
   if($k0=="$n") $vStr .= "<tr class=EditButtons style='display:none;background-color:lightgray;' id=$tid-$k0 >$s</tr>";  else $vStr .= "<tr>$s</tr>"; 
 }
$b=  "<button onclick=\"TableManagement('$tid','$nrows','$ncols','AddCol', {})\">Save</button>"; 
$b .= togglePHP("<br/><input type=text id=AddColMore value='VARCHAR(255)' /> ",'AddColB','+','str'); 
 $xCol = "<th rowspan=$nrows valign=top class=EditButtons style='display:none;background-color:lightgray;' class=AddCol><span id=AddColName Class=Editable>Name</span><br/> $b </th>"; 
 echo "<table border=1 id=$tid nrows=$nrows ncols=$ncols> <tr>$hStr $xCol </tr> $vStr </table>"; 
 echo "<button class=EditButtons style='display:none;' onclick=\"TableManagement('$tid','$nrows','$ncols', 'SaveTableAll',{})\">Save</button>";
 //echo "<button onclick=\"TableManagement('$tid','$nrows','$ncols','AddRow',{})\">Add</button>";
 //echo "<button onclick=\"ShowBy('$tid-$n','id')\">Row+</button><button onclick=\"ShowBy('AddCol','c')\">Col+</button>";
 echo "<button class=EditButtons style='display:none;' onclick=\"Button2Edit('.Editable','')\">Edit</button>";

 
echo <<<END
  <script> 
  function Button2Edit(i,f){ if(!($(i).attr('contenteditable')=='true')) $(i).attr('contenteditable',true); else $(i).attr('contenteditable',false); }

  function TableManagement(tid,nrows,ncols,flag,o) { 
     if(flag=='AddCol') { dimag({'outputid':'UsersID','LoadPHP':'Users.php','Users':flag, 'Value': $("#AddColName").text(), 'Extra': $("#AddColMore").val() });  return;  }
     if(flag=='DelCol') { dimag({'outputid':'UsersID','LoadPHP':'Users.php','Users':flag, 'Value': o.Name});  return;  }
     if(flag=='AddRow') { var v={}, k; 
          for(j=1; j<ncols; j++) { k=$("#"+tid+'-'+ 0 + '-'+ j).text(); v[k] = $("#"+tid+'-'+(nrows-1)+ '-'+ j).html();} 
          dimag({'outputid':'UsersID','LoadPHP':'Users.php','Users':flag, 'Value': v});  
     return;  }
     if(flag=='DelRow') { dimag({'outputid':'UsersID','LoadPHP':'Users.php','Users':flag, 'Value': o.UniqID});  return;  }
     var k='',  i1=0, j1=0, i2=nrows, j2=ncols; 
     if(flag=='AddRow') { var v={};  for(j=1; j<j2; j++){ k=$("#"+tid+'-'+ 0 + '-'+ j).text(); v[k] = $("#"+tid+'-'+nrows+ '-'+ j).html();       }   
    } else { var  v=[]; 
     for(i=i1; i<i2; i++){ v[i]={}; 
      for(j=j1+1; j<j2; j++){ k=$("#"+tid+'-'+ 0 + '-'+ j).text(); v[i][k] = $("#"+tid+'-'+ i + '-'+ j).html();       }
     }
    }
     //$('#tax').val(JSON.stringify(v));
    dimag({'outputid':'UsersID','LoadPHP':'Users.php','Users':flag, 'Value':v});
  }
  </script>
END;
} 
function BGColor($id,$s,$c='red'){ echo "$s"; 
echo <<<END
  <script> 
   $('#$id').css('background-color','$c');
  </script>
END;
}
function QueryGenerator($Table,$flag='Table'){
  if($flag=='Table') {
         $q = "CREATE TABLE $Table ( 
                 id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                 UniqID varchar(255) NOT NULL UNIQUE, 
                 UserID varchar(255) NOT NULL UNIQUE, 
                 FirstName VARCHAR(255), 
                 LastName VARCHAR(255) NOT NULL, 
                 Email VARCHAR(255) UNIQUE,
                 Priv VARCHAR(50), 
                 Password VARCHAR(255), 
                 COURSES TEXT, 
                 Reg_Date TIMESTAMP
           ); ";
  }
  return $q; 
}
?> 
