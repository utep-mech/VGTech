<?php


$files = array('3.html'=>'3.html', '2.php'=>'2.php','dimag.php'=>'dimag.php', 'd.js'=>'d.js', 
  'IO.php'=>"$COMMON/CLASSES/IO.php", 'ParameterLists.php'=>"$COMMON/CLASSES/ParameterLists.php", 
  'Layout'=>"$HOME/Layout/bodymain.xml"
  ); 


echo "<br/>Edit:"; 

foreach ($files as $n=>$f) {
  echo "<button onclick=\"dimag({'EditFile':'$f', 'editor':'textarea','outputid':'middle'});\">$n</button>";
}

?>

