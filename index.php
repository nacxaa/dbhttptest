<?php

include_once ('functions.php');

@apache_setenv('no-gzip', 1);
@ini_set('display_errors', 1);
@ini_set('error_reporting', E_ALL);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);


$c = mysqli_connect('mysql', 'root', 'root', 'foo');
if(!$c) die('Error connecting to DB!');

if($_SERVER['REQUEST_URI'] == '/dbs/foo/tables/source') {

    header("Transfer-encoding: chunked");
    outputFromDb($c);

} elseif($_SERVER['REQUEST_URI'] == '/prepare') {
    prepare($c);
    echo 'DB prepare done.';
}
else {
    echo "Hi there! :)";
}