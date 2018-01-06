<?php
$dbConn = mysqli_connect("localhost", "michael", "17701788", "autoservice");
if(!$dbConn) {
    exit("Failed to connect to database " . mysqli_connect_error());
}

$file=fopen('users.csv', 'r');

$header=fgetcsv($file);

$escapedHeader=[];

foreach ($header as $key => $value) {
    $lheader=strtolower($value);
    $escapedItem=preg_replace('/[^a-z]/', '', $lheader);

    array_push($escapedHeader, $escapedItem);

    
}

while($columns=fgetcsv($file))
{

   if($columns[0]=="")
   {
       continue;
   }

   $data=array_combine($escapedHeader, $columns);

   foreach ($data as $key => &$value)
   {
       switch($key){
           case "email":
            $value=preg_replace('/(?![[:alnum:]]|@|-|_|\.)./', '', $value);
           break;
           case "name":
            $value=trim($value, " ");
            $value=preg_replace('/(?|!|@|-|_|\.)./', '', $value);
            $value=strtolower($value);
            $value=ucfirst($value);
           break;
           case "surname":
            $value=trim($value, " ");
            $value=preg_replace('/(?|!|@|-|_|\.)./', '', $value);
            $value=strtolower($value);
            $value=ucfirst($value);
           break;
           default:
            echo "Unknown column!";
       }
       
   }


   $name=$data['name'];
   $surname=$data['surname'];
   $email=$data['email'];

   echo "$name, $surname, $email ";


}

?>