<?php
/**
* @version $Id: codewriter.group.php,v 1.1 2005/04/11 19:50:49 gpowers Exp $
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
class CodeWriterGroupTest extends GroupTest {

    function CodeWriterGroupTest() {
        $this->GroupTest('CodeWriterGroupTest');
        $this->addTestFile('codewriter.test.php');
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new CodeWriterGroupTest();
    $test->run(new HtmlReporter());
}
?>
