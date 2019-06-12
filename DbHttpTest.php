<?php
use PHPUnit\Framework\TestCase;

include_once ('functions.php');

class DbHttpTest extends TestCase
{
    var $c = null;

    private function connect() {
        if(!$this->c) {
            $this->c = mysqli_connect('mysql', 'root', 'root', 'foo');
            if(!$this->c) die("Error connecting to DB!");
        }
    }

    public function testPushAndPop()
    {
        $stack = [];
        $this->assertSame(0, count($stack));

        array_push($stack, 'foo');
        $this->assertSame('foo', $stack[count($stack)-1]);
        $this->assertSame(1, count($stack));

        $this->assertSame('foo', array_pop($stack));
        $this->assertSame(0, count($stack));
    }

    public function testSingleChunkOutput() {

        ob_start();
        dump_chunk('abcd');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame("4\r\nabcd\r\n", $output, 'Single chunk encoding');
    }

    public function testEndChunkOutput() {

        ob_start();
        end_chunk();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame("0\r\n\r\n", $output, 'End chunk encoding');
    }

    public function testChunkExampleFromWiki() {

        ob_start();
        dump_chunk('Wiki');
        dump_chunk('pedia');
        dump_chunk(' in');
        dump_chunk('chunks.');
        end_chunk();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame("4\r\nWiki\r\n5\r\npedia\r\n3\r\n in\r\n7\r\nchunks.\r\n0\r\n\r\n", $output, 'Whole message encoding');
    }

    public function testCorrectHttpHeader() {

        $url = 'http://localhost:80/dbs/foo/tables/source';
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        //curl_setopt($this->ch, CURLOPT_VERBOSE, 1);


        $response = curl_exec($this->ch);

        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        //$body = substr($response, $header_size);


        $this->assertNotFalse(strpos($header, 'Transfer-encoding: chunked'), 'Chunk encoding has needed header');
    }

    public function testDbOutput() {

        $this->connect();

        $sql = "delete from source";
        $result = mysqli_query($this->c, $sql);
        $this->assertNotFalse($result);

        $sql = "INSERT INTO source (a,b,c) VALUES (64789,1,4),(916809,0,4),(599182,1,2)";
        $res = mysqli_query($this->c, $sql);

        ob_start();
        outputFromDb($this->c);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($output, "23\r\n64789;1;4\r\n916809;0;4\r\n599182;1;2\r\n\r\n0\r\n\r\n", '3 DB records encoding');
    }

    public function testDbPrepare() {

        $this->connect();

        $sql = "delete from source";
        $result = mysqli_query($this->c, $sql);
        $this->assertNotFalse($result);

        prepare($this->c);
        $sql = "select count(*) from source";
        $result = mysqli_query($this->c, $sql);
        $row = mysqli_fetch_row($result);
        $this->assertEquals(1000000, $row[0], 'Source table should contain exactly 1 million records');

    }

    public function testDbRecords()
    {

        $this->connect();

        $records = 8;
        $sql     = "select * from source ORDER BY rand() LIMIT 0," . $records;
        $result  = mysqli_query($this->c, $sql);
        $i       = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $i++;
            $tmp = $row['a'];
            while ($tmp >= 3) $tmp -= 3;
            $this->assertEquals($row['b'], $tmp, 'Data in the column, should be ' . $row['a'] . ' % 3');

            $tmp = $row['a'];
            while ($tmp >= 5) $tmp -= 5;
            $this->assertEquals($row['c'], $tmp, 'Data in the column, should be ' . $row['a'] . ' % 5');
        }
        $this->assertEquals($records, $i, 'Should receive ' . $records . ' records');
    }

}