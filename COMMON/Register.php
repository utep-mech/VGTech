<?php
//echo '................'; pa($_POST);
$id='NewUserID';  $UserDIR="$DATA/USERS"; $LoadPHP = $_POST['send']['LoadPHP']; 
  $CourseList = json_decode(file_get_contents("$DATA/COURSES/Courses.json"),true);
  foreach($CourseList as $k=>$v) {if($v['Type']=='Open') { $CoursesOpen["$k"]=$v['Name']; } }
  //$CoursesOpen = array("EA1"=>"Engineering Analysis I","EA2"=>"EA-II");
  if($_POST['send']['Type']=='Save')  { $uid=$_POST['send']['Value']['UserID']; $uidfile = "$UserDIR/$uid.json"; 
    if(file_exists($uidfile)) {echo "$uid already exists";  return; }
    $U[$uid] = $_POST['send']['Value'];  $passwd=$U[$uid]['Password']; $U[$uid]['Password']=crypt($passwd); file_put_contents("$uidfile",json_encode($U)); 
    echo "Congratulations! Your userid: $uid, password: $passwd <a href=.>Login now</a>";
    return; 
  } 
echo <<<END
<script>function RegisterNewUser(id) {
   var v={}, w=[];     
   $(".C"+id).each(function(){  v[$(this).prop("id")]=$(this).prop("value");  }); 
   v['UserID']=v['Email'];
   var j=0; $(".Courses"+id).each(function(){  if($(this).prop('checked')) {w[j]=$(this).prop("value"); j++;}  }); 
   if(j>0) v.COURSES = w; 
   var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
   //if(! (re.test(v['Email']) )) {alert('Email is invalid '); return; }
   //if(v['Password']=='') {alert('Password cannot be empty'); return;}
    dimag({'middle':'mout-NewUserID','LoadPHP':'$LoadPHP','Users':'NewUser', 'Type':'Save','Value':v}); 
    //alert(JSON.stringify(v));
}
</script>
END;
    $str = "";
    $str .= "<tr><td>Email*</td><td><input id=Email class=C$id size=40 type=text /> (Required)</td></tr>"; 
    $str .= "<tr><td>Userid</td><td><input id=UserID class=C$id placeholder='same as email' size=40 type=text disabled/></td></tr>"; 
    $str .= "<tr><td>Password</td><td><input id=Password  class=C$id size=40 type=text value=".uniqid()." disabled/></td></tr>";
    //echo "<br/>Password:<input id=Password  class=C$id size=20 type=password placeholder='Will be emailed' disabled/>";
    $str .= "<tr><td> LastName</td><td><input id=LastName  class=C$id size=40 type=text/></td></tr>";
    $str .= "<tr><td> FirstName</td><td><input id=FirstName  class=C$id size=40 type=text/></td></tr>";
    $str .= "<tr><td> School Name</td><td><input id=SchoolName class=C$id size=40 type=text/></td></tr>";
    $str .= "<tr><td> Address</td><td><input id=Address class=C$id size=40 type=text/></td></tr>";
    $str .= '<tr><td>Subscribe Courses</td><td> ';
    foreach($CoursesOpen as $k=>$v) { $str .= "<input class=Courses$id type=checkbox value=$k /> $v , "; }
    $str .= '</td></tr>';
    echo "<table>$str</table>"; 
    echo "<br/> <button onclick=\"RegisterNewUser('$id'); \">Submit</button>";

if(!$admin) return; 
if(!isset($_POST['send']['Users'])) {
 echo "<button onclick=\"dimag({'outputid':'$id','LoadPHP':'Users.php','Users':'ListUsers'})\">List</button>";
 echo "<button onclick=\"dimag({'outputid':'$id','LoadPHP':'Users.php','Users':'NewUser'})\">New User</button>";
 echo "<span id=$id></span><span id=mout-$id></span>"; 
 return; 
} 

return; 

//--------------------------------------
?> 
