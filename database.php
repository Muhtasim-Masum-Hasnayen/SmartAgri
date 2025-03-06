<?php

$server="localhost";
$user="root";
$pass="";
$name="farming_management";
$conn="";

$conn=mysqli_connect($server,$user,$pass,$name);

if($conn){
    
    #echo"you are connected";

}
else 
{
    
    #echo"not connected";

  
}





?>