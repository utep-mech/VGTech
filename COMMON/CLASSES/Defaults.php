<?php
namespace Defaults {
	 function DefaultXML() {  
           return '
 <ParameterList OutputID="middle" Name="Research">
    <Parameter Name="Intro" File="File.html" HighLight="Hilight.html" />  
    <ParameterList Name="Member">
      <Parameter Name="Member1" File="m1.html" HighLight="Hilight.html" />  
      <Parameter Name="M2" File="m1.html" HighLight="Hilight.html" />  
    </ParameterList>
    <ParameterList Name="Project">  
      <Parameter Name="P1" File="m1.html" HighLight="Hilight.html" />  
      <Parameter Name="P2" File="m1.html" HighLight="Hilight.html" />        
    </ParameterList>
 </ParameterList>
          '; 
       }
 //-------------------------------
 function DefaultHTML() {  
  return '
    <span> </span>
  '; 
 }
 //-------------------------------
 function DefaultJSON() 
 {
  return '
{
 "a": {"Name":"Research"}, 
 "P": [
   {"a":{"Name":"Intro"}, "v":"value"}
 ], 
 "PL":[
   {"a":{"Name":"PL2"}, "P":[{"a":{}}]   }
 ]
}
  '; 
    	
 } 
 //-------------------------------
 function DefaultMD()  { return '{"a": {"Name":"Research"}, "C": [ {"a":{"Name":"Intro"}, "v":"value"}] }'; } 
//-------------------------------
function UserJSON()  {
  return '
   {
    "Email":"","UserID":"","Password":"","FirstName":"", "LastName":"", "Photo":"", 
    "group":"student", "groups":["student", "admin", "guest"], 
    "COURSES":["Math-Grade3","EA2", "EA1"],
    "Extra":["EncryptPassword","Disable"]
  } 
 ';
     
} 
//-----------------------
function DefaultUsers()  {
return '{
     "vkumar":{"UserID":"vkumar","Password":"$1$p4SqwFEe$q8zATOjXI7nfBwxmlwU6//","LastName":"Kumar","FirstName":"V.","group":"superadmin"},
     "vkumar1":{"Email":"vkumar1@mu2com.com","Password":"123","LastName":"V. Kumar1","group":"instructor"},
     "skm":{"Password":"Chandi#123","LastName":"S. Mehra","group":"admin"},
     "rprem":{"Password":"Chandi#123","LastName":"Randheer","group":"admin"},
     "sandeepkumarmehra123@gmail.com":{"Password":"Chandi#123","LastName":"S. Mehra","group":"admin"},
     "rprem31@gmail.com":{"Password":"Chandi#123","LastName":"Randheer","group":"admin"},
     "vkumar2":{"Password":"123","LastName":"V. Kumar2","group":"EA2"}
  }';
}
//-----------------------

}

?>