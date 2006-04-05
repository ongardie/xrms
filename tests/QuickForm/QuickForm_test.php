<?php
/**
 * Test harness for the XRMS GUP_QuickForm 
 *
 * @todo
 * $Id: QuickForm_test.php,v 1.4 2006/04/05 01:31:42 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'utils-database.php');
require_once($include_directory . 'utils-recurrence.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once("PHPUnit.php");
require_once("PHPUnit/GUI/HTML.php");

require_once($include_directory.'utils-activities.php');

require_once($include_directory . 'classes/QuickForm/ADOdb_QuickForm.php');

global $test_output;


$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

/*
    Tests to write:
        -Test when prepend_tablename is used in all cases
*/


Class XRMSQuickFormTest extends PHPUnit_TestCase { 
    
    function XRMSQuickFormTest( $name = "XRMSQuickFormTest" ) {
        $this->PHPUnit_TestCase( $name );
    }

   function setUp() {   

       global $options;
       $this->session_user_id= session_check();
       global $session_user_id;
       $session_user_id=$this->session_user_id;

       $this->options = $options;
       $this->con = get_xrms_dbconnection();
       //connect to the xrms database

       $sql = "CREATE TABLE qf_test (
                qf_id int(11) NOT NULL auto_increment,
                case_type_id int(11) NOT NULL default '0',
                title varchar(100) NOT NULL default '',
                description text,
                entered_at datetime default NULL,
                status char(1) default 'o',
                booya enum('victor','victoria','pat'),
                qf_test_filename varchar(255) default NULL,
                qf_test_filetype varchar(255) default NULL,    
                qf_test_filesize int(11) default NULL,
                qf_test_filedata longblob,                             
                qf_test_record_status char(1) default 'a',
                PRIMARY KEY  (qf_id)
                ) TYPE=MyISAM;";

        $rst = $this->con->execute($sql);
        if(!$rst) {
            db_error_handler($this->con, $sql);
        }

        // buffer output for later display
        ob_start();


        $this->assertTrue($rst, "Creation of test table qf_test failed!");
    }

   function teardown() {
        $sql = "drop table qf_test";
        $rst = $this->con->execute($sql);
        if(!$rst) {
            db_error_handler($this->con, $sql);
        }

        $this->assertTrue($rst, "Destruction of test table qf_test failed!");

        // buffer output for later display
        global $test_output;
        $test_output .= ob_get_contents();
        ob_end_clean();

       $this->con=NULL;
    }
    function test_XRMSTEST() {
	    $this->assertTrue(true, "This should never fail.");
    }



    function test_CRUD() {
        $con = $this->con;
        global $session_user_id;
        $session_user_id=$this->session_user_id;        

        // save $_POST
        $OLDPOST = $_POST;

        // new/create
        $_POST = array();
        $_POST['form_action'] = 'new';
        $_POST['return_url'] = 'http://localhost/xrms/test.php?arg=val&arg2=val2';

        $form_html = $this->MakeSimpleForm('CRUD_1');
        $this->CompareTest($form_html, 'QuickForm_Test_CRUD_1');


        // create/view
        $_POST = array();
        $_POST['form_action'] = 'create';
        $_POST['qf_test_QF_description'] = 'Created by QuickForm test.';
        $_POST['qf_test_QF_booya'] = 'victoria';
        $_POST['return_url'] = 'http://localhost/xrms/test.php?arg=val&arg2=val2';

        $form_html = $this->MakeSimpleForm('CRUD_2');

        $rows = $this->CheckDBResults($con);

        if(1 != count($rows)) {
            $this->assertTrue(false, "test_CRUD_2 returned " . count($rows) . " instead of 1");
        }

        $this->CompareTest($form_html, 'QuickForm_Test_CRUD_2');

        // view
        $_POST = array();
        $_POST['form_action'] = 'view';
        $_POST['qf_test_QF_qf_id'] = 1;
        $_POST['return_url'] = 'http://localhost/xrms/test.php?arg=val&arg2=val2';
        $form_html = $this->MakeSimpleForm('CRUD_3');
        $this->CompareTest($form_html, 'QuickForm_Test_CRUD_3');

        // update
        $_POST = array();
        $_POST['form_action'] = 'update';
        $_POST['qf_test_QF_qf_id'] = 1;
        $_POST['qf_test_QF_description'] = 'Created by QuickForm test. (modified)';
        $_POST['return_url'] = 'http://localhost/xrms/test.php?arg=val&arg2=val2';
        $form_html = $this->MakeSimpleForm('CRUD_4');
        $this->CompareTest($form_html, 'QuickForm_Test_CRUD_4');



        // delete
        $_POST = array();
        $_POST['form_action'] = 'delete';
        $_POST['qf_test_QF_qf_id'] = 1;
        $_POST['return_url'] = 'http://localhost/xrms/test.php?arg=val&arg2=val2';

        $form_html = $this->MakeSimpleForm('CRUD_5');

        $rows = $this->CheckDBResults($con);

        if(0 != count($rows)) {
            $this->assertTrue(false, "test_CRUD_5 returned " . count($rows) . " instead of 0");
        }

        $this->CompareTest($form_html, 'QuickForm_Test_CRUD_5');

        // restore $_POST
        $_POST = $OLDPOST;

        //$this->assertTrue($activity_result, "Failed to get information about activity");
    }
    function test_SchemaRead() {

	    $model = new ADOdb_QuickForm_Model();
	    $model->ReadSchemaFromDB($this->con, 'qf_test');

        ob_start();
        $fields = $model->GetFields();
        echo "<pre>";
        print_r($fields);
        echo "\n</pre>";

        $fields_output .= ob_get_contents();
        ob_end_clean();

        $this->CompareTest($fields_output, 'QuickForm_Test_SchemaRead');
    }


    function test_SimpleForm() {
        $form_html = $this->MakeSimpleForm('SimpleForm'); 
        $this->CompareTest($form_html, 'QuickForm_Test_SimpleForm');
    }

    function MakeSimpleForm($form_name) {

	    $model = new ADOdb_QuickForm_Model();
	    $model->ReadSchemaFromDB($this->con, 'qf_test');
	    $model->SetDisplayNames(array('description' => _('Description'),
	                                  'on_what_table' => _('On what table'),
	                                  'on_what_id' => _('On what id'),
	                                  'entered_at' => _('Entered At'),
	                                  'qf_test_record_status' => _('Status'),
        ));
	
	    $model->SetForeignKeyField('case_type_id', 'Case Type', 'case_types', 'case_type_id', 'case_type_pretty_name');
        $model->SetFileField('qf_test_filedata', 'qf_test_filename', 'qf_test_filetype', 'qf_test_filesize', 'www.test.com');
        $model->SetFieldType('qf_test_filetype', 'hidden');
        $model->SetFieldType('qf_test_filesize', 'hidden');

        $model->SetDisplayOrders(array('description','case_type_id','status','title'));
	
	    $view = new ADOdb_QuickForm_View($this->con, _('Edit Note'), 'POST', true);
	    $view->SetReturnButton('Return to List', $return_url);
	    $view->SetButtonText('A B C', 'Easy As', '1 2 3');
        $view->EnableDeleteButton();
	
	    $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
	    $qf_html = $controller->ProcessAndRenderForm();
        $msg = $controller->GetStatusMessage();
	    $form_html = "<form name=$form_name>$msg\n$qf_html</form>";
        return $form_html;
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
// don't output the good form because <form name=> will clash
            //echo "Output should have been : " . WrapForm($contents);
            echo "But this was returned instead : " . WrapForm($output);
            $this->assertTrue(false, "$filename Mismatch.");
        }
    }
    function CheckDBResults($con) {
        $sql = "select * from qf_test";
        $rst = $con->execute($sql);
        if($rst) {
            return $rst->GetAll();

        } else {
            $this->assertTrue(false, db_error_handler($con, $sql));
        }
    }



}


// simple comparison of $output to the contents of $filename
// contents are then available in $contents
function CompareScalarToFile($output, $filename, &$contents) {
    $handle = fopen($filename, "r");
    $contents = fread($handle, filesize($filename));
    fclose($handle);

    if($output != $contents) 
        return false;
    else
        return true;
}

// nicen up the display a bit
function WrapForm($form) {
    return "<div style=\"border: solid black 1px;\">$form</div>";
}




$suite= new PHPUnit_TestSuite("XRMSQuickFormTest");
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
 * $Log: QuickForm_test.php,v $
 * Revision 1.4  2006/04/05 01:31:42  vanmer
 * - changed to use centralized XRMS dbconnection
 *
 * Revision 1.3  2006/01/27 22:49:43  daturaarutad
 * add tests for CRUD of records and prepend_tablename feature
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