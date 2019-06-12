<?php

function dump_chunk($chunk) {
    printf("%x\r\n%s\r\n", strlen($chunk), $chunk);
    flush();
}
function end_chunk() {
    echo "0\r\n\r\n";
    flush();
}

function prepare($c) {
    for($i = 0; $i<1000; $i++) {
        $num = rand(0, 1000000);
        $sql = "INSERT INTO source (a,b,c) VALUES (".$num.",".($num % 3).",".($num % 5).")";
        for($k = 1; $k<1000; $k++) {
            $num = rand(0, 1000000);
            $sql .= ",(".$num.",".($num % 3).",".($num % 5).")";
        }
        $res = mysqli_query($c, $sql);
    }
}

function outputFromDb(&$c) {

    $sql = "select * from source"; // LIMIT 0, 10000
    $res = mysqli_query($c, $sql);

    $out = '';
    $i = 0;
    while($row = mysqli_fetch_assoc($res)) {
        $out .= implode(';', $row)."\r\n";
        if(++$i==1000) {
            dump_chunk($out);
            $out = '';
            $i = 0;
        }
    }
    if($out) dump_chunk($out);

    end_chunk();
}