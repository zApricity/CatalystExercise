<?php

$u = NULL;
$p = NULL;
$h = NULL;
$ufile = NULL;
$create_table = false;
$dry_run = false;
$help = false;

$optind = null;
$options = array_slice($argv, $optind);

foreach($options as $id => $value){
    switch($value){
        case "-u":
        $u=$options[$id+1];
        break;
        case "-p":
        $p=$options[$id+1];
        break;
        case "-h":
        $h=$options[$id+1];
        break;
        case "--file":
        $ufile=$options[$id+1];
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

if($h == NULL || $u == NULL || $h == NULL){
    echo "Please use -u - MySQL username, -p - MySQL password, -h - MySQL host, to log into the database";
    exit(4);
}else{
    $conn = new mysqli($h, $u, $p, "catalyst");
}
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    echo "Please use -u - MySQL username, -p - MySQL password, -h - MySQL host, to log into the database";
    exit(4);
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
    "-h - MySQL host", "\n";;
}

if($create_table){
    createTable($conn);
}

if(!$help && $ufile != NULL){
    parseCSV($conn, $dry_run, $ufile);
}


function parseCSV($conn, $dry_run, $ufile) {


    if($ufile==NULL){
        echo "Please specify an input file with --file";
        exit(4);
    }
    $file=fopen('C:/xampp/htdocs/CatalystPractical/'.$ufile.'.csv', 'r');
    
    $header=fgetcsv($file);
    
    $escapedHeader=[];
    
    foreach ($header as $key => $value) {
        $lheader=strtolower($value);
        $escapedItem=preg_replace('/[^a-z]/', '', $lheader);
        
        array_push($escapedHeader, $escapedItem);
    }
    while($columns=fgetcsv($file))
    {
        $validemail = TRUE;
        if($columns[0]=="")
        {
            continue;
        }
        
        $data=array_combine($escapedHeader, $columns);
        
        foreach ($data as $key => &$value)
        {
            $regexp = "/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/";
            switch($key){
                case "email":
                if(preg_match($regexp, $value)){
                    $validemail = TRUE;
                }else{
                    $validemail = FALSE;
                }
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
        
        $uname=$data['name'];
        $usurname=$data['surname'];
        $uemail=$data['email'];

        if($dry_run){
            echo "$uname, $usurname, $uemail", "\n";
            if(!$validemail){
                fwrite( STDOUT, "Invalid e-mail detected for user " .$uname. ", entry will not be added to database! \n" );
            }
        }

        if(!$validemail && !$dry_run){
            fwrite( STDOUT, "Invalid e-mail detected for user " .$uname. ", entry not added to database! \n" );
        }

        if(!$dry_run && $validemail){
            $sql = "INSERT INTO users (name, surname, email)
            VALUES ('".mysql_real_escape_string($uname)."', '".mysql_real_escape_string($usurname)."', '$uemail')";
            
            if ($conn->query($sql) === TRUE) {
                echo "User, " .$uname. " created successfully \n";
            } else {
                echo "Error: " .$conn->error. "\n";
            }
        }
    }
}

function createTable($conn) {
    $sql = "DROP TABLE IF EXISTS Users";

    if ($conn->query($sql) === TRUE) {
        echo "Table Users dropped successfully", "\n";
    } else {
        echo "Error dropping table: " . $conn->error, "\n";;
    }
    
    $sql = "CREATE TABLE Users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        name VARCHAR(30) NOT NULL,
        surname VARCHAR(30) NOT NULL,
        email VARCHAR(50) UNIQUE KEY
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo "Table Users created successfully", "\n";;
        } else {
            echo "Error creating table: " . $conn->error, "\n";;
        }
}


?>