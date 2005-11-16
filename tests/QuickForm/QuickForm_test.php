<?php
/**
 * Test harness for the XRMS GUP_QuickForm 
 *
 * @todo
 * $Id: QuickForm_test.php,v 1.2 2005/11/16 18:35:17 daturaarutad Exp $
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

require_once($include_directory . 'classes/QuickForm/ADOdb_QuickForm.php');

global $test_output;


$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

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
       $this->con = &adonewconnection($options['xrms_db_dbtype']);
       //connect to the xrms database
       $this->con->nconnect($options['xrms_db_server'], $options['xrms_db_username'], $options['xrms_db_password'], $options['xrms_db_dbname']);


       $sql = "CREATE TABLE qf_test (
                qf_id int(11) NOT NULL auto_increment,
                case_type_id int(11) NOT NULL default '0',
                title varchar(100) NOT NULL default '',
                description text,
                entered_at datetime default NULL,
                status char(1) default 'o',
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


	
	
	    $view = new ADOdb_QuickForm_View($this->con, _('Edit Note'), 'POST');
	    $view->SetReturnButton('Return to List', $return_url);
	    $view->SetButtonText('A B C', 'Easy As', '1 2 3');
	
	    $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
	    $form_html = $controller->ProcessAndRenderForm();

        $this->CompareTest($form_html, 'QuickForm_Test_SimpleForm');

    }



    // simple comparison of $output to the contents of $filename
    // writes bad file to $filename.bad for easy updating
    function CompareTest($output, $filename) {
        
        if(!CompareScalarToFile($output, "output/$filename", $contents)) {
            $bad_file = "output/$filename.bad";

            $handle = fopen($bad_file, "w");
            fwrite($handle, $output);
            fclose($handle);

            echo "Failed test: $filename" . WrapForm($output);
            $this->assertTrue(false, "$filename Mismatch.");
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
 * Revision 1.2  2005/11/16 18:35:17  daturaarutad
 * newer better faster
 *
 * Revision 1.1  2005/11/15 22:53:23  daturaarutad
 * first tests
 *
 *
 */
 ?>