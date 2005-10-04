<?php
if (!$include_directory) require_once('../../../include-locations.inc');

require_once("PHPUnit.php");

require_once($include_directory."classes/File/fixedWidthParser.php");

Class FixedWidthParserTest extends PHPUnit_TestCase {
     function FixedWidthParserTest( $name = "FixedWidthParserTest" ) {
            $this->PHPUnit_TestCase( $name );
     }

    function setUp()
    {
        global $include_directory;
        $this->parser=new fixedWidthParser;
        $this->standardTestFile=$include_directory."classes/File/tests/testing/fixed_width_standard_test.txt";
        $this->multiRecordTestFile=$include_directory."classes/File/tests/testing/fixed_width_multirecord_test.txt";
    }

    function teardown()
    {
        unset($this->parser);
    }


    function test_true() {
        $this->assertTrue(true, "This should never fail");
    }

    function test_fixedWidthStandardMap($map=false,$filename=false, $result=false) {
        if (!$map AND !$filename AND !$result) {
            $map=array();
            $map[]=array( 'name' => 'account_number', 'start'=>1, 'end'=>9, 'type'=>'string');
            $map[]=array( 'name' => 'account_name', 'start'=>10, 'end'=>25, 'type'=>'string');
            $map[]=array( 'name' => 'account_code', 'start'=>26, 'end'=>26, 'type'=>'string');
            
            $filename=$this->standardTestFile;
            $result=array('account_number'=>1228, 'account_name'=>'BOB', 'account_code'=>'C');
        }
        // Instantiate Class
        $objParser = $this->parser;
        
        // Use Headers as field keys
        $objParser->useHeaders( true );
    
        $objParser->SetFieldFormat($map);
        $objParser->parseFile($filename);
        $data=$objParser->GetRecords();
        $this->assertTrue($data, "Failed to retrieve any records for $filename using map $map");
        if ($data) {
            $first=current($data);
            foreach ($result as $rkey=>$rval) {
                $this->assertTrue($first[$rkey]==$rval, "Failed to match $rkey to $rval, got {$first[$rkey]} instead.");
            }
        }
        return $data;
    }

    function test_fixedWidthSecondLine($map=false, $filename=false, $result=false) {
        if (!$result) { $result=array('account_number'=>222020011, 'account_name'=>"JOE'SACCOUNTOFDO", 'account_code'=>'S'); }
        $data=$this->test_fixedWidthStandardMap();
        reset($data);
        next($data);
        $rec=current($data);
        if ($rec) {
            foreach ($result as $rkey=>$rval) {
                $this->assertTrue($rec[$rkey]==$rval, "Failed to match $rkey to $rval, got {$rec[$rkey]} instead.");
            }
        }
        return $data;
    }

    function test_fixedWidthMultiRecordMap($mappings=false,$record_identifier=false, $filename=false, $results=false) {
        if (!$mappings AND !$filename AND !$results AND !$record_identifier) {
            $map=array();
            $map[]=array( 'name' => 'account_number', 'start'=>1, 'end'=>3, 'type'=>'string');
            $map[]=array( 'name' => 'record_type', 'start'=>5, 'end'=>6, 'type'=>'string');
            $map[]=array( 'name' => 'account_code', 'start'=>7, 'end'=>11, 'type'=>'string');
            $map[]=array( 'name' => 'account_name', 'start'=>12, 'end'=>27, 'type'=>'string');
            $map[]=array( 'name' => 'account_total', 'start'=>26, 'end'=>33, 'type'=>'string');
            $map[]=array( 'name' => 'account_state', 'start'=>35, 'end'=>36, 'type'=>'string');
            $mappings['N']=$map;

            $map=array();
            $map[]=array( 'name' => 'account_number', 'start'=>1, 'end'=>3, 'type'=>'string');
            $map[]=array( 'name' => 'record_type', 'start'=>5, 'end'=>6, 'type'=>'string');
            $map[]=array( 'name' => 'transaction_type', 'start'=>7, 'end'=>10, 'type'=>'string');
            $map[]=array( 'name' => 'transaction_qty', 'start'=>11, 'end'=>13, 'type'=>'string');
            $map[]=array( 'name' => 'transaction_price', 'start'=>17, 'end'=>22, 'type'=>'string');
            $map[]=array( 'name' => 'transaction_instrument', 'start'=>23, 'end'=>27, 'type'=>'string');
            $mappings['P1']=$map;
            
            $record_identifier=array( 'name' => 'record_type', 'start'=>5, 'end'=>6, 'type'=>'string');
            $filename=$this->multiRecordTestFile;
            $results[]=array('account_number'=>'T02', 'record_type'=>'N', 'account_code'=>10022, 'account_name'=>'TEST ACCOUNT', 'account_state'=>'TX', 'account_total'=>200.32);
            $results[]=array('account_number'=>'T02', 'record_type'=>'P1', 'transaction_type'=>'SELL', 'transaction_qty'=>120, 'transaction_instrument'=>'DTX', 'transaction_price'=>2.20);
            $results[]=array('account_number'=>'T02', 'record_type'=>'P1', 'transaction_type'=>'BUY', 'transaction_qty'=>9, 'transaction_instrument'=>'IBM', 'transaction_price'=>12.801);
            $results[]=array('account_number'=>'T03', 'record_type'=>'N', 'account_code'=>10023, 'account_name'=>'SUPERBALL TEST', 'account_state'=>'NY', 'account_total'=>98.20);
            $results[]=array('account_number'=>'T03', 'record_type'=>'P1', 'transaction_type'=>'TEST', 'transaction_qty'=>84, 'transaction_instrument'=>'TY05', 'transaction_price'=>6.62);
        }
        // Instantiate Class
        $objParser = $this->parser;
        
        // Use Headers as field keys
        $objParser->useHeaders( true );
    
        $objParser->SetRecordIdentifier($record_identifier);
        $objParser->SetRecordFormats($mappings);
        $objParser->SetFieldFormat($map);
        $objParser->parseFile($filename);
        $data=$objParser->GetRecords();

        $this->assertTrue($data, "Failed to retrieve any records for $filename using map $mappings");
        if ($data) {
//            echo '<pre>'; print_r($data); echo '</pre>';
            foreach ($results as $result) {
                $first=current($data);
                foreach ($result as $rkey=>$rval) {
                    $this->assertTrue($first[$rkey]==$rval, "Failed to match $rkey to $rval, got {$first[$rkey]} instead.");
                }
                next($data);
            }
        }
        return $data;
    }
}
 ?>