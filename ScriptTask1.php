<?php
$dbConn = mysqli_connect("localhost", "michael", "17701788", "autoservice");
if(!$dbConn) {
    exit("Failed to connect to database " . mysqli_connect_error());
}

$u;
$p;
$h;
$file;
$create_table = false;
$dry_run = false;
$help = false;

$optind = null;
$options = array_slice($argv, $optind);
var_dump($options);

foreach($options as $id => $value){
    switch($value){
        case "-u":
        $u=$options[$id+1];
        echo $u;
        break;
        case "-p":
        $p=$options[$id+1];
        echo $p;
        break;
        case "-h":
        $h=$options[$id+1];
        echo $h;
        break;
        case "--file":
        $file=$options[$id+1];
        echo $file;
        break;
        case "--create_table":
        $create_table=true;
        break;
        case "--dry_run":
        $dry_run=true;
        break;
        case "--help":
        $help=true;
        break;
        default:
    }
}

if($help){
    echo "--file [csv file name] - this is the name of the CSV to be parsed", "\n",
    "--create_table - this will cause the MySQL users table to be built (and no further
    action will be taken)", "\n",
    "--dry_run - this will be used with the --file directive in the instance that we want
    to run the script but not insert into the DB. All other functions will be executed,
    but the database won't be altered.", "\n",
    "-u - MySQL username", "\n",
    "-p - MySQL password", "\n",
    "-h - MySQL host";
}

if($dry_run){
    parseCSV();
}



function parseCSV() {
    $file=fopen('C:\xampp\htdocs\CatalystPractical\users.csv', 'r');
    
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
                $value=preg_replace('/(?|!|@|-|_|\.)./', '', $value);
                $value=preg_replace('/\s*/m', '', $value);
                $value=strtolower($value);
                $value=ucfirst($value);
                break;
                case "surname":
                $value=preg_replace('/\s*/m', '', $value);
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
        
        echo "$name, $surname, $email", "\n";
    }
}

function createTable() {
    $sql = "DROP TABLE IF EXISTS Users";

    if ($conn->query($sql) === TRUE) {
        echo "Table Users dropped successfully";
    } else {
        echo "Error dropping table: " . $conn->error;
    }
    
    $sql = "CREATE TABLE Users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        name VARCHAR(30) NOT NULL,
        surname VARCHAR(30) NOT NULL,
        UNIQUE KEY email VARCHAR(50),
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo "Table Users created successfully";
        } else {
            echo "Error creating table: " . $conn->error;
        }
}


?>