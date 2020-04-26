<?php
require_once("$COMMON/CLASSES/USERS.php");

$db='USERS'; $collection='users'; $dbTable="$db.$collection"; $col=$collection; 
$PD=$_POST['send'];  

$Udb = new \IO\MongoDB([url=>$url, db=>$db, col=>$col, LoadPHP=>$LoadPHP, outputid=>$outputid, admin=>$admin, uid=>$userid]);

$uOBJ = new \USERS\Main(['LoadPHP'=>$LoadPHP,'outputid'=>'tableM']); 
$manager = new MongoDB\Driver\Manager('mongodb://localhost:27017');
$MDB = new \IO\MongoDB($manager, ['LoadPHP'=>$LoadPHP, 'db'=>$db, 'collection'=>$collection]); 
$bulk2 = new MongoDB\Driver\BulkWrite();

if(isset($PD['List']))  { $uqid=uniqid();
    //$Udb->List2($Udb->ExecCMD([find=>'users'])); 
    $ii=''; $DelB = '&#10007;'; $EditB='&#9998;';
    $s = '<tr><td>MISC</td><td>Password</td><td>LastN,FN</td><td>Email</td></tr>'; 
    foreach($Udb->ExecCMD([find=>'users']) as $k=>$r) { $tmpoutid=$uqid."$k"; //\IO\pa($r);
      $id = (string)$r->_id; $emailid = $r->Email; 
      $UserID=$r->UserID; $PW=$r->Password; $Fn=$r->FirstName; $Ln=$r->LastName;
      $del = "<button onclick=\"dimag({'outputid':'$tmpoutid', 'LoadPHP':'$LoadPHP', db:'$db', 'col':'$col', 'DelID':'$id'});\">$DelB</button>";
      $edit = "<button onclick=\"dimag({outputid:'$outputid', LoadPHP:'$LoadPHP', db:'$db', Update:{find:'$col',filter:{UserID:'$UserID'}} });\">$EditB</button>";
      $raw=\IO\toggle("raw$k$uqid", '<br/>'.json_encode($r).'<br/>'); $tmpoutput="<span id='$tmpoutid'></span>"; 
      $s .= "<tr><td>$k $del $edit $raw $UserID</td><td>Update</td><td>$Ln, $Fn</td><td>$emailid $tmpoutput</td></tr>";
    }
    echo "<table border=1>".$s."</table>"; 
    return; 
}
//if(isset($PD['List']))  { $MDB->List(new MongoDB\Driver\Query([],[])); return; }

if(isset($PD['DelID'])) {$MDB->Delete($bulk2,['_id'=>new MongoDB\BSON\ObjectID($PD['DelID'])]); return; }
if(isset($PD['UpdateDB'])) { $UserID=$PD['UpdateDB'];
 foreach(['groups','COURSES','Extra'] as $v) if(!isset($PD['UserInfo'][$v])) $PD['UserInfo'][$v]=[]; 
 if(in_array("EncryptPassword", $PD['UserInfo']['Extra']) && isset($PD['UserInfo']['Password'])) {
     $PD['UserInfo']['Password'] = crypt($PD['UserInfo']['Password']); 
 }
   $Udb->ExecCMD([update=>$col, updates=>[ [q=>[UserID=>$UserID], u=>['$set'=>$PD['UserInfo'] ],upsert=>true] ] ]);
 //$MDB->Update($bulk2, ['Email' => $PD['UpdateDB'] ], $PD['UserInfo']); 
 echo "$UserID info updated"; 
 return; 
}

//-----------
/*
$iwrite=0; 
foreach(json_decode(\Defaults\DefaultUsers(), true) as $k=>$v) {
    if(!isset($v['UserID'])) $v['UserID']=$k; 
    if(isset($v['Email'])) $q=['Email'=>$v['Email']]; else $q=['UserID'=>$v['UserID']];
    if(!count($manager->executeQuery($dbTable,new MongoDB\Driver\Query($q,[]))->toArray()) ) 
       { $bulk2->insert($v); $iwrite=1;}
}
if($iwrite) $result = $manager->executeBulkWrite("$db.$collection", $bulk2);
*/
//--------------

if(isset($PD['NewUsersDB'])) { $U=$PD['UserInfo']; $Email=$U['Email']; $UserID=$Email; $U['UserID']=$UserID;
    if(sizeof($Udb->ExecCMD([find=>$col, filter=>[Email=>$Email] ]))>0) { 
        echo "<span style='background-color:orange'>Error! $Email already exists </span>"; 
        return; 
    } 
    if(in_array('EncryptPassword', $U['Extra']) ) $U['Password'] = crypt($U['Password']); 
    $Udb->ExecCMD([update=>$col, updates=>[ [q=>[UserID=>$UserID], u=>['$set'=>$U ],upsert=>true] ] ]);
    echo "$UserID created!"; 
     return; 
}
//--------------
if(isset($PD['NewUsers'])) { echo $uOBJ->NewUser();  return; }
if(isset($PD['Update'])) { $q = $PD['Update']; $q['projection']=['_id' => 0]; 
  //$q = new MongoDB\Driver\Query(['UserID'=>$PD['Update']],['projection' => ['_id' => 0]]); 
    echo $uOBJ->Update(json_encode( $Udb->ExecCMD($q)[0] )); //$uOBJ->Update(json_encode($MDB->queryOne( $q )));
  return; 
}

//echo $s; 
echo "<button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'NewUsers':1,'return':1});\">Add Users</button>";
echo "<button onclick=\"dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'List':1});\">List</button>";

//-------------------------
return;

//$bulk2->insert( json_decode('{"x":2,"y":{"a":3,"b":5}}'));
$bulk2->update(['_id' =>new MongoDB\BSON\ObjectID('5b5f8c3ddb3ffa3ab85c03b2')], ['$set' => ['x' => 12]] );
$bulk2->update(['_id' =>new MongoDB\BSON\ObjectID('5b5f8ebadb3ffa3ab62800b3')], ['$set' => ['x.xx.yyy' => 5]] );
$bulk2->delete(['_id' =>new MongoDB\BSON\ObjectID('5b5f7a95e995380b324d273d')], ['limit' => 1]);
if(isset($_GET['DelID'])) $bulk2->delete(['_id' =>new MongoDB\BSON\ObjectID($_GET['DelID'])]);
$bulk2->insert( json_decode('{"x":2,"y":{"a":3,"b":5}}'));
$result = $manager->executeBulkWrite("$db.$collection", $bulk2);

$filter = ['Email'=>'v@utep.ea']; $filter = [];
$options = [
   'projection' => ['_id' => 1,'y.a'=>1],
];
$options = [];
$query = new MongoDB\Driver\Query($filter,$options);
$MDB->list(new MongoDB\Driver\Query([],[])); 


//----------------

?>
