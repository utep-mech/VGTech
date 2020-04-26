<?php 

function fpRead($i, &$v, $n='', &$vp='', $flag='0', $flag2="1",$Options='') {
        global $FormEditID; 
//------------------GET POSTED VALUE------------------
        if($i=="PV" && isset($_POST["$n"])) { 
		//$v=stripslashes($_POST["$n"]); 
		if(get_magic_quotes_gpc()) $v=stripslashes($_POST["$n"]); else $v=$_POST["$n"]; 
	return; } 
        if($i == "PVTC") { if(isset($_POST[$n.'status'])) $vp=1; else $vp=0; 
		//$v=stripslashes($_POST[$n.'value']); 
                if(get_magic_quotes_gpc()) $v=stripslashes($_POST[$n.'value']); else $v=$_POST[$n.'value']; 
	return;}
        if($i=="PVTA6") {$n="Hidden$n"; 
                if(isset($_POST["$n"])) { if(get_magic_quotes_gpc()) $v=stripslashes($_POST["$n"]); else $v=$_POST["$n"]; } 
	return; }
//-----------------------------------------------------
}

function fp($i, $v='', $n='', $vp='', $flag='0', $flag2="1",$Options='') {
        global $FormEditID; 

        $EditorUpload="filebrowserBrowseUrl : '/Softwares/pdw_file_browser/index.php?editor=ckeditor'";
        $EditorUpload .=','.  "filebrowserImageBrowseUrl : '/Softwares/pdw_file_browser/index.php?editor=ckeditor&filter=image'";
        $EditorUpload .=','.  "filebrowserFlashBrowseUrl : '/Softwares/pdw_file_browser/index.php?editor=ckeditor&filter=flash'";

        if(in_array($i, array("T", "T1")) && $flag) { echo "<input type='hidden' value='$v' name=$n id=$n>\n"; return; }

//------------------GET POSTED VALUE------------------
        if($i=="PV" && isset($_POST["$n"])) { 
		//$v=stripslashes($_POST["$n"]); 
		if(get_magic_quotes_gpc()) $v=stripslashes($_POST["$n"]); else $v=$_POST["$n"]; 
	return; } 
        if($i == "PVTC") { if(isset($_POST[$n.'status'])) $vp=1; else $vp=0; 
		//$v=stripslashes($_POST[$n.'value']); 
                if(get_magic_quotes_gpc()) $v=stripslashes($_POST[$n.'value']); else $v=$_POST[$n.'value']; 
	return;}
        if($i=="PVTA6") {$n="Hidden$n"; 
                if(isset($_POST["$n"])) { if(get_magic_quotes_gpc()) $v=stripslashes($_POST["$n"]); else $v=$_POST["$n"]; } 
	return; }
//-----------------------------------------------------

        if($i == "HideUnhide") { $mode='table'; if($flag2 !=1) $mode=$flag2;
		if($flag) { 
    			echo "$vp<input type='button' id='Calling$n' value='-' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
			echo "<div id='Hidden$n' name='Hidden$n' style='display:inline;'>$v</div>";
		} else { 
    			echo "$vp<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
			echo "<div id='Hidden$n' name='Hidden$n' style='display:none;'>$v</div>";
		} 
	}
        if($i == "P") {echo " <form method='post' action='$v'>\n"; }
        if($i == "Post") {echo " <form method='post' action='$v'>"; }
        if($i == "PostData") {echo " <form method='post' action='$v' enctype='multipart/form-data'> "; }
        if($i == "TA") echo "$vp<br><textarea NAME=$n id=$n ROWS=4 COLS=100>".$v."</textarea>\n";
        if($i == "TAHideUnhide") {
		if($flag) { 
    			echo "$vp<input type='button' id='Calling$n' value='-' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
			echo "<div id='Hidden$n' name='Hidden$n' style='display:inline;'>"; 
			echo "<br><textarea NAME=$n id=$n ROWS=20 COLS=100>".$v."</textarea>\n";
			echo "</div>";
		} else { 
    			echo "$vp<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
			echo "<div id='Hidden$n' name='Hidden$n' style='display:none;'>"; 
			echo "<br><textarea NAME=$n id=$n ROWS=20 COLS=100>".$v."</textarea>\n";
			echo "</div>";
		} 
        } 
        if($i == "TA3") echo "$vp<br><textarea class='ckeditor' cols='80' id='$n' name='$n' rows='10'>$v</textarea> ";
        if($i == "TA4") { 
                echo htext($vp)."<br><textarea cols='10' id='$n' name='$n' rows='1'>$v</textarea><br>";
                //echo " <script> CKEDITOR.replace( '$n', {toolbar: " . ToolBar() ."});  </script>";
                echo " <script> CKEDITOR.replace( '$n', {toolbar: 'Full', $EditorUpload });  </script>";
        }
        if($i == "TA5") { echo "<textarea cols='10' id='$n' name='$n' rows='1' style='display:none;'>$v</textarea>"; }
        if($i == "TA6") { 
		echo "<input type='hidden' size='1' value='$v' name=$n id=$n> "; 
		if($flag=='Full') echo "$vp<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideTAByID('$n','Full')\" />";
		else echo "$vp<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideTAByID('$n','Basic')\" />";
                echo "<textarea cols='10' id='Hidden$n' name='Hidden$n' rows='1' style='display:none;'>$v</textarea>";
        }
        if($i == "TA7") { 
		echo "<input type='hidden' size='1' value='$v' name=$n id=$n> "; 
		echo "$vp<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideTAByID('$n','Full')\" />";
                echo "<textarea cols='10' id='Hidden$n' name='Hidden$n' rows='1' style='display:none;'>$v</textarea>";
        }
        if($i == "TA17") { $ToolBar='Full'; $Type='editor'; 
		if(isset($Options['Type'])) $Type=$Options['Type']; 
		if(isset($Options['ToolBar'])) $ToolBar=$Options['ToolBar']; 
		echo "$vp<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideTAByID2('$n','$ToolBar','$Type')\" />";
                echo "<textarea cols='10' id='$n' name='$n' rows='1' style='display:none;'>$v</textarea>";
        }
        if($i == "TA8") { 
			$stat1='+'; $stat2='none'; 
		        if($Options['Status']=="Opened") {$stat1='-'; $stat2='table'; }
    			echo "$vp<input type='button' id='Calling$n' value='$stat1' onclick=\"HideUnhideDivByID('$n','$mode')\" />";
			echo "<div id='Hidden$n' name='Hidden$n' style='display:$stat2;'>"; 
			echo "<br><textarea NAME=$n id=$n ROWS=4 COLS=100>".$v."</textarea>\n";
			$ToolBarinForm="[['Strike','Subscript','Superscript', '-', 'NumberedList', 'BulletedList','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', '-', 'Link', 'Unlink' ], ['Font','FontSize', 'TextColor', 'BGColor','RemoveFormat' ], ['equation','SpecialChar','Image','Table','HorizontalRule','Source','Maximize','Iframe'] ]"; 
	  if($Options['EditorType']=="ToolBarWidth") echo " <script> CKEDITOR.replace( '$n', {toolbar:'Basic', width: '550px' });  </script>";
	  elseif($Options['EditorType']=="ToolBar") echo " <script> CKEDITOR.replace( '$n', {toolbar:$ToolBarinForm});  </script>";
	  elseif($Options['EditorType']=="Basic") echo " <script> CKEDITOR.replace( '$n', {toolbar:'Basic'});  </script>";
	  else echo " <script> CKEDITOR.replace( '$n', {toolbar:'Full', $EditorUpload});  </script>";
			echo "</div>";
        } 
        if($i == "ShowSpanish") { if($n!="SpanishDescription") return; 
		echo "<input type='Hidden' size='50' value='0' name=$n id=$n> "; 
  		echo "<div id='SpanishText'></div>";
		echo "<br><input type='button' id='Calling$n' value='Show Spanish' onclick=\"ShowSpanishByFile()\" />";
		$Display1='Q'; $Display2="$vp"; $Display3='@value'; 
echo <<<END
		<script>
		function ShowSpanishByFile() { 
    		$.post("DisplayOnly.php", {f: '$v', Display1: '$Display1', Display2:'$Display2', Display3:'$Display3'},
      			function(data) { 
			//data=escapeHtml(data);
			//alert(data);
			document.getElementById('SpanishText').innerHTML='<u>Computer-translated Spanish</u>:<br>' + data ; 
			document.getElementById('$n').value=1; 
		})
		}
		</script>
END;


        }
        if($i == "T") { if($flag2==0) echo "($vp) $v, "; else echo "$vp<input type='text' value='$v' name=$n id=$n> "; }
        if($i == "T1") { if($flag2==0) echo "($vp) $v, "; else echo "$vp<input type='text' size='3' value='$v' name=$n id=$n> "; }
        if($i == "TCTA5") { $n0=$n;
                  $n=$n0.'status';
                  if($vp && $flag2 !=3) echo "<input type='CHECKBOX' value='1' name=$n id=$n checked>\n";
                        else echo "<input type='CHECKBOX' value='1' name=$n id=$n>\n";
                echo "($flag)"; $n=$n0.'value';

		//echo "<input type='text' size='20' value='$v' name=$n id=$n> "; 
                echo "<input type='text' size='20' value='".htmlspecialchars($v)."' name=$n id=$n> ";
		//echo "<textarea cols='20' rows='1' name=$n id=$n>$v</textarea>"; 
		echo "<input type='button' id='Calling$n' value='+' onclick=\"HideUnhideTAByID('$n','Full')\" />";
                echo "<textarea cols='10' id='Hidden$n' name='Hidden$n' rows='1' style='display:none;'>$v</textarea>";
        }
        if($i == "Tb") echo "$vp<input type='text' value='$v' name=$n id=$n> <br>\n";
        if($i == "TC") { $n0=$n; 
		if($flag2 == 0) { if($vp) echo "<b>($flag) $v, </b>"; else echo "($flag) $v, ";
		} elseif($flag2 == 2) { echo "($flag) $v, ";
		} else {
                  $n=$n0.'status'; 
		  if($vp && $flag2 !=3) echo "<input type='CHECKBOX' value='1' name=$n id=$n checked>\n";
		  	else echo "<input type='CHECKBOX' value='1' name=$n id=$n>\n";
		  echo "($flag)";
                  $n=$n0.'value'; 
                  if($flag2==3) echo "$v"; else echo "<input type='text' value='$v' name=$n id=$n> ";
 		}
	}
        if($i == "C") { echo "$vp<input type='CHECKBOX' value='$v' name=$n id=$n>\n"; }
        if($i == "C1") { if($v==1) echo "<input type='CHECKBOX' value='$v' name=$n id=$n checked>\n"; else echo "<input type='CHECKBOX' value='$v' name=$n id=$n>\n";}
        if($i == "CC") echo "<input type='CHECKBOX' value='$v' name=$n id=$n checked>$vp\n";
        if($i == "R") {
                  if("$v"=="$vp") echo "<input type='radio' value='$vp' name=$n id=$n checked>$vp, \n";
                  else   echo "<input type='radio' value='$vp' name=$n id=$n>$vp, \n";
         }
        if($i == "H") echo "<input type='hidden' value='$v' name=$n id=$n>\n";
        if($i == "HH") echo "<input type='hidden' value='$v' name=$n id='$vp'>\n";
        if($i == "Sb") echo "<br><input type='submit' name='submit' value='Submit'><br> ";
        if($i == "Snb") echo "<input type='submit' name='submit' value='Submit'> ";
        if($i == "Sn2") echo "<input type='submit' name='submit' value='$v'> ";
        if($i == "S") echo "<br><input type='submit' name='submit' value='Submit'><br> </form>";
        if($i == "Button") {echo "<input type='submit' name='$n' value='$v'> "; $FormEditID="$n";}
        if($i == "Finish") echo "</form>";

	if($i == "Select") { 
		echo "<select name='$n'>";
		foreach($v as $itmp=>$vtmp) { 
			if("$vtmp"=="$vp") $selected="selected"; else $selected="";
			echo "<option value='$vtmp' $selected>$vtmp</option>"; 
		}
		echo "</select>";
	}
        if($i == "B") {return "<input type='button' value='$v'>"; }
	echo "\n";
}
function fpRv($i, $v='', $n='', $vp='', $flag='0', $flag2="1") {
        if($i == "B") {return "<input type='button' value='$v'>"; }
}

?>
