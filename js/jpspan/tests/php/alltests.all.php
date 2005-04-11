<?php
/**
* @version $Id: alltests.all.php,v 1.1 2005/04/11 19:50:49 gpowers Exp $
* @package JPSpan
* @subpackage Tests
*/
/**
* Init
*/
require_once('../config.php');

define("TEST_RUNNING", true);

/**
* @package JPSpan
* @subpackage Tests
*/
class AllTests extends GroupTest {

    function AllTests() {
        $this->GroupTest('All JPSpan PHP Tests');
        $this->loadGroups();
    }

    function loadGroups() {
        if ( $d = opendir('.') ) {
            while (($file = readdir($d)) !== false) {
                if ( is_file('./'.$file) ) {
                    $farray = explode('.',$file);
                    if ( $farray[1] == 'group' ) {
                        $classname = ucfirst($farray[0]).'GroupTest';
                        require_once './'.$file;
                        $this->AddTestCase(new $classname);
                    }
                }
            }
            closedir($d);
        }
    }

}

/**
* Run the tests
*/
$test = &new AllTests();
$test->run(new HtmlReporter());
?>
