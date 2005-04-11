<?php
/**
* @version $Id: handle.group.php,v 1.1 2005/04/11 19:50:49 gpowers Exp $
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
class HandleGroupTest extends GroupTest {

    function HandleGroupTest() {
        $this->GroupTest('HandleGroupTest');
        $this->addTestFile('handle.test.php');
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new HandleGroupTest();
    $test->run(new HtmlReporter());
}
?>
