<?php
namespace IO {
  class MongoDB {    // future IOdb    
      public $db='Grade06', $collection = 'Menu', $url='mongodb://localhost:27017'; 
      public function __construct($O=[]) {
            foreach($O as $k=>$v) $this->$k = $v; 
            $this->manager= new \MongoDB\Driver\Manager($this->url);; 
            $this->id = uniqid();
      }
      public function query($q) {return $this->manager->executeQuery($this->db.".".$this->collection, $q);}
      public function queryOne($q) {
          foreach($this->query($q) as $k=>$r) $rOne=$r; 
          return $rOne; 
      }
      
      public function List($q) {
          $LoadPHP=$this->LoadPHP; $db=$this->db; $col = $this->collection;
          $ii=''; $DelB = '&#10007;'; $EditB='&#9998;';
          foreach($this->query($q) as $k=>$r) { $tmpoutid=$this->id."$k"; 
              $id = (string)$r->_id; $emailid = $r->Email;
            $del = "<button onclick=\"dimag({'outputid':'$tmpoutid', 'LoadPHP':'$LoadPHP', 'db'=>'$db', 'col'=>'$col', 'DelID':'$id'});\">$DelB</button>";
            $edit = "<button onclick=\"dimag({'outputid':'$tmpoutid', 'LoadPHP':'$LoadPHP', 'db'=>'$db', 'col'=>'$col', 'Update':'$emailid'});\">$EditB</button>";
            echo "<br/>$del $edit ".json_encode($r)."<span id='$tmpoutid'></span><br/";
          }
      }
      //--------------------------------------------------
      public function Delete($bulk, $cond) { $db=$this->db; $collection = $this->collection; echo 'Deleted';
          $bulk->delete($cond); $result = $this->manager->executeBulkWrite("$db.$collection", $bulk);
      }
      public function Update($bulk, $check, $value) { $db=$this->db; $collection = $this->collection; echo 'Updated';
         $bulk->update($check, ['$set' =>  $value]);  $result = $this->manager->executeBulkWrite("$db.$collection", $bulk);
      }
      public function Insert($bulk, $value, $O=[]) { $db=$this->db; $collection = $this->collection; echo 'Inserted';
         $bulk->insert($value); $result = $this->manager->executeBulkWrite("$db.$collection", $bulk);
      }
      //--------------------------------------------------
      public function Delete2($q, $O=[]) { $qq=$q['deletes'][0]['q']; $q['deletes'][1] = $q['deletes'][0];
          $q['deletes'][1]['q']['_id'] =  new \MongoDB\BSON\ObjectID($qq['_id']);
          return $this->manager->executeCommand($this->db, new \MongoDB\Driver\Command($q));
      }
      
      public function Update2($q, $O=[]) {        
        $q['updates'][0]['upsert'] = ($q['updates'][0]['upsert'] == 'true') ? true : false;
        return $this->manager->executeCommand($this->db, new \MongoDB\Driver\Command($q));
      
      }
      public function Insert2($q, $O=[]) { 
        foreach($q['documents'] as $k=>$v) $q['documents'][$k]['_id'] = (String)(new \MongoDB\BSON\ObjectId());
        return $this->manager->executeCommand($this->db, new \MongoDB\Driver\Command($q));
      }
      //------------------------------------------------
      public function ListCollections($O=[]) {
          $LoadPHP=$this->LoadPHP; $db=$this->db; $col = $this->collection; $uqid=uniqid(); $outputid=$this->outputid; $s = ''; 
          foreach($this->getCol(['listCollections'=>1] ) as $k=>$v) {
             $s .= "<br/><button onclick=\"dimag({outputid:'tableL', LoadPHP:'$LoadPHP', db:'$db', col:'$v', a:{q:{find: '$v'}} }); \">$v</button>";
             $s .= "<button onclick=\" dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'db':'$db', 'q':{'drop':'$v'}}); \">X</button>";
          }
          return $s;
      }
      
      
      public function ExecCMD($c, $O=[]) {return ($this->manager->executeCommand($this->db, new \MongoDB\Driver\Command($c) ))->toArray(); }
      public function CMD2A($c, $O=[]) { 
          $A=($this->manager->executeCommand($this->db, new \MongoDB\Driver\Command($c) ))->toArray();
          return json_decode(json_encode($A),true); 
      }
      public function getCol($c) {$A=[];foreach($this->ExecCMD($c) as $k=>$v){$A[] = $v->name; } return $A;}
      public function getIDs($c) {$A=[];foreach($this->ExecCMD($c) as $k=>$v){if(isset($v->id))$A[]=$v->id;} return $A;}
      public function getIDAttr($c) {
          foreach($this->ExecCMD($c) as $k=>$v){ $v = is_object($v)? (array) $v : $v; 
              if(isset($v['id'])) $A[$v['id']] = (array) $v['a']; 
          } 
          return $A;
      }
      
      public function db2data($c) { $A = ($this->ExecCMD($c))[0];  
         $d = $A->v->getData(); if( $A->a->zip =='gzip') $d=gzuncompress($d); 
         return $d;
      }
      public function UpdateItem($q, $v) {  $col=$q['col']; $id=$q['id'];  $keyid=isset($q['keyid'])?$q['keyid']:'id'; 
        $a = $this->ExecCMD([find=>$col, filter=>[$keyid=>$id], projection=>[a=>1] ])[0]->a; 
        if(isset($q['a']['ext'])) $u['a.ext']=$q['a']['ext'];
        if(isset($q['a']['zip'])) {$a->zip=$q['a']['zip']; $u['a.zip']=$a->zip; }
        
        if(isset($a->zip) && $a->zip=='gzip') {
            $u['v'] = new \MongoDB\BSON\Binary(gzcompress($v), \MongoDB\BSON\Binary::TYPE_GENERIC);
        } else { $u['v'] = $v; }
        //\IO\pa($u); 
        
        $q=[update=>$col,  updates=>[ [q=>[$keyid=>$id], u=>['$set'=>$u], upsert=>true]   ]  ]; 
        $this->ExecCMD($q); echo 'Updated'; //\IO\pa($this->ExecCMD([find=>$col, filter=>[$keyid=>$id]]));
        return;
      }
      public function count($col, $id) {return count($this->ExecCMD([find=>$col, filter=>[id=>$id], projection=>[id=>1] ])); }
      public function f2db($f2b,$col='Q', $O=[]) { $f=$f2b['f']; 
        if(!file_exists($f)) return "file $f doesn't exists"; 
        $fn=basename($f); $ext=strtolower(pathinfo($f, PATHINFO_EXTENSION)); $id=pathinfo($f, PATHINFO_FILENAME);
        $n=$this->count($col,$id); //$n=count($this->ExecCMD([find=>$col, filter=>[id=>$id]]));  
        $v = new \MongoDB\BSON\Binary(gzcompress(file_get_contents($f)), \MongoDB\BSON\Binary::TYPE_GENERIC);
        $a = [Name=>$fn, ext=>$ext, zip=>'gzip']; if(isset($f2b['a'])) $a=array_merge($a,$f2b['a']);
        //$u = [a=>$a, v=>$v]; 
        if($n==0) {$u = [a=>$a, v=>$v]; $u['_id'] = (String)(new \MongoDB\BSON\ObjectId());} else $u = [v=>$v];
        $q=[update=>$col,  updates=>[ [q=>[id=>$id], u=>['$set'=>$u], upsert=>true]   ]  ];  
        $this->manager->executeCommand($this->db, new \MongoDB\Driver\Command($q) ); 
      }
      public function Edit($A) { $r = $A[0]; 
          $LoadPHP=$this->LoadPHP; $db=$this->db; $col = $this->collection; $uqid=uniqid();
          $id = (string)$r->_id; unset($r->_id); 
          $Update = "<button onclick=\"
             var u = {'\$set': JSON.parse( $('#TA$uqid').val() ) };
             var q = {update: '$col', updates: [{q: {_id: '$id' }, u : u, 'upsert': true}]  };
             dimag({'outputid':'inserted$uqid', 'LoadPHP':'$LoadPHP', 'db':'$db', 'col': '$col', 'q':q });
           \">Save</button>";
          $Itext = "<textarea rows=5 cols=80 id=TA$uqid>". json_encode($r,JSON_PRETTY_PRINT) ."</textarea>";
          echo "<br/>$Itext <br/> $Update <span id=inserted$uqid></span><br/>"; 
      }
      public function QuickU($id, $k,$v, $O=[]) { //echo json_encode($v).": $k<p/>"; exit();
          $n = isset($v['Name'])?$v['Name']:"None"; $nn=$n; $n = isset($O['n']) && $O['n']>0 ? "<b>$n</b>" : $n; 
          $LoadPHP=$this->LoadPHP; $db=$this->db; $col = $this->collection; $uqid=uniqid();
          $outputid = isset($v['outputid'])?$v['outputid']:'tableM'; 
          $keys = [outputid=>$outputid, LoadPHP=>$LoadPHP, db=>$db, col=>$col]; 
          
          $f= isset($v['LoadFile'])? $v['LoadFile'] : '';  $vs=json_encode($v); 
          if(!$this->ignoreEdit) { 
            $UB = $this->EditB($id, $LoadPHP, $db, $col, "$k.Name", $v['Name']); 
            if(isset($v['q']['find'])) $UB .= ", Col: ". $this->EditB($id, $LoadPHP, $db, $col, "$k.q.find", $v['q']['find']);
            $UB .= $this->EditB($id, $LoadPHP, $db, $col, "$k", $vs,1); // Raw edits
          } else {$keys['a']=$v; $keys['_id']=$id; 
            $ctoggle='$(".classkeys").css("background-color", "");$(this).css("background-color", "yellow");'; 
            if(isset($v['LoadA'])) { $keys2 = [outputid=>'tableR', LoadPHP=>'AssessmentDB.php', db=>$db];
                $keys2['LoadA'] = [find=>"Assessments", filter=>[id=>$v['LoadA'] ]];
                $UB = sprintf('<button class=classkeys onclick=\' %s dimag(%s); dimag(%s); \'>%s</button>',$ctoggle, json_encode($keys), json_encode($keys2),$n);
            } else {
                $UB = sprintf('<button class=classkeys onclick=\' %s dimag(%s);\'>%s</button>',$ctoggle, json_encode($keys), $n); 
            }
          } //query only
        return $UB;          
      }
      public function EditB($id, $LoadPHP, $db, $col,  $k, $n, $raw=0) {  $uqid=uniqid(); $UB=''; 
          if($raw==1) {$dis='none'; $UB .="<button onclick=\" \$('#$uqid').toggle(); \">Raw</button>"; } else $dis='';
          $UB .= "<span id='$uqid' contenteditable=true style='display:$dis;' onkeyup=\"
             var value = ( $raw ==1)? JSON.parse( $(this).text() ) : $(this).text(); 
             var u = {'\$set': {'$k': value } };
             var q = {update: '$col', updates: [{q: {_id: '$id' }, u : u, 'upsert': true}]  };  
             dimag({outputid:'None', LoadPHP:'$LoadPHP', db:'$db',  q:q });
           \">$n</span>";
          return $UB; 
      }
      public function queryB($n, $oid, $LoadPHP, $db, $col, $a, $O=[]) {  // Not USED
          return sprintf('<button onclick=\'dimag({outputid:"%s", LoadPHP:"%s", db:"%s", col:"%s", a:%s});\'>%s</button>',$oid,$LoadPHP,$db,$col,$a,$n); 
      }
      public function Load2($r,$O=[]) { 
          $r=is_object($r)? json_decode(json_encode($r), true) : $r; // Messes with MongoDB Object, so be careful!
          $uqid=uniqid(); $id=$r['_id']; $edit= !$this->ignoreEdit; 
          $S = (isset($r['a']['ContentKey'])) ? $r['a']['ContentKey'] : 'S'; 
            
          $ns2=\IO\nbsp(2); $ns4=\IO\nbsp(4); $ns6=\IO\nbsp(6);  
          $s =''; $key='a'; 
          $nL1=0; $sa = ''; 
          //echo "<input type=checkbox onclick=\" $('.tvk').attr('contenteditable',true); \">Cb</input>"; 
          if(isset($r[$S])) foreach($r[$S] as $k=>$v) {  //Loop Level 1
              $key1="$S.$k.a"; $nL1++; 
              $ss=''; $nL2=0; 
              if(isset($v[$S])) foreach($v[$S] as $kk=>$vv) { //Loop Level 2
                 $key2="$S.$k.$S.$kk.a"; $nL2++; 
                 $sss=''; $nL3=0; 
                 if(isset($vv[$S])) foreach($vv[$S] as $kkk=>$vvv) { $nL3++; //Loop Level 3
                   $key3="$S.$k.$S.$kk.$S.$kkk.a";
                   $sss .= "<br/>$ns6 ".$this->QuickU($id, $key3, $vvv['a']);
                 }
                 if(isset($vv['Q'])) foreach($vv['Q'] as $kkk=>$vvv) { 
                   $sss .= "<br/>$ns6"."<button>".$vvv."</button>";  //need to fix
                 }
                 $ss .= "<br/>$ns4 ". $this->QuickU($id, $key2, $vv['a'],['n'=>$nL3]); 
                 $ss .= ($nL3)? \IO\toggle("$uqid-$k-$kk",($sss),'+') : $sss;
              }
              $sa .= "<br/>$ns2 ".$this->QuickU($id, $key1, $v['a'], ['n'=>$nL2]);
              $nk=sizeof($v[$S]); $sn = "<br/>$ns4 ".$this->QuickU($id, "$S.$k.$S.$nk.a", [Name=>'Type New Name to Save']);
              if($edit) $ss .= "<button onclick=\" \$('#N$k$nk$uqid').toggle(); \">New2</button><span id=N$k$nk$uqid style='display:none;'>$sn</span>";
              $sa .= ($nL2)? \IO\toggle("$uqid-$k","$ss",'+') : $ss; 
          }
          $s .= $this->QuickU($id, $key, isset($r['a'])?$r['a']:[], ['n'=>$nL1]);
          $nk=sizeof($r[$S]); $sn = "<br/>$ns2 ".$this->QuickU($id, "$S.$nk.a", [Name=>'Type New Name to Save']);
          if($edit) $sa .= "<button onclick=\" \$('#N$nk$uqid').toggle(); \">New</button><span id=N$nk$uqid style='display:none;'>$sn</span>"; 
          
          $s .= ($nL1)? \IO\toggle("$uqid-0","$sa",isset($r['a']['toggle'])?$r['a']['toggle']:'+') : $sa; 
          if($edit) $sjson .=  \IO\toggle("$uqid-json",json_encode($r), "+"); 
          
          return "$sjson $s"; 
      }
      
      public function List2($A,$O=[]) { $this->ignoreEdit = isset($O['Edit']) ? !$O['Edit'] : 0;   
          $LoadPHP=$this->LoadPHP; $db=$this->db; $col = $this->collection; $uqid=uniqid();
          $ii=''; $DelB = \IO\B('x'); $EditB=\IO\B('p');
          if(!$this->admin) { $this->ignoreEdit=1; }
          
          foreach($A as $k=>$r) { $tmpoutid="$uqid-$k";   
           $id = (string)$r->_id;  
           $del = "<button onclick=\"
             $('#del$tmpoutid').css('text-decoration', 'line-through');
             var q = {delete: '$col', deletes: [ {q:{'_id':'$id'},limit:1 } ]  };   
             dimag({'outputid':'$tmpoutid', 'LoadPHP':'$LoadPHP', 'db':'$db', 'col': '$col', 'q':q });
           \">$DelB</button>";
            $edit = "<button onclick=\"
             var q= {find:'$col',filter:{'_id':'$id'} };  
             dimag({outputid:'$tmpoutid', EditDB:1, LoadPHP:'$LoadPHP', db:'$db', col:'$col', q:q});
            \">$EditB</button>";
            if($this->ignoreEdit) {$del=''; $edit=''; }
            echo "<br/><span id='del$tmpoutid'>$del $edit ".$this->Load2($r)."</span>";
           // \IO\pa($r);
            echo "<span id='$tmpoutid'></span>";
            
          }
          
          $Save = "<button onclick=\"  
             var q = {insert: '$col', documents: [ JSON.parse( $('#TA$uqid').val() ) ]  };
             dimag({'outputid':'inserted$uqid', 'LoadPHP':'$LoadPHP', 'db':'$db', 'col': '$col', 'q':q });
           \">Save</button>";
          $Itext = "<textarea id=TA$uqid cols=50>". \Defaults\DefaultMD() ."</textarea>";
          $showB = "<br/><button onclick=\"  $('#inserted$uqid').show(); $(this).hide(); \">Insert</button> "; 
          if(!$this->ignoreEdit) echo "$showB <span id=inserted$uqid style='display:none;'>  <br/> $Itext <br/>$Save </span>"; 
      }
      public function ListFiles($Dir,$O=[]) { $uqid=uniqid(); $s='';  $col=$this->col; $db=$this->db; $LoadPHP=$this->LoadPHP; 
        $s .= "Directory <input type=text size=40 value=$Dir /> <br/>";
        $sListE = "<button onclick=\" var col= $('#inpcol$uqid').val(), tag=$('#tag$uqid').val(); 
          if(tag=='') filter={}; else filter={'a.tag':tag};   
          dimag({outputid:'tableM', db:'$db', col:col, LoadPHP:'$LoadPHP', q:{find:col,filter:filter}});
        \">List</button>";
        $tag = "<input id='tag$uqid' size=1 type=text value='' /> tag ";
        $s .= "Collection (db:$db) <input size=5 id='inpcol$uqid' type=text value=$col /> ($tag) $sListE  <br/>";
        
        $files=glob("$Dir/?????????????.{json,xml,html}", GLOB_BRACE);
        
        //$allIDs=$this->getIDs([find=>$this->col, projection=>[id=>1]]); 
        $ID2Attr=$this->getIDAttr([find=>$this->col, projection=>[id=>1,a=>1]]);  
        //\IO\pa($ID2Attr);
        $L=[db=>$this->db, col=>$this->col, LoadPHP=>$this->LoadPHP];
        
        $select="<input type=checkbox onclick=\" $('.select$uqid').prop('checked', $(this).is(':checked') ); \" />Check all ";
        $fntoggle="<input type=checkbox onclick=\" $('.fn$uqid').toggle(); \" />Show filename";
        //$str = "<tr><td>$fntoggle</td><td>tag</td><td>tags</td><td width=70%></td></tr>";   
        $tagA=[];  $str = "";
        foreach($files as $k=>$f) { $id="$k-$uqid"; $fid=pathinfo($f, PATHINFO_FILENAME); $fn=basename($f); $L['outputid']="msg-$id";
            $LoadFile = "<button onclick=\"
                var c = $(this).css('background-color'); c= (c=='rgb(255, 255, 0)')?'':'yellow'; $(this).css('background-color',c);
                if(c=='yellow') dimag({outputid:'msg-$id', db:'$db', col:'$col', LoadPHP:'$LoadPHP', a:{LoadFile:'$f'}}); else $('#msg-$id').html('');
            \">LoadFile</button>";
            $Add2dbs = isset($ID2Attr[$fid])?"<b>Update</b>":"Add2db";  
            
            if(isset($ID2Attr[$fid]['tag'])) {$tagv = $ID2Attr[$fid]['tag'];  if(!in_array($tagv,$tagA)) $tagA[]=$tagv; } else $tagv='';
            if(isset($ID2Attr[$fid]['tags'])) $tagsv = implode(', ', $ID2Attr[$fid]['tags']);  else $tagsv='';  
            
            $tag = "<input size=10 type=text value='$tagv' onkeyup=\" 
             var u = {'\$set': {'a.tag': $(this).val() } };
             var q = {update: '$col', updates: [{q: {id: '$fid' }, u : u, 'upsert': true}]  };
             dimag({outputid:'msg-$id', LoadPHP:'$LoadPHP', db:'$db',  q:q });
           \" ></input>";
            $tags = "<input size=20 type=text value='$tagsv' onkeyup=\" 
             var u = {'\$set': {'a.tags': $(this).val().trim().split(/\s*,\s*/) } };
             var q = {update: '$col', updates: [{q: {id: '$fid' }, u : u, 'upsert': true}]  };
             dimag({outputid:'msg-$id', LoadPHP:'$LoadPHP', db:'$db',  q:q });
           \" />";
            if(!isset($ID2Attr[$fid])) {$tag=''; $tags='';}
            $f2db = "<button onclick=\" dimag({outputid:'msg-$id', db:'$db', col:'$col', LoadPHP:'$LoadPHP', f2db:{f:'$f'}});\">$Add2dbs</button>";
            
            $LoadFromdb = sprintf('<button onclick=\'
              var c = $(this).css("background-color"); c= (c=="rgb(255, 255, 0)")?"":"yellow"; $(this).css("background-color",c);
              if(c=="yellow")  dimag(%s); else $("#msg-%s").html("");
            \'>LoadFromDB</button>', json_encode(array_merge($L, [a=>[LoadItemsFrom=>$col, Items=>[$fid] ] ]) ), $id);
            $LoadFromdb = isset($ID2Attr[$fid])? $LoadFromdb : '';
            
            $fnt="$k: <span class=fn$uqid style='display:none;'>$fn</span>";
            $select="<input class=select$uqid type=checkbox id=$fid />";
            
            //$str .= "<tr><td>$fnt $f2db</td><td>$tag</td><td>$tags</td><td>$LoadFile $LoadFromdb<span id=msg-$id></span></td></tr>";
            $str .= "<tr><td>$select $fnt $f2db $LoadFile $LoadFromdb | $tag | $tags <span id=msg-$id></span></td></tr>";
        }
        //$tag=''; foreach($tagA as $k=>$v) { $tag .= "<option>$v</option>"; }; $tag = "<select>$tag</select>";
        
        $tagdrop = "<button onclick=\" $('#tagdrop$uqid').toggle(); if( $(this).text() == '+') $(this).text('-'); else $(this).text('+'); \">+</button>";
        $tag=''; foreach($tagA as $k=>$v) { $tag .= "<input type=checkbox>$v</input><br/>"; };
        $tag = "$tagdrop <span id=tagdrop$uqid style='display:none;'><span style='position:fixed; z-index:2; background-color:white; outline-style:solid;'>$tag</span></span>";
        $sh = "<tr><td>$select | $fntoggle | tag $tag | tags </td></tr>";
        $s .= "<table border=1 width=100%>$sh $str</table>";
        
        return $s;
      }
      public function SaveUserItem($q, $v) {  $col=$q['col']; $id=$q['id']; $ucol="Users_$col"; $uid=$q['uid'];  $admin=$this->admin;       
        $iattempt = isset($q['iattempt'])?$q['iattempt']:0; 
        if($admin) {      
             $this->ExecCMD( [update=>$col,  updates=>[ [q=>[id=>$id], u=>['$set'=> [Soln => $v['Soln']] ], upsert=>true]   ]  ] ); 
             echo 'Admin Updated at '.date("Y-m-d H:i:s");
        } else {
          $u["Users.$uid.$iattempt.uSoln"] = $v['Soln'];
          $q=[update=>$ucol,  updates=>[ [q=>[id=>$id], u=>['$set'=>$u], upsert=>true]   ]  ];
          $this->ExecCMD($q); echo "$uid solved it at ".date("Y-m-d H:i:s");
        }
        return;
      }

      function LoadItems($a,$O=[]) { $outputid=$this->outputid; $ShowA=isset($a['ShowA'])?$a['ShowA']:0; //\IO\pa($a); return;
        $nattempt=4; //hardcoded, need to read from input
        $uqid=uniqid(); $db=$this->db; $col=$a['LoadItemsFrom']; $ucol="Users_$col"; $uid=$this->uid; $LoadPHP=$this->LoadPHP; $admin=$this->admin;
        $uid = str_replace(".","_",$uid); //Sanitize uid's '.' with '_'
        //\IO\pa($a); return;
        $s = ""; $ssol='';  echo "<script>var sa=[]; </script>"; 
        $iattempt=0; $reset=isset($a['reset']); if($reset) echo \IO\color("$uid answers resent<br/>",1,'yellow'); 
        //$reset=1;
        foreach($a['Items'] as $k=>$v) { 
          $d = $this->ExecCMD([find=>$col, filter=>[id=>$v], projection=>[_id=>0] ])[0]; 
          if( $d->a->zip =='gzip') $d->v=gzuncompress($d->v->getData());
          if($admin) $EditB = $this->EditItem([find=>$col, filter=>['id'=>$v] ], [Button=>1,outputid=>"Edit$k$uqid"]);
          
          $ret=true; foreach(['<input', '<textarea','<select'] as $kk=>$vv) {if(!(strpos($d->v, $vv)===false)) { $ret=false; }}
          if($ret) {$s .= "<span class=scan$k$uqid id=Edit$k$uqid>".$d->v."</span> <br/> $EditB"; continue;} // Return if users doesn't need to input anything
          
          if( $this->count($ucol,$id)<1) $this->ExecCMD([update=>$ucol, updates=>[ [q=>[id=>$v], u=>['$set'=>[a=>$d->a] ], upsert=>true] ]]);
          
          //$u["Users.$uid.0"] = [a=>$d->a, Soln=>$d->Soln, v=>new \MongoDB\BSON\Binary(gzcompress($d->v), \MongoDB\BSON\Binary::TYPE_GENERIC) ];
          //$this->ExecCMD([update=>$ucol,  updates=>[ [q=>[id=>$v], u=>['$set'=>$u], upsert=>true] ] ]);
          //$this->ExecCMD([update=>$ucol,  updates=>[ [q=>[id=>$v], u=>['$unset'=>[Users=>""] ], upsert=>true] ] ]);
          //\IO\pa($this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>["Users.$uid"=>1] ])[0]->Users->$uid);
          //echo $nudata; continue;
          //\IO\pa($this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>["Users.$uid"=>1] ])[0]->Users->$uid); return;
          
          $nudata=sizeof($this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>["Users.$uid"=>1] ])[0]->Users->$uid); 
          
          if($reset || $nudata<1) { $u = [a=>$d->a];
            if(isset($d->a->PHP) && $d->a->PHP==1 ) { 
                $PHP = \IO\strPHPstr($d->v); $d->v = $PHP['ss'];
                foreach($d->Soln as $kk=>$vv) { $PHPB = \IO\strPHPstr($vv->value);
                    if(sizeof($PHPB['keys'])>0) { $vv->value=$PHPB['ss']; $PHP['keys']=array_merge($PHP['keys'],$PHPB['keys']);  }
                }
              $d->v = \IO\PHPEval($d->v,$PHP['keys']);  
              foreach($d->Soln as $kk=>$vv) $vv->value = \IO\PHPEval($vv->value,$PHP['keys']);  
            } 
            $u["Users.$uid"][$iattempt] = [a=>$d->a, Soln=>$d->Soln, v=>new \MongoDB\BSON\Binary(gzcompress($d->v), \MongoDB\BSON\Binary::TYPE_GENERIC) ];
            $this->ExecCMD([update=>$ucol,  updates=>[ [q=>[id=>$v], u=>['$set'=>$u], upsert=>true] ] ]);
          }
          //echo "R:$reset $ucol $uid"; \IO\pa($this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>[_id=>0] ]));
         if($this->admin) {$d = $this->ExecCMD([find=>$col, filter=>['id'=>$v], projection=>[_id=>0] ])[0];
         } else {$d = $this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>["Users.$uid"=>1] ])[0]->Users->$uid[$iattempt]; }
         if( $d->a->zip =='gzip') $d->v=gzuncompress($d->v->getData());
         
          //foreach($sol[0]->Soln as $kk=>$vv) { $ssol .= "<span id=ssol$k$kk$uqid style='display:none;'>".$vv->value."</span>"; }
          $stmp = $d->v; 
          //\IO\pa( \IO\strPHPstr($this->db2data([find=>'Q', filter=>['id'=>'5b98af8a38f2d'] ])) ); 
          
          $s .= "<span class=scan$k$uqid id=Edit$k$uqid>".$d->v."</span>"; // this is the main content
          
          $sol=[]; $disabled=0; 
          if($admin) {if(isset($d->Soln)) $sol=$d->Soln;} else {if(isset($d->uSoln)) {$sol= $ShowA?$d->Soln:$d->uSoln; $disabled=1;  } }  ;
          //\IO\pa($d);
          if(sizeof($sol)>0) {
            $s .= "<script>sa[$k] = ". json_encode($sol) . ";  var i=0; 
             $('.scan$k$uqid :input').each(function(){ var val= sa[$k][i]['value'];
              if( $(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio' ) {  $(this).prop('checked', val=='true');
              } else { $(this).val( val );  }
              i++;
             }); 
             if($disabled) { $('.scan$k$uqid :input').attr('disabled','disabled'); $('#Submit$k$uqid').attr('disabled','disabled'); } 
            </script>";
          }
          $s .= "<br/><button class=B$k$uqid id=Submit$k$uqid onclick=\" 
          var i=0, v={Soln:[]}, val; 
          $('.scan$k$uqid :input').each(function(){  var tpe = $(this).attr('type'); 
            //var tpe = $(this).is('textarea') ? 'textarea' : tpe = $(this).attr('type'); 
            if ( $(this).is('textarea') )  tpe='textarea'; else if( $(this).is('select') ) tpe='select';  
              if(tpe=='checkbox' || tpe=='radio' ) {val = $(this).is(':checked'); 
              } else { val = $(this).val();  }
              v['Soln'].push({type:tpe, value:val}); 
          });
          dimag({outputid:'msg$k$uqid', LoadPHP:'$LoadPHP', db:'$db', SaveUserItem:{col:'$col',id:'$v',uid:'$uid',iattempt:'$iattempt'},data:v});
          \">Submit</button>"; 
         // $ISCheck = "<input type=checkbox id=ISoln$k$uqid />Admin "; 
         
          $resetB = "<button onclick=\"dimag({outputid:'$outputid', LoadPHP:'$LoadPHP', db:'$db', a:{LoadItemsFrom:'$col',Items:['$v'],reset:['$uid']} }); \">reset</button> ";
          //$AdminB = "<button onclick=\"dimag({outputid:'$outputid', LoadPHP:'$LoadPHP', db:'$db', a:{LoadItemsFrom:'$col',Items:['$v'],admin:1} }); \">Admin</button> ";
          $AdminB = "Admin"; 
          $s .= "<button onclick=\"dimag({outputid:'$outputid', LoadPHP:'$LoadPHP', db:'$db', a:{LoadItemsFrom:'$col',Items:['$v'],ShowA:1, iattempt:'$iattempt'} }); \">ShowA</button> ";
          
          $MySol = "<button class=B$k$uqid onclick=\" var v={Soln:[]},  i=0;
            $('.scan$k$uqid :input').each(function(){ var val= sa[$k][i]['value']; //$('#ssol$k'+i+'$uqid').text();  //sa['Soln'][i]['value']; 
              if( $(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio' ) {  $(this).prop('checked', val=='true');
              } else { $(this).val( val );  }
              i++; 
            });
          \">MySol</button>"; 
          //$s .= $MySol; 
          $disableB=" <script>var i=0; \$('.scan$k$uqid :input').each(function(){ i++; }); if(i<1) \$('.B$k$uqid').hide(); </script>"; 
          if($admin) $s .= " <span style='border-style: dashed;'>$EditB $resetB $AdminB </span>"; 
          $s .= "<span id=msg$k$uqid>$disableB </span><hr/>";
        }
        $Math="<script>MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']);</script>";
        return $s.$Math; 
      
      }
      
      function LoadItem($q,$O=[]) { $uqid=uniqid(); $outputid=$this->outputid; $db=$this->db;
      \IO\pa($q); return;
      
           $ShowA=isset($a['ShowA'])?$a['ShowA']:0; //\IO\pa($a); return;
      $nattempt=4; //hardcoded, need to read from input
        $col=$a['LoadItemsFrom']; $ucol="Users_$col"; $uid=$this->uid; $LoadPHP=$this->LoadPHP; $admin=$this->admin;
      $uid = str_replace(".","_",$uid); //Sanitize uid's '.' with '_'
      //\IO\pa($a); return;
      $s = ""; $ssol='';  echo "<script>var sa=[]; </script>";
      $iattempt=0; $reset=isset($a['reset']); if($reset) echo \IO\color("$uid answers resent<br/>",1,'yellow');
      //$reset=1;
      foreach($a['Items'] as $k=>$v) {
          $d = $this->ExecCMD([find=>$col, filter=>[id=>$v], projection=>[_id=>0] ])[0];
          if( $d->a->zip =='gzip') $d->v=gzuncompress($d->v->getData());
          if($admin) $EditB = $this->EditItem([find=>$col, filter=>['id'=>$v] ], [Button=>1,outputid=>"Edit$k$uqid"]);
          
          $ret=true; foreach(['<input', '<textarea','<select'] as $kk=>$vv) {if(!(strpos($d->v, $vv)===false)) { $ret=false; }}
          if($ret) {$s .= "<span class=scan$k$uqid id=Edit$k$uqid>".$d->v."</span> <br/> $EditB"; continue;} // Return if users doesn't need to input anything
          
          if( $this->count($ucol,$id)<1) $this->ExecCMD([update=>$ucol, updates=>[ [q=>[id=>$v], u=>['$set'=>[a=>$d->a] ], upsert=>true] ]]);
          
          //$u["Users.$uid.0"] = [a=>$d->a, Soln=>$d->Soln, v=>new \MongoDB\BSON\Binary(gzcompress($d->v), \MongoDB\BSON\Binary::TYPE_GENERIC) ];
          //$this->ExecCMD([update=>$ucol,  updates=>[ [q=>[id=>$v], u=>['$set'=>$u], upsert=>true] ] ]);
          //$this->ExecCMD([update=>$ucol,  updates=>[ [q=>[id=>$v], u=>['$unset'=>[Users=>""] ], upsert=>true] ] ]);
          //\IO\pa($this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>["Users.$uid"=>1] ])[0]->Users->$uid);
          //echo $nudata; continue;
          //\IO\pa($this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>["Users.$uid"=>1] ])[0]->Users->$uid); return;
          
          $nudata=sizeof($this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>["Users.$uid"=>1] ])[0]->Users->$uid);
          
          if($reset || $nudata<1) { $u = [a=>$d->a];
          if(isset($d->a->PHP) && $d->a->PHP==1 ) {
              $PHP = \IO\strPHPstr($d->v); $d->v = $PHP['ss'];
              foreach($d->Soln as $kk=>$vv) { $PHPB = \IO\strPHPstr($vv->value);
              if(sizeof($PHPB['keys'])>0) { $vv->value=$PHPB['ss']; $PHP['keys']=array_merge($PHP['keys'],$PHPB['keys']);  }
              }
              $d->v = \IO\PHPEval($d->v,$PHP['keys']);
              foreach($d->Soln as $kk=>$vv) $vv->value = \IO\PHPEval($vv->value,$PHP['keys']);
          }
          $u["Users.$uid"][$iattempt] = [a=>$d->a, Soln=>$d->Soln, v=>new \MongoDB\BSON\Binary(gzcompress($d->v), \MongoDB\BSON\Binary::TYPE_GENERIC) ];
          $this->ExecCMD([update=>$ucol,  updates=>[ [q=>[id=>$v], u=>['$set'=>$u], upsert=>true] ] ]);
          }
          //echo "R:$reset $ucol $uid"; \IO\pa($this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>[_id=>0] ]));
          if($this->admin) {$d = $this->ExecCMD([find=>$col, filter=>['id'=>$v], projection=>[_id=>0] ])[0];
          } else {$d = $this->ExecCMD([find=>$ucol, filter=>['id'=>$v], projection=>["Users.$uid"=>1] ])[0]->Users->$uid[$iattempt]; }
          if( $d->a->zip =='gzip') $d->v=gzuncompress($d->v->getData());
          
          //foreach($sol[0]->Soln as $kk=>$vv) { $ssol .= "<span id=ssol$k$kk$uqid style='display:none;'>".$vv->value."</span>"; }
          $stmp = $d->v;
          //\IO\pa( \IO\strPHPstr($this->db2data([find=>'Q', filter=>['id'=>'5b98af8a38f2d'] ])) );
          
          $s .= "<span class=scan$k$uqid id=Edit$k$uqid>".$d->v."</span>"; // this is the main content
          
          $sol=[]; $disabled=0;
          if($admin) {if(isset($d->Soln)) $sol=$d->Soln;} else {if(isset($d->uSoln)) {$sol= $ShowA?$d->Soln:$d->uSoln; $disabled=1;  } }  ;
          //\IO\pa($d);
          if(sizeof($sol)>0) {
              $s .= "<script>sa[$k] = ". json_encode($sol) . ";  var i=0;
             $('.scan$k$uqid :input').each(function(){ var val= sa[$k][i]['value'];
              if( $(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio' ) {  $(this).prop('checked', val=='true');
              } else { $(this).val( val );  }
              i++;
             });
             if($disabled) { $('.scan$k$uqid :input').attr('disabled','disabled'); $('#Submit$k$uqid').attr('disabled','disabled'); }
            </script>";
          }
          $s .= "<br/><button class=B$k$uqid id=Submit$k$uqid onclick=\"
          var i=0, v={Soln:[]}, val;
          $('.scan$k$uqid :input').each(function(){  var tpe = $(this).attr('type');
            //var tpe = $(this).is('textarea') ? 'textarea' : tpe = $(this).attr('type');
            if ( $(this).is('textarea') )  tpe='textarea'; else if( $(this).is('select') ) tpe='select';
              if(tpe=='checkbox' || tpe=='radio' ) {val = $(this).is(':checked');
              } else { val = $(this).val();  }
              v['Soln'].push({type:tpe, value:val});
          });
          dimag({outputid:'msg$k$uqid', LoadPHP:'$LoadPHP', db:'$db', SaveUserItem:{col:'$col',id:'$v',uid:'$uid',iattempt:'$iattempt'},data:v});
          \">Submit</button>";
          // $ISCheck = "<input type=checkbox id=ISoln$k$uqid />Admin ";
          
          $resetB = "<button onclick=\"dimag({outputid:'$outputid', LoadPHP:'$LoadPHP', db:'$db', a:{LoadItemsFrom:'$col',Items:['$v'],reset:['$uid']} }); \">reset</button> ";
          //$AdminB = "<button onclick=\"dimag({outputid:'$outputid', LoadPHP:'$LoadPHP', db:'$db', a:{LoadItemsFrom:'$col',Items:['$v'],admin:1} }); \">Admin</button> ";
          $AdminB = "Admin";
          $s .= "<button onclick=\"dimag({outputid:'$outputid', LoadPHP:'$LoadPHP', db:'$db', a:{LoadItemsFrom:'$col',Items:['$v'],ShowA:1, iattempt:'$iattempt'} }); \">ShowA</button> ";
          
          $MySol = "<button class=B$k$uqid onclick=\" var v={Soln:[]},  i=0;
            $('.scan$k$uqid :input').each(function(){ var val= sa[$k][i]['value']; //$('#ssol$k'+i+'$uqid').text();  //sa['Soln'][i]['value'];
              if( $(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio' ) {  $(this).prop('checked', val=='true');
              } else { $(this).val( val );  }
              i++;
            });
          \">MySol</button>";
          //$s .= $MySol;
          $disableB=" <script>var i=0; \$('.scan$k$uqid :input').each(function(){ i++; }); if(i<1) \$('.B$k$uqid').hide(); </script>";
          if($admin) $s .= " <span style='border-style: dashed;'>$EditB $resetB $AdminB </span>";
          $s .= "<span id=msg$k$uqid>$disableB </span><hr/>";
      }
      $Math="<script>MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']);</script>";
      return $s.$Math;
      
      }
      function LoadData($d,$O=[]) { $outputid=$this->outputid;
          if( $d->a->zip =='gzip') $d->v=gzuncompress($d->v->getData()); 
          return $d->v . "<script>MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$outputid']);</script>";
      }
      function EditItem($q,$O=[]) { $uqid=uniqid(); $db=$this->db;  
          $LoadPHP=$this->LoadPHP; $editor=$this->editor;  $name="id".$uqid; $id=$name;
          $outputid = isset($O['outputid'])?$O['outputid']:$this->outputid;  
          $L=[LoadPHP=>$this->LoadPHP,outputid=>$outputid];  $L['EditItem']=$q;
          /*
          if($_POST['send']['Reset']==1) { echo 'Reset' . json_encode($q);
            $this->ExecCMD([ update=>$q['find'],   updates=> [   [q=>$q['filter'], u=>['a.zip'=>'none', 'a.ext'=>'text', v=>''] ]    ]   ]); 
            return;
          }
          $reset = sprintf('<button onclick=\'dimag(%s);\'>Reset</button>',json_encode(array_merge($L,[Reset=>1])) );
          */
          if(isset($O['Button'])) return sprintf('<button onclick=\'dimag(%s);\'>Edit</button>',json_encode($L) ) . $reset; 
          
          $A = ($this->ExecCMD($q))[0]; 
          $data = isset($A->v)?$A->v:''; if( $A->a->zip =='gzip') $data=gzuncompress($data->getData());
          //\IO\pa($A); //echo "D:".$data; return;
          $data=htmlentities($data);  //if(!($editor=='ckeditor')) $data=htmlentities($data);
          //echo json_encode($q); return;
          $editor = ($A->a->ext=='html')?'ckeditor':'TA';
          $htmlchecked = ($A->a->ext=='html')?'checked':''; $zipchecked = ($A->a->zip=='gzip')?'checked':'';
          //echo "H:$htmlchecked, $zipchecked";
          $col=$q['find']; $keyid= key($q['filter']);  $itemid = $q['filter'][$keyid]; 
          $qs = json_encode($q); 
          
          $s = "<br/><textarea rows=20 cols=130 name=$name id='$id'>$data</textarea>"; 
          $s .= "
            <br/><button onclick=\" var a={}; 
              a.ext = $('#ext$uqid').prop('checked')? $('#ext$uqid').val():'text'; 
              a.zip = $('#zip$uqid').prop('checked')? $('#zip$uqid').val() : 'none'; //alert(JSON.stringify(a));
              if('$editor'=='ckeditor') var d=CKEDITOR.instances.$name.getData(); else var d = $('#$id').val();  
              dimag({outputid:'msg$uqid', LoadPHP:'$LoadPHP', db:'$db', UpdateItem:{col:'$col',id:'$itemid',keyid:'$keyid',a:a},data:d});
            \">Submit</button>
            <input id=ext$uqid type=checkbox value=html $htmlchecked>html</input> 
            <input id=zip$uqid type=checkbox value=gzip $zipchecked>gzip</input>
            <span>{db:$db, col:$col, $keyid:$itemid}</span>
            <span id='msg$uqid'></span>
        ";
          if(isset($A->a->PHP) && $A->a->PHP>0) { $PHP = isset($A->PHP)?$A->PHP:''; 
              $sphp .= "<br/><textarea id=PHP$uqid rows=10 cols=100>$PHP</textarea>
                 <br/><button onclick=\"
                 var q = {update: '$col', updates: [{q: {'$keyid': '$itemid' }, u : {'\$set':  {PHP: $('#PHP$uqid').val()}  }, 'upsert': true}]  };
                 dimag({outputid:'msg$uqid', LoadPHP:'$LoadPHP', db:'$db', 'col': '$col', 'q':q }); 
                 \">Save</button>
              "; 
              $s .= "<br/>PHP:".\IO\toggle(uniqid(),$sphp);
          }
          if(isset($A->Soln)) { 
              foreach($A->Soln as $k=>$v) $ssol .= $v->value; 
          }
          //\IO\pa($A); 
        if($editor=='ckeditor') {$s .= "<script>CKEDITOR.replace('$name', { mathJaxLib: mathJaxLib }); </script>"; }
        return $s.'<hr/>';
      } 
      
  }
  //------------------
  class IO {
        public $LoadPHP='index.php', $outputid='none', $toggle='-', $editor='Default';
        public function __construct($f, $O=array()) { 
          foreach($O as $k=>$v) {$this->$k = $v; }   
          $this->f=$f; $this->id = uniqid();
          if(is_dir($f)) die("'$f' is a directory"); 
          if(!file_exists($f)) { if(!($this->admin)) return; echo "'$f' does't exists"; }
          $this->fext=strtolower(pathinfo($f, PATHINFO_EXTENSION));  
          if($this->editor=='Default') {if($this->fext=='html') $this->editor='ckeditor'; }
        }
   function html() {  $f=$this->f; $oid=$this->outputid; $LoadPHP=$this->LoadPHP; $s = '';  $uqid=uniqid(); 
    if($this->admin) $s .= "<button onclick=\" dimag({'outputid':'$oid', 'LoadPHP':'$LoadPHP', 'EditFile':'$f'});\">Edit</button><br/>"; 
    if($this->fext == 'html') {
      $s .= file_get_contents($f); 
      $s .= "<script>
	    MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$oid']);
	   \$('#bodymain').data()['file']='$f'; 
	 </script>";
    } else {$s .= '<pre>'.htmlentities(file_get_contents($f)).'</pre>';}
     
    echo $s; 
   }
   function html2() {  $f=$this->f; $oid=$this->outputid; $LoadPHP=$this->LoadPHP; $s = '';  $uqid=uniqid();
   if($this->admin) $s .= "<button onclick=\" dimag({'outputid':'$oid', 'LoadPHP':'$LoadPHP', 'EditFile':'$f'});\">Edit</button><br/>";
   if($this->fext == 'html') {
       $s .= "<span id=$uqid>".file_get_contents($f)."</span>";
       $s .= "<script>
	    MathJax.Hub.Queue(['Typeset',MathJax.Hub,'$oid']);
        setTimeout(function(){ //alert (size(id2array('#$uqid form')));
          dimag2({'outputid':id2array('#$uqid form'), 'file':'$f', 'LoadPHP':'Questions.php'});
        },500);
	\$('#bodymain').data()['file']='$f';
	</script>";
   } else {$s .= '<pre>'.htmlentities(file_get_contents($f)).'</pre>';}
   
   echo $s;
   }
   //----------------------------
   function xml() {  $f=$this->f; $oid=$this->outputid; $LoadPHP=$this->LoadPHP;  
       $s=''; $ss=''; $Save=0; $id=uniqid(); 

    if($this->admin) { $s .='<br/>'; 
      $s .= "<button onclick=\" var o={'id':'$id'};
        \$('.c$id').each(function(){ var id= \$(this).attr('id'); o[id] = \$(this).val();  });
        dimag({'outputid':'$oid', 'LoadPHP':'$LoadPHP', 'LoadFile':'$f', 'SaveData': o});
      \">Save</button>"; 
      $s .= "<button onclick=\" dimag({'outputid':'$oid', 'LoadPHP':'$LoadPHP', 'EditFile':'$f'});\">Raw</button>"; 
    }
       $xml = simplexml_load_file($f); $i=0; 
       if(isset($_POST['send']['SaveData'])) {$Save=1; $d=$_POST['send']['SaveData']; $id=$d['id']; };
       foreach($xml as $k=>$v) { $a=$v->attributes(); 
	 $n=$a['name']; if(isset($a['Name'])) $n=$a['Name']; 
         $i++; 
         $val=$a['value']; if($Save) {$val=$d["id$id-$i"]; $v['value']=$val; }
         if(isset($a['Description'])) $desc=$a['Description']; else $desc=''; 
         if($k !='ParameterList') $ss .= "<tr><td>$n</td><td><input value='$val' id='id$id-$i' class='c$id' /></td><td>$desc</td></tr>"; 
         $sss = ''; $j=0; 
         if($k=='ParameterList') {
          foreach($v as $kk=>$vv) { $j++; 
           $a=$vv->attributes(); 
	   $nn=$a['name']; if(isset($a['Name'])) $nn=$a['Name']; 
           $val=$a['value']; if($Save) {$val=$d["id$id-$i-$j"]; $vv['value']=$val; }
           if(isset($a['Description'])) $desc=$a['Description']; else $desc=''; 
           $sss .= "<tr><td>$nn</td><td><input value='$val' id='id$id-$i-$j' class='c$id' /></td><td>$desc</td></tr>"; 
          }
          $ss .= "<tr><td>$n</td><td colspan=2><table border=1>$sss</table></td></tr>";
         }
       }
       //echo '<textarea>'.$xml->asXml().'</textarea>'; 
       if($Save) {$xml->asXml($f);     echo "Saved $f at " . date('Y-m-d h:m:s') . "<br/>";}
       $s .= "<table border=1>$ss</table>"; 
    
    echo $s; 
   }
   //----------------------------
   
   function Edit($LoadPHP='index.php', $oid='none', $etype='Default', $toggle='+') {   
    $f = $this->f; $uqid=$this->id; $LoadPHP=$this->LoadPHP; $editor=$this->editor; $toggle=$this->toggle; 
    $name="id".$uqid; $id=$name; 
    
    $data=file_get_contents($f); 
    $data=htmlentities($data);  //if(!($editor=='ckeditor')) $data=htmlentities($data);  

    $s = "<div id='msg_$uqid'></div>
     <br/><textarea rows=20 cols=130 name='$name' id='$id' data-file='$f' data-editor='$editor'>$data</textarea>
     <br/><button onclick=\"
      var einfo= \$('#$id').data(); var f = \$('#f2_$id').val();  einfo.force = \$('#cb_$id').is(':checked')?1:0; 
      if(einfo.editor=='ckeditor') var d=CKEDITOR.instances.$name.getData(); else var d = $('#$id').val(); 
      dimag({'outputid':'msg_$uqid', 'LoadPHP':'$LoadPHP', 'einfo':einfo, 'Save':f,'data':d});
      \">Submit</button>  
      <input type='text' id='f2_$id' size=50 value='$f' name='file'> 
      | Force <input type='checkbox' id='cb_$id'>          
    "; 
     if($editor=='ckeditor') { 
       $s .= "<script>
	 CKEDITOR.replace('$name', { 
		mathJaxLib: mathJaxLib
	});
       </script>"; 
     }
     if($toggle=='+') { 
          $s = "<button class=BEdit onclick=\" 
                var o = \$('#toggle_$uqid'), t = \$(this); 
                \$('.'+t.attr('class')).css('background-color', ''); t.css('background-color', 'yellow');
                if(o.css('display')=='none') t.text('-'); else t.text('+'); 
                o.toggle(); 
               \">+</button>" 
               ."<div id='toggle_$uqid' style='display:none;'>".$s."</div>"; 
     }
     echo $s; 
     
   } 	 
   //----------------------------
   function EditButton() {
      $f = $this->f; $LoadPHP=$this->LoadPHP; $outputid=$this->outputid; $fn=basename($f); 
      echo "<button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'EditFile':'$f'});\">$fn</button>";
   }
  }
  //------------------
  class Setup_Delete {
   public $outputid='tableM', $admin=0, $fMain='Main.xml',  $fLayout="Layout.xml", $fcss="CSS.css"; 
   public function __construct($O=array()) {   foreach($O as $k=>$v) {$this->$k = $v; }   }
   
   public function Initial() {  
    $outputid=$this->outputid; $LoadPHP=$this->LoadPHP; $DIR=$this->DIR;  
    $fMain = "$DIR/".$this->fMain; $fLayout = "$DIR/".$this->fLayout; $fcss = "$DIR/".$this->fcss; 
    if($this->admin) $s = "<br/>Edit:
      <button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'EditFile':'$fMain'});\">Main</button>
      <button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'EditFile':'$fLayout'});\">Layout</button>
      <button onclick=\"dimag({'outputid':'$outputid', 'LoadPHP':'$LoadPHP', 'EditFile':'$fcss'});\">CSS</button>
    "; 
    
   $css=str_replace(array("\n", "\t", "\r"), '', file_get_contents("$fcss") ); 
   $Layout=str_replace(array("\n", "\t", "\r"), '', file_get_contents("$fLayout") ); 
   $s .= "<script> 
        if($('#cssInitial').length == 0) {
             \$('head').append('<style id=cssInitial>$css</style>'); 
             dimag({'outputid':'tableL', 'LoadPHP':'$LoadPHP', 'LoadFile':'$fMain', 'Level':'Level0'});
             dimag({'outputid':'tableM', 'LoadPHP':'$LoadPHP', 'LoadFile':'Intro.html', 'Level':'Level1'});
        }
    </script>"; 
    echo $s; 
   }
 }
 //------------------
 function UpdateJSON($d) {  $f = $d['file']; $fn=basename($f);
   if(!file_exists($f)) return "Error UpdateJSON(): file '$f' doesn't exists!";
   $w = json_decode(file_get_contents($f),true);   
   foreach($d['Keys'] as $k=>$v) $w[$k]=$v; 
   file_put_contents($f,json_encode($w)); 
   echo "Updated";
 }  
  //------------------
  function SaveRaw($f,$data, $force=0) {  $dir=dirname($f); 
    if($force) {if(mkdir($dir, 0777, true)) echo "Dir '$dir' created<br/>";  } 
    file_put_contents($f,$data);   
    if(!is_dir($dir)) die("Error! Couldn't save because $dir doesn't exists. Check force to force it."); 
    echo "<br/>Saved $f at " . date('Y-m-d h:m:s') . "<br/>";
  }  
  function SavePOST() {  
     $force=false; if(isset($_POST['send']['einfo']['force'])) $force=$_POST['send']['einfo']['force'];
    \IO\SaveRaw($_POST['send']['Save'],$_POST['send']['data'], $force); 
   } 
  function Check($A,$k,$v) { $flag=0; 
   if(isset($A[$k])) { if(($A[$k]==$v)) $flag=1;   }
   return $flag; 
  }
  function CheckPOST($k,$v) { $flag=0;  
   if(isset($_POST['send'][$k])) { if(($_POST['send'][$k]==$v)) $flag=1;   }
   return $flag; 
  }
  function  B($b){
      $Buttons=['la'=> '&larr;', 'ra'=> '&rarr;', 'x'=>'&#10007;','p'=>'&#9998;']; 
      return $Buttons[$b]; 
  }
  function  color($s,$f=false,$c=''){  return $f?sprintf("<span style='background-color:%s;'>%s</span>",$c,$s):$s; }
  function  nbsp($n){ $s=''; for($i=0; $i<$n; $i++) {$s .= '&nbsp;';}; return $s; }
  function  eps(){return 1E-6; }
  function  p(){$s=$this->data; echo "<textarea rows='10' cols='50'>$s</textarea>"; }
  function  pa($a=NULL){ echo "<pre>"; print_r($a); echo "</pre>"; } 
  function  ApBs($A,$B){ return json_encode(array_merge($A,$B));  }
  function  ApBButton($A,$B,$n){ return sprintf('<button onclick=\'dimag(%s);\'>%s</button>', json_encode(array_merge($A,$B)), $n);  }
  
  function  strPHPstr($s){    $ss=$s; 
      preg_match_all('/PHP\s*\[([^\[]+)\]/',$ss,$m); 
      if(sizeof($m[0])>0) foreach($m[0] as $k=>$v) { $w=explode(',', $m[1][$k]); shuffle($w);  $ss = str_replace($v, $w[0], $ss); }
      preg_match_all('/PHP\s*{([^{]+)}/',$ss,$m);
      foreach($m[0] as $k=>$v) {
        if(!(strpos($m[1][$k], ':') === false))  {
          $w=explode(':', $m[1][$k]); $key=$w[0]; $vA = explode(',', $w[1]);
          $fac = isset($vA[2])?$vA[2]:1; $format = isset($vA[3])?$vA[3]:'2d';
          $vs = eval("return $fac;")*mt_rand($vA[0],$vA[1]);  $PHP[$key]=$vs;
          $ss = str_replace($v, sprintf("%$format",$vs), $ss); //$vclean = preg_replace("/\//",'\\/',$v); preg_replace("/($vclean)/", $vs, $ss);
        }
      }      
      return [ss=>$ss, s=>$s, keys=>$PHP];  
  }
  
  function PHPEval($s,$A,$O=[]) {$ss=$s;
  preg_match_all('/PHP\s*{([^{]+)}/',$ss,$m);
  foreach($m[0] as $k=>$v) {
      if (strpos($m[1][$k], ':') === false) { $w=explode(',', $m[1][$k]); $stmp = $w[0];
      $format = isset($w[1])?$w[1]:'1d';
      foreach($A as $kk=>$vv) $stmp=str_replace("$kk",$vv,$stmp);
      $ss = str_replace($v, sprintf("%$format", eval("return $stmp;") ), $ss);
      }
  }
  return  $ss;
  }
  
  function  id2file($f,$type=array('html','json','xml')) { 
	  if(!file_exists($f)) { $file = "$f.html"; 
   	    if(file_exists("$f.json")) $file = "$f.json"; 
   	    if(file_exists("$f.xml")) $file = "$f.xml"; 
	    $f = $file; 
	  } 
	  return $f; 
  } 

  function toggle($id,$s='None', $key='+', $showkey='+', $hidekey='-') {
    $s2=''; $display='none'; if($key=='-') $display='inline';
    $st = "<button onclick=\"
      if( \$(this).text() == '$showkey') {
        \$(this).text('$hidekey'); \$('#$id').show();
      } else {
       \$(this).text('$showkey'); \$('#$id').hide();
      }
    \">$key</button>";

    if($s != 'None') $s2 = "<span id=$id style='display:$display;'>".$s."</span>";
    return $st . $s2;

  }
//---------------CAMERA----------------------[
function CameraOpen($id,$ic) { 
 return "<span class=$id><br/><button onclick=\"
      //var facingMode = \$('#facingMode$id').is(':checked')?'user':'environment'; 
      CameraOpen({'id':'video$id','audio':false,facingMode:'environment'}); 
     \$('.$id').hide(); \$('#CameraMain$id').show(); \$('#CaptureButton$ic$id').show(); 
      \">Camera</button></span>
      ";
}

function CameraCapture($id,$ic,$SubmitID) { 
 return "   
    <span id=CaptureButton$ic$id  style='display:none;'><button onclick=\"
      Camera2Canvas({'id':'video$id',canvas:'canvas$id'}); 
      Canvas2Img({canvas:'canvas$id',photo:'photo$id'}); 
      CameraStopBoth({'id':'video$id'}); 
      var img = \$('#divphoto$id').html(); 
      \$('#append$id').is(':checked')? \$('#$SubmitID').append(img) : \$('#$ic$id').html(img); 
      \$('#CameraMain$id').hide(); \$('#CaptureButton$ic$id').hide(); \$('.$id').show(); 
  \">Capture</button></span> 
      ";
}

function CameraPlaceHolder($id,$width=320,$height=240) { 
 return "   
   <span id=CameraMain$id style='display:none;'>
  <div><video id=video$id style='display:block;' width=$width height=$height controls autoplay> Video not available.  </video></div>
  <div><canvas id=canvas$id style='display:none;'> </canvas></div>
  <div id=divphoto$id style='display:none;'><br/><img id=photo$id /> </div>
   Append <input id=append$id type=checkbox /> | 
   <button onclick=\" CameraStopBoth({'id':'video$id'}); CameraOpen({'id':'video$id','audio':false,facingMode:'environment'});  \">Rear</button>
   <button onclick=\" CameraStopBoth({'id':'video$id'}); CameraOpen({'id':'video$id','audio':false,facingMode:'user'}); \">Front</button>
   </span>
  ";
}
//---------------CAMERA----------------------]
function json2A($s) { return json_decode($s,true); }
function A2json($A) { return json_encode($A); }
function ClassesInO($c) { return get_class_methods($c); }

}
?>
