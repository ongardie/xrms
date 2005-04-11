<?php
/**
* @version $Id: include.group.php,v 1.1 2005/04/11 19:50:50 gpowers Exp $
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
class IncludeGroupTest extends GroupTest {

    function IncludeGroupTest() {
        $this->GroupTest('IncludeGroupTest');
        $this->addTestFile('include.test.php');
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new IncludeGroupTest();
    $test->run(new HtmlReporter());
}
?>
