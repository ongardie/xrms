<?php
/**
* @version $Id: listener.group.php,v 1.1 2005/04/11 19:50:50 gpowers Exp $
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
class ListenerGroupTest extends GroupTest {

    function ListenerGroupTest() {
        $this->GroupTest('ListenerGroupTest');
        $this->addTestFile('listener.test.php');
        $this->addTestFile('requestdata.test.php');
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new ListenerGroupTest();
    $test->run(new HtmlReporter());
}
?>
