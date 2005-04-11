<?php
/**
* @version $Id: serializer.group.php,v 1.1 2005/04/11 19:50:50 gpowers Exp $
* @package JPSpan
* @subpackage Tests
*/

/**
* Init
*/
require_once('../config.php');

/**
* @package JPSpan
* @subpackage Tests
*/
class SerializerGroupTest extends GroupTest {

    function SerializerGroupTest() {
        $this->GroupTest('SerializerGroupTest');
        $this->addTestFile('serializer.test.php');
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new SerializerGroupTest();
    $test->run(new HtmlReporter());
}
?>
