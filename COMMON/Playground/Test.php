<script>
function AddCol(id) {   $("#"+id+" tr:first").append("<th>newcol</th>");   $("#"+id+" tr:gt(0)").append("<td>Col</td>"); }
function AddRow(id) {   var s, n=$("#"+id+" tr").length; m=$("#"+id+" tr:first th").length; alert('n:'+n+', m:' + m); 
   for(j=0; j<m; j++) s += '<td>'+ $('#'+id+'-0-'+j).text()  + '</td>'; 
   $("#"+id+" tr:last").after("<tr>"+ s +"</tr>"); 
}
function DeleteBy(i,t,f){ if(t=='c') $('.'+i).remove(); else if(t=='id') $('#'+i).remove(); else if(t=='e') $(i).remove(); else return 0; }
function ToggleBy(n,bid,t){ if(t=='c') $('.'+i).toggle(); else if(t=='id') $('#'+i).toggle(); else if(t=='n') $('[name='+n+']').toggle(); else if(t=='e') $(i).toggle(); else return 0;  
    if($('#'+bid).text()=='+') $('#'+bid).text('-'); else $('#'+bid).text('+');  
}
</script>
<?php
SortableTable(); 

?>
<button onclick="AddRow('testtable')">AddRow</button>
<button onclick="AddCol('testtable')">AddCol</button>
<?php
  $s="1:&cross; 2:&and; 3:&or; 4:&equiv; 5:&loz; 6:&#9745;  7:&#9747; 8:&#9762; 9:&#9850; 10:&#9919; 11:&#9997; 12:&#9998; 12: &#9999;"; 
  togglePHP("$s <br/>" . htmlspecialchars($s),'thshvk','+'); 

$A=array(0=>array("C1"=>1,'C2'=>2, 'C3'=>3),1=>array("C1"=>'A','C2'=>'B', 'C3'=>'C')) ; 
TableVK($A,array('AddRow'=>1)); 
function TableVK($A, $O=''){ if(isset($O['id'])) $tid=$O['id']; else  $tid='testtable'; 
$th='';  $tr=''; $i=0; 

$toggleB = "<button id=$tid-c onclick=\"ToggleBy('hideable','$tid-c','n')\">+</button>"; 
foreach($A as $k0=>$v0){$td=''; $i++; $j=0; 
   foreach($v0 as $k=>$v) { 
       if($j=='0') {$td .= "<td>$k0 <button name=hideable style='display:none;' onclick=\"DeleteBy('$tid-$i','id',1)\">&#9747;</button></td>"; if($k0=='0') $th .= "<th>$toggleB</th>";  }  
       $j++; 
       $td .= "<td id=$tid-$i-$j class=$tid-$j>$v</td>";  
        if($k0=='0') {$th .= "<th id=$tid-0-$j class=$tid-$j sortable='true'>$k <button name=hideable style='display:none;' onclick=\"DeleteBy('$tid-$j','c',1)\">&#9747;</button> </th>"; }
   }
   $tr .= "<tr id=$tid-$i>$td</td>"; 
}

echo "<table border=1 id=$tid><tr>$th</tr>$tr</table>"; 
}
?>
