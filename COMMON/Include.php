<?php
//------------------------
function pa($v) { echo '<pre>'; print_r($v); echo '</pre>'; }
//------------------------
function ht($s,$c='yellow',$flag=1,$t='') {if($flag) return "<span title=$t style='background-color:$c;'>$s</span>"; else return $s; }
//------------------------
function ServerInfo($v) {
 if($v==1) print_r($_POST); if($v==2) print_r($_GET); if($v==3) print_r($_SESSION); if($v==4) print_r($_SERVER); if($v==5) phpinfo();
}

//------------------------
function togglePHP($s,$id, $O='+', $ret='echo',$bmsg='',$amsg='') { $str="<button id=B$id>$O</button>";
  if($O=='+') $str .= "<span id=$id style='display:none'>$s</span>";
  if($O=='-') $str .= "<span id=$id>$s</span>";
  $str = "$bmsg $str $amsg";
$str .= <<<ENDB
<script>
   $('#B$id').click(function() { $('#$id').toggle(); if($('#B$id').text()=='-') $('#B$id').text('+'); else $('#B$id').text('-');});
</script>
ENDB;
  if($ret == 'str') return $str; else echo $str;
}
//------------------------
function SQL2ArrayVK($q, $h, $u, $p, $dbname) {
  $conn = mysqli_connect($h, $u, $p, $dbname); if (!$conn) { die("<p/>failed: " . mysqli_connect_error()); }
  if($result = mysqli_query($conn,$q)) { 
    while ($row = mysqli_fetch_assoc($result)) {if(!isset($A)) {$A[0]=$row; } else {$A[]=$row; } }
  } else die("<p/>Error: " . mysqli_connect_error());
  mysqli_close($conn);
  return $A; 
}
//

function hash_equalsVK($a, $b) {$ret = strlen($a) ^ strlen($b);      $ret |= array_sum(unpack("C*", $a^$b));  return !$ret;     }
function SQLR2A($result) {while ($row = mysqli_fetch_assoc($result)) {if(!isset($TA)) {$TA[0]=$row; } else {$TA[]=$row; } }; return $TA;     }
//------------------------
function SortableTable() {
echo <<<END
<script>
$('th[sortable=true]').click(function(){ $('th[sortable=true]').css('background-color','');  $(this).css('background-color','gray'); 
    var table = $(this).parents('table').eq(0)
    var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
    this.asc = !this.asc
    if (!this.asc){rows = rows.reverse()}
    for (var i = 0; i < rows.length; i++){table.append(rows[i])}
})
function comparer(index) {
    return function(a, b) {         var valA = getCellValue(a, index), valB = getCellValue(b, index)
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB)
    }
}
function getCellValue(row, index){ return $(row).children('td').eq(index).html() }

</script>
END;
}
//----------------------------

?>
