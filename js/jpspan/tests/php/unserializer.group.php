<?php
/**
* @version $Id: unserializer.group.php,v 1.1 2005/04/11 19:50:50 gpowers Exp $
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
class UnserializerGroupTest extends GroupTest {

    function UnserializerGroupTest() {
        $this->GroupTest('UnserializerGroupTest');
        $this->addTestFile('unserializer.test.php');
        $this->addTestFile('unserializer_php.test.php');
        $this->addTestFile('unserializer_xml.test.php');
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new UnserializerGroupTest();
    $test->run(new HtmlReporter());
}
?>
