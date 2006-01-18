<?php
/**
 * Test harness for the XRMS GUP_GUP_Pager 
 *
 * @todo
 * $Id: GUP_Pager_test.php,v 1.2 2006/01/18 20:43:57 daturaarutad Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'utils-recurrence.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once("PHPUnit.php");
require_once("PHPUnit/GUI/HTML.php");

require_once($include_directory.'utils-activities.php');

require_once($include_directory . 'classes/Pager/GUP_Pager.php');

global $test_output;


$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

Class XRMS_GUP_PagerTest extends PHPUnit_TestCase { 
    
    function XRMSGUP_PagerTest( $name = "XRMS_GUP_PagerTest" ) {
        $this->PHPUnit_TestCase( $name );
    }

   function setUp() {   

       global $options;
       $this->session_user_id= session_check();
       global $session_user_id;
       $session_user_id=$this->session_user_id;

       $this->options = $options;
       $this->con = &adonewconnection($options['xrms_db_dbtype']);
       //connect to the xrms database

        $sql = "drop table pager_test";
        $rst = $this->con->execute($sql);
        if(!$rst) {
            db_error_handler($this->con, $sql);
        }




       $this->con->nconnect($options['xrms_db_server'], $options['xrms_db_username'], $options['xrms_db_password'], $options['xrms_db_dbname']);


       $sql = "CREATE TABLE pager_test (
                id int(11) NOT NULL auto_increment,
                name varchar(100) NOT NULL default '',
                amount float NOT NULL default '',
                entered_at datetime default NULL,
                status char(1) default 'o',
                pager_test_record_status char(1) default 'a',
                PRIMARY KEY  (id)
                ) TYPE=MyISAM;";

        $rst = $this->con->execute($sql);
        if(!$rst) {
            db_error_handler($this->con, $sql);
        }

        // buffer output for later display
        ob_start();


        $this->assertTrue($rst, "Creation of test table qf_test failed!");
    }

    function populate() {
        $j = 1;

        for($i=0; $i<100; $i++) {

            $date = date('Y-m-d H:i', $i % 10 * 86400);

            $sql = "insert into pager_test(name, amount, entered_at) values('user $i', '$j', '$date')";
            $rst = $this->con->execute($sql);
            if(!$rst) {
                db_error_handler($this->con, $sql);
            }
            $j = $i % 10 + $j %10;
        }

    }

    

   function teardown() {
        $sql = "drop table pager_test";
        $rst = $this->con->execute($sql);
        if(!$rst) {
            db_error_handler($this->con, $sql);
        }

        $this->assertTrue($rst, "Destruction of test table pager_test failed!");

        // buffer output for later display
        global $test_output;
        $test_output .= ob_get_contents();
        ob_end_clean();

       $this->con=NULL;
    }
    function test_XRMSTEST() {
	    $this->assertTrue(true, "This should never fail.");
    }


    function test_SQL_Only() {

        $this->populate();

        $sql = "select * from pager_test";

        $columns = array();
        $columns[] = array('name' => _('Name'), 'index_sql' => 'name', 'sql_sort_column' => 'name');
        $columns[] = array('name' => _('Amount'), 'index_sql' => 'amount', 'type' => 'currency');
        $columns[] = array('name' => _('Entered Date'), 'index_sql' => 'entered_at', 'type' => 'date', 'default_sort' => 'desc');
        
        $pager = new GUP_Pager($this->con, $sql, null, _('Search Results'), 'PagerTestForm', 'TestPager', $columns, true, true);
        
        // set up the bottom row of buttons
        $endrows = "<tr><td class=widget_content_form_element colspan=10>$pager_columns_button" . $pager->GetAndUseExportButton() .  "</td></tr>";
        
        $pager->AddEndRows($endrows);
        $output = $pager->Render(6);

        $this->CompareTest($output, 'GUP_Pager_Test_SQL_Only');
    }

    function make_SQL_Calc_Pager() {

        $sql = "select * from pager_test";

        $columns = array();
        $columns[] = array('name' => _('Name'), 'index_sql' => 'name', 'sql_sort_column' => 'name');
        $columns[] = array('name' => _('Amount'), 'index_sql' => 'amount', 'type' => 'currency', 'group_calc' => true);
        $columns[] = array('name' => _('Calc'), 'index_calc' => 'calculated_item');
        $columns[] = array('name' => _('Entered Date'), 'index_sql' => 'entered_at', 'type' => 'date');
        
        $pager = new GUP_Pager($this->con, $sql, 'data_callback', _('Search Results'), 'PagerTestForm', 'TestPager', $columns, true, true);
        
        // set up the bottom row of buttons
        $endrows = "<tr><td class=widget_content_form_element colspan=10>$pager_columns_button" . $pager->GetAndUseExportButton() .  "</td></tr>";
        
        $pager->AddEndRows($endrows);
        return $pager->Render(6);
    }

    function test_SQL_Calc() {
        $this->populate();

        $output = $this->make_SQL_Calc_Pager();
        $this->CompareTest($output, 'GUP_Pager_Test_SQL_Calc1');

/*      Emulate a sort:

            function TestPager_resort(sortColumn) {
                document.PagerTestForm.TestPager_sort_column.value = sortColumn + 1;
                document.PagerTestForm.TestPager_next_page.value = '';
                document.PagerTestForm.TestPager_resort.value = 1;
                document.PagerTestForm.action = document.PagerTestForm.action + "#" + "TestPager";
                document.PagerTestForm.submit();
            }

       using these hidden form vars:

            <input type=hidden name=TestPager_use_post_vars value=1>
            <input type=hidden name=TestPager_next_page value="">
            <input type=hidden name=TestPager_resort value="0">
            <input type=hidden name=TestPager_group_mode value="">
            <input type=hidden name=TestPager_last_group_mode value="">
            <input type=hidden name=TestPager_current_sort_column value="3">
            <input type=hidden name=TestPager_sort_column value="3">
            <input type=hidden name=TestPager_current_sort_order value="desc">

            <input type=hidden name=TestPager_sort_order value="desc">
            <input type=hidden name=TestPager_maximize value="">
            <input type=hidden name=TestPager_show_hide value="show">
            <input type=hidden name=TestPager_refresh value="">
            <input type=hidden name=TestPager_export value="">

*/

        //hidden form vars first
        $_POST['TestPager_use_post_vars'] = 1;
        $_POST['TestPager_current_sort_column'] = 1;
        $_POST['TestPager_sort_column'] = 1;
        $_POST['TestPager_current_sort_order'] = 'asc';
        $_POST['TestPager_sort_order'] = 'desc';
        $_POST['TestPager_show_hide'] = 'show';

        // javascript set
        $_POST['TestPager_sort_column'] = 3;
        $_POST['TestPager_next_page'] = '';
        $_POST['TestPager_resort'] = 1;


        $output = $this->make_SQL_Calc_Pager();
        $this->CompareTest($output, 'GUP_Pager_Test_SQL_Calc2');


/*      Emulate maximize:

            function TestPager_maximize() {
                document.PagerTestForm.TestPager_maximize.value = 'true';
                document.PagerTestForm.TestPager_next_page.value = '';
                document.PagerTestForm.action = document.PagerTestForm.action + "#" + "TestPager";
                document.PagerTestForm.submit();
            }

       using these hidden form vars:

            <input type=hidden name=TestPager_use_post_vars value=1>
            <input type=hidden name=TestPager_next_page value="">
            <input type=hidden name=TestPager_resort value="0">
            <input type=hidden name=TestPager_group_mode value="">
            <input type=hidden name=TestPager_last_group_mode value="">
            <input type=hidden name=TestPager_current_sort_column value="3">
            <input type=hidden name=TestPager_sort_column value="3">
            <input type=hidden name=TestPager_current_sort_order value="desc">

            <input type=hidden name=TestPager_sort_order value="desc">
            <input type=hidden name=TestPager_maximize value="">
            <input type=hidden name=TestPager_show_hide value="show">
            <input type=hidden name=TestPager_refresh value="">
            <input type=hidden name=TestPager_export value="">

*/

        //hidden form vars first
        $_POST['TestPager_use_post_vars'] = 1;
        $_POST['TestPager_current_sort_column'] = 1;
        $_POST['TestPager_sort_column'] = 1;
        $_POST['TestPager_current_sort_order'] = 'asc';
        $_POST['TestPager_sort_order'] = 'desc';
        $_POST['TestPager_show_hide'] = 'show';

        // javascript set
        $_POST['TestPager_maximize'] = 3;
        $_POST['TestPager_next_page'] = '';


        $output = $this->make_SQL_Calc_Pager();
        $this->CompareTest($output, 'GUP_Pager_Test_SQL_Calc3');



/*      Emulate group button click:

            function TestPager_group(groupColumn) {
                document.PagerTestForm.TestPager_last_group_mode.value = document.PagerTestForm.TestPager_group_mode.value;
                document.PagerTestForm.TestPager_group_mode.value = groupColumn;
                document.PagerTestForm.TestPager_next_page.value = '';
                document.PagerTestForm.action = document.PagerTestForm.action + "#" + "TestPager";
                document.PagerTestForm.submit();
            }

       using these hidden form vars:

            <input type=hidden name=TestPager_use_post_vars value=1>
            <input type=hidden name=TestPager_next_page value="">
            <input type=hidden name=TestPager_resort value="0">
            <input type=hidden name=TestPager_group_mode value="">
            <input type=hidden name=TestPager_last_group_mode value="">
            <input type=hidden name=TestPager_current_sort_column value="3">
            <input type=hidden name=TestPager_sort_column value="3">
            <input type=hidden name=TestPager_current_sort_order value="desc">

            <input type=hidden name=TestPager_sort_order value="desc">
            <input type=hidden name=TestPager_maximize value="">
            <input type=hidden name=TestPager_show_hide value="show">
            <input type=hidden name=TestPager_refresh value="">
            <input type=hidden name=TestPager_export value="">

*/

        //hidden form vars first
        $_POST['TestPager_use_post_vars'] = 1;
        $_POST['TestPager_current_sort_column'] = 1;
        $_POST['TestPager_sort_column'] = 1;
        $_POST['TestPager_current_sort_order'] = 'asc';
        $_POST['TestPager_sort_order'] = 'desc';
        $_POST['TestPager_show_hide'] = 'show';

        // javascript set
        $_POST['TestPager_last_group_mode'] = '';
        $_POST['TestPager_group_mode'] = 1;
        $_POST['TestPager_next_page'] = '';


        $output = $this->make_SQL_Calc_Pager();
        $this->CompareTest($output, 'GUP_Pager_Test_SQL_Calc4');




/*      Emulate group <select> change (Amount = 6):

            function TestPager_group(groupColumn) {
                document.PagerTestForm.TestPager_last_group_mode.value = document.PagerTestForm.TestPager_group_mode.value;
                document.PagerTestForm.TestPager_group_mode.value = groupColumn;
                document.PagerTestForm.TestPager_next_page.value = '';
                document.PagerTestForm.action = document.PagerTestForm.action + "#" + "TestPager";
                document.PagerTestForm.submit();
            }

       using these hidden form vars:

            <input type=hidden name=TestPager_use_post_vars value=1>
            <input type=hidden name=TestPager_next_page value="">
            <input type=hidden name=TestPager_resort value="0">
            <input type=hidden name=TestPager_group_mode value="">
            <input type=hidden name=TestPager_last_group_mode value="">
            <input type=hidden name=TestPager_current_sort_column value="3">
            <input type=hidden name=TestPager_sort_column value="3">
            <input type=hidden name=TestPager_current_sort_order value="desc">

            <input type=hidden name=TestPager_sort_order value="desc">
            <input type=hidden name=TestPager_maximize value="">
            <input type=hidden name=TestPager_show_hide value="show">
            <input type=hidden name=TestPager_refresh value="">
            <input type=hidden name=TestPager_export value="">

*/

        //hidden form vars first
        $_POST['TestPager_use_post_vars'] = 1;
        $_POST['TestPager_current_sort_column'] = 1;
        $_POST['TestPager_sort_column'] = 1;
        $_POST['TestPager_current_sort_order'] = 'asc';
        $_POST['TestPager_sort_order'] = 'desc';
        $_POST['TestPager_show_hide'] = 'show';
        $_POST['TestPager_group_id'] = 6;
        // javascript set
        $_POST['TestPager_last_group_mode'] = '';
        $_POST['TestPager_group_mode'] = 1;
        $_POST['TestPager_next_page'] = '';


        $output = $this->make_SQL_Calc_Pager();
        $this->CompareTest($output, 'GUP_Pager_Test_SQL_Calc5');





    }



    // simple comparison of $output to the contents of $filename
    // writes bad file to $filename.bad for easy updating
    function CompareTest($output, $filename) {
        
        if(!CompareScalarToFile($output, "output/$filename", $contents)) {
            $bad_file = "output/$filename.bad";

            $handle = fopen($bad_file, "w");
            fwrite($handle, $output);
            fclose($handle);

            echo "Test Failed : $filename<br>\n";
            echo "Output should have been : " . WrapForm($contents);
            echo "But this was returned instead : " . WrapForm($output);
            $this->assertTrue(false, "$filename Mismatch.");
        }
    }



}

function data_callback($row) {
    $row['calculated_item'] = $row['name'] . '-' . $row['amount'];
    return $row;
}



// simple comparison of $output to the contents of $filename
// contents are then available in $contents
function CompareScalarToFile($output, $filename, &$contents) {

    if(file_exists($filename)) {

        $handle = fopen($filename, "r");

        if($handle && filesize($filename)) {

            $contents = fread($handle, filesize($filename));
            fclose($handle);
    
            if($output == $contents) 
                return true;
        }
    } 
    return false;

}

// nicen up the display a bit
function WrapForm($form) {
    return "<div style=\"border: solid black 1px;\">$form</div>";
}




$suite= new PHPUnit_TestSuite("XRMS_GUP_PagerTest");
$display = new PHPUnit_GUI_HTML($suite);
$display->show();

echo $test_output;


//$suite = new PHPUnit_TestSuite( "get_object_groups_object_inherit");
/*
$test = new ACLTest( "test_get_object_groups_object_inherit");
$display = new PHPUnit_GUI_HTML($test);
$display->show();
*/
//$result = PHPUnit::run($suite);
//print $result->toHTML();

//$testRunner = new TestRunner();
//$testRunner->run($suite);
/*
 $test = new ACLTest( "test_get_object_groups_object_inherit");
 $testRunner = new TestRunner();
 $testRunner->run( $test );
 */
/*
 * $Log: GUP_Pager_test.php,v $
 * Revision 1.2  2006/01/18 20:43:57  daturaarutad
 * improve failed test display
 *
 * Revision 1.1  2006/01/04 03:06:15  daturaarutad
 * new file
 *
 * Revision 1.2  2005/11/16 18:35:17  daturaarutad
 * newer better faster
 *
 * Revision 1.1  2005/11/15 22:53:23  daturaarutad
 * first tests
 *
 *
 */
 ?>