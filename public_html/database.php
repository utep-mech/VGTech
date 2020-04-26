<?php

$servername = "localhost"; $username = "vkumar"; $password = "Re=2300"; $dbname = "USERS";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); } 

$sql = "UPDATE USERS SET LastName='jvja' WHERE userid='test1'";
$result = mysqli_query($conn, 'SELECT * FROM USERS');

while($row2 = mysqli_fetch_array($result)) $rowA[]=$row2; 
echo '<pre>';
print_r($rowA);
echo '</pre>';



echo "<table border='1'>
<tr>
<th>Firstname</th>
<th>Lastname</th>
</tr>";

while($row = mysqli_fetch_array($result))
{
echo "<tr>";
echo "<td>" .  $row['userid'] . $row['FirstName'] . "</td>";
echo "<td>" . $row['LastName'] . "</td>";
echo "</tr>";
}
echo "</table>";



if ($conn->query($sql) === TRUE) { echo "Record updated successfully";
} else { echo "Error updating record: " . $conn->error; }

$conn->close();
?>
