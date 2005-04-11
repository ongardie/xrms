<?php
/**
* @version $Id: monitor.group.php,v 1.1 2005/04/11 19:50:50 gpowers Exp $
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
class MonitorGroupTest extends GroupTest {

    function MonitorGroupTest() {
        $this->GroupTest('MonitorGroupTest');
        $this->addTestFile('monitor.test.php');
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new MonitorGroupTest();
    $test->run(new HtmlReporter());
}
?>
