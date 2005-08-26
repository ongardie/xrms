<?php
  /**
   * Test harness for the SMTPs Class
   *
   * Goals:
   *  - test property assignments
   *  - test "boundary" results
   *  - test "failure" senarios
   *  - test server connections based upon ini configuration
   *
   * @package SMTPs_Test
   *
   * @author Walter Torres <walter@torres.ws>
   *
   * @version $Revision: 1.7 $
   * @copyright copyright information
   * @license URL name of license
   *
   **/


require_once("PHPUnit.php");
require_once("PHPUnit/GUI/HTML.php");

require_once("../SMTPs.php");



// ***********************************************************************
// Test Class Properties Interface access

// This class simply assigns a given value to a desired property
// and then pulls the value directly from the Class properties.
Class SMTPs_Test
{

    function SMTPs_Test()
    {
        $this->SMTPs = new SMTPs ();
    }

    function read_config_file( $_path = false )
    {
        $result = $this->SMTPs->setConfig($_path);

        return $result;
    }

   /*
    * set property and directly pull set value
    *
    * Determines the method inwhich the message are to be sent.
    * - 'sockets' [0] - conect via network to SMTP server - default
    * - 'pipe     [1] - use UNIX path to EXE
    * - 'phpmail  [2] - use the PHP built-in mail function
    */
    function set_Transport_Type( $_type = false )
    {
        // Setter method to define property
        $this->SMTPs->setTransportType($_type);

        // retrieve property value directly
        return ( $this->SMTPs->_transportType );
    }

   /*
    * Simple SET and GET of property
    *
    */
    function get_Transport_Type( $_type = false )
    {
        $this->SMTPs->setTransportType($_type);

        return $this->SMTPs->getTransportType();
    }

   /*
    * Host names should only be legel DNS names or IP addresses
    *
    */
    function set_Host( $_host = false )
    {
        $this->SMTPs->setHost($_host);

        return $this->SMTPs->_smtpsHost;
    }

    function get_Host( $_host = false )
    {
        $this->SMTPs->setHost($_host);

        return $this->SMTPs->getHost();
    }

   /*
    * Host ports can only be positive wholw numbers
    * between 1 and 65535
    */
    function set_Port( $_port = false )
    {
        $this->SMTPs->setPort($_port);

        return $this->SMTPs->_smtpsPort;
    }

    function get_Port( $_port = false )
    {
        $this->SMTPs->setPort($_port);

        return $this->SMTPs->getPort();
    }

    function set_ID( $_id = false )
    {
        $this->SMTPs->setID($_id);

        return $this->SMTPs->_smtpsID;
    }

    function get_ID( $_id = false )
    {
        $this->SMTPs->setID($_id);

        return $this->SMTPs->getID();
    }

    function set_PW( $_pw = false )
    {
        $this->SMTPs->setPW($_pw);

        return $this->SMTPs->_smtpsPW;
    }

    function get_PW( $_pw = false )
    {
        $this->SMTPs->setPW($_pw);

        return $this->SMTPs->getPW();
    }

    function set_Char_Set( $_set = false )
    {
        $this->SMTPs->setCharSet($_set);

        return $this->SMTPs->_smtpsCharSet;
    }

    function get_Char_Set( $_set = false )
    {
        $this->SMTPs->setCharSet($_set);

        return $this->SMTPs->getCharSet();
    }

    function set_Trans_Encode( $_encode = false )
    {
        $this->SMTPs->setTransEncode($_encode);

        return $this->SMTPs->_smtpsTransEncode;
    }

    function get_Trans_Encode( $_encode = false )
    {
        $this->SMTPs->setTransEncode($_encode);

        return $this->SMTPs->getTransEncode();
    }

   /*
    * Message Content Sensitivity
    * Message Sensitivity values:
    *   - [0] None - default
    *   - [1] Personal
    *   - [2] Private
    *   - [3] Company Confidential
    */
    function set_Sensitivity( $_sensitivity = false )
    {
        $this->SMTPs->setSensitivity($_sensitivity);

        return $this->SMTPs->_msgSensitivity;
    }

    function get_Sensitivity( $_sensitivity = false )
    {
        $this->SMTPs->setSensitivity($_sensitivity);

        return $this->SMTPs->getSensitivity();
    }

    function set_Priority( $_priority = false )
    {
        $this->SMTPs->setPriority($_priority);

        return $this->SMTPs->_msgPriority;
    }

    function get_Priority( $_priority = false )
    {
        $this->SMTPs->setPriority($_priority);

        return $this->SMTPs->getPriority();
    }

    function set_Subject( $_subject = false )
    {
        $this->SMTPs->setSubject($_subject);

        return $this->SMTPs->_msgSubject;
    }

    function get_Subject( $_subject = false )
    {
        $this->SMTPs->setSubject($_subject);

        return $this->SMTPs->getSubject();
    }

    function set_Msg_Boundary()
    {
        $this->SMTPs->_setBoundary();

        return $this->SMTPs->_smtpsBoundary;
    }

    function get_Msg_Boundary()
    {
        $this->SMTPs->_setBoundary();

        return $this->SMTPs->_getBoundary();
    }

    function get_Msg_Header()
    {
        return $this->SMTPs->getHeader();
    }

   /*
    * FROM Address is stored in a high structured array_keys
    */
    function set_From_Address( $_sender = false )
    {
        $this->SMTPs->setFrom($_sender);

        return $this->SMTPs->_msgFrom;
    }

    function get_From_Address( $_sender = false )
    {
        $this->SMTPs->setFrom($_sender);

        return $this->SMTPs->getFrom();
    }

    function set_Email_List_TO( $_fullAddress = false )
    {
        $this->SMTPs->setTo($_fullAddress);

        return $this->SMTPs->_msgRecipients;
    }

    function get_Email_List_TO( $_fullAddress = false )
    {
        $this->SMTPs->setTO($_fullAddress);

        return $this->SMTPs->get_email_list( 'to' );
    }

    function set_Email_List_CC( $_fullAddress = false )
    {
        $this->SMTPs->setCC($_fullAddress);

        return $this->SMTPs->_msgRecipients;
    }

    function get_Email_List_CC( $_fullAddress = false )
    {
        $this->SMTPs->setCC($_fullAddress);

        return  ( $this->SMTPs->get_email_list( 'cc' ) );
    }

    function set_Email_List_BCC( $_fullAddress = false )
    {
        $this->SMTPs->setBCC($_fullAddress);

        return $this->SMTPs->_msgRecipients;
    }

    function get_Email_List_BCC( $_fullAddress = false )
    {
        $this->SMTPs->setBCC($_fullAddress);

        return  ( $this->SMTPs->get_email_list( 'bcc' ) );
    }

    function get_RCPT_List( $to_address = false, $cc_address = false, $bcc_address = false )
    {
        if ( $to_address )
            $this->SMTPs->setTO($to_address);

        if ( $cc_address )
            $this->SMTPs->setCC($cc_address);

        if ( $bcc_address )
            $this->SMTPs->setBCC($bcc_address);

        return $this->SMTPs->get_RCPT_list();
    }

    function Strip_Email( $_fullAddress = false )
    {
        return $this->SMTPs->_strip_email($_fullAddress);
    }

    function set_Xheader( $_xheader = false )
    {
        $this->SMTPs->setXheader($_xheader);

        return $this->SMTPs->_msgXheader;
    }

    function get_Xheader( $_xheader = false )
    {
        $this->SMTPs->setXheader($_xheader);

       return $this->SMTPs->getXheader();
    }

    function set_Body_Content( $_content = false, $_contentType = false )
    {
        $this->SMTPs->setBodyContent($_content, $_contentType);

        return $this->SMTPs->_msgContent;
    }

    function get_Body_Content( $_content = false, $_contentType = false )
    {
        $this->SMTPs->setBodyContent($_content, $_contentType);

        return $this->SMTPs->getBodyContent();
    }

    function set_Attachment( $_strContent = false, $_strFileName = false, $_strMimeType = false )
    {
        $this->SMTPs->setAttachment($_strContent, $_strFileName, $_strMimeType);

        return $this->SMTPs->_msgContent;
    }

}


// ***********************************************************************
// Test Class Properties

Class SMTPsPropertiesTest extends PHPUnit_TestCase
{

    function SMTPsPropertiesTest( $name = "SMTPsPropertiesTest" )
    {
        $this->PHPUnit_TestCase( $name );
    }

   function setUp()
   {
        $this->SMTPs = new SMTPs();
        $this->SMTPsTest = new SMTPs_Test();

        include ( 'SMTPs_test_config.php' );
   }


   function teardown()
   {
        $this->options = NULL;
        $this->SMTPs = NULL;

        $this->user = NULL;
        $this->scope = NULL;
        $this->permission=NULL;
        $this->on_what_id=NULL;
        $this->groupName=NULL;
        $this->roleName=NULL;
        $this->controlled_objectName=NULL;
        $this->controlled_objectTable=NULL;
        $this->controlled_objectField=NULL;
        $this->controlled_objectDataSource=NULL;
        $this->data_sourceName=NULL;
    }

    function test_assert()
    {
        $this->assertTrue(true,"This should never fail");
    }

    function test_read_config_file( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->ini_path;

        $result = $this->SMTPsTest->read_config_file($_path);

        $this->assertTrue($result, "Read: '" . $_path . "'");

        return $result;
    }

    function test_set_Transport_Type( $_type = false )
    {
        // This will use the predefined value if nothing is sent it
        if ( $_type === false )
            $_type = $this->transportType;

        // Call to "parent" class for processing
        $_retValue = $this->SMTPsTest->set_Transport_Type($_type);

        // Determine if we received what we think we should have
        $this->assertEquals($_type, $_retValue, "Set Transport Type: ");

        // In case this method was called by another Class, return detemination boolean
        return ( $_retValue == $_type );
    }

    function test_get_Transport_Type( $_type = false )
    {
        if ( $_type === false )
            $_type = $this->transportType;

        $_retValue = $this->SMTPsTest->get_Transport_Type($_type);

        // I used this "assert" call in order for the FAIL display to show me what
        // should have received and what I actually received
        $this->assertEquals($_type, $_retValue, "Get Transport Type: ");

        // Use this in case this method was called from another Test Suite
        // otherwise it really doesn't do a thing
        return ( $_retValue == $_type );
    }

    function test_set_Host( $_host = false )
    {
        if ( $_host === false )
            $_host = $this->host;

        $_retValue = $this->SMTPsTest->set_Host($_host);

        $this->assertEquals($_host, $_retValue, "Set Host: ");

        return ( $_retValue == $_host );
    }

    function test_get_Host( $_host = false )
    {
        if ( $_host === false )
            $_host = $this->host;

        $_retValue = $this->SMTPsTest->get_Host($_host);

        $this->assertEquals($_host, $_retValue, "Get Host: ");

        return ( $_retValue == $_host );
    }

    function test_set_Port( $_port = false )
    {
        if ( $_port === false )
            $_port = $this->port;

        $_retValue = $this->SMTPsTest->set_Port($_port);

        $this->assertEquals($_port, $_retValue, "Set Port: ");

        return ( $_retValue == $_port );
    }

    function test_get_Port( $_port = false )
    {
        if ( $_port === false )
            $_port = $this->port;

        $_retValue = $this->SMTPsTest->get_Port($_port);

        $this->assertEquals($_port, $_retValue, "Get Port: ");

        return ( $_retValue == $_port );
    }

    function test_set_ID( $_id = false )
    {
        if ( $_id === false )
            $_id = $this->id;

        $_retValue = $this->SMTPsTest->set_ID($_id);

        $this->assertEquals($_id, $_retValue, "Set SMTP ID: ");

        return ( $_retValue == $_id );
    }

    function test_get_ID( $_id = false )
    {
        if ( $_id === false )
            $_id = $this->id;

        $_retValue = $this->SMTPsTest->get_ID($_id);

        $this->assertEquals($_id, $_retValue, "Get SMTP ID: ");

        return ( $_retValue == $_id );
    }

    function test_set_PW( $_pw = false )
    {
        if ( $_pw === false )
            $_pw = $this->pw;

        $_retValue = $this->SMTPsTest->set_PW($_pw);

        $this->assertEquals($_pw, $_retValue, "Set SMTP Password: ");

        return ( $_retValue == $_pw );
    }

    function test_get_PW( $_pw = false )
    {
        if ( $_pw === false )
            $_pw = $this->pw;

        $_retValue = $this->SMTPsTest->get_PW($_pw);

        $this->assertEquals($_pw, $_retValue, "Get SMTP Password: ");

        return ( $_retValue == $_pw );
    }

    function test_set_Char_Set( $_set = false )
    {
        if ( $_set === false )
            $_set = $this->charSet;

        $_retValue = $this->SMTPsTest->set_Char_Set($_set);

        $this->assertEquals($_set, $_retValue, "Set Character Set: ");

        return ( $_retValue == $_set );
    }

    function test_get_Char_Set( $_set = false )
    {
        if ( $_set === false )
            $_set = $this->charSet;

        $_retValue = $this->SMTPsTest->get_Char_Set($_set);

        $this->assertEquals($_set, $_retValue, "Get Character Set: ");

        return ( $_retValue == $_set );
    }

    function test_set_Trans_Encode( $_encode = false )
    {
        if ( $_encode === false )
            $_encode = $this->transEncode;

        $_retValue = $this->SMTPsTest->set_Trans_Encode($_encode);

        $this->assertEquals($_encode, $_retValue, "Set Content Transfer Encoding: ");

        return ( $_retValue == $_encode );
    }

    function test_get_Trans_Encode( $_encode = false )
    {
        if ( $_encode === false )
            $_encode = $this->transEncode;

        $_retValue = $this->SMTPsTest->get_Trans_Encode($_encode);

        $this->assertEquals($_encode, $_retValue, "Get Content Transfer Encoding: ");

        return ( $_retValue == $_encode );

    }

   /*
    * Message Content Sensitivity
    * Message Sensitivity values:
    *   - [0] None - default
    *   - [1] Personal
    *   - [2] Private
    *   - [3] Company Confidential
    */
    function test_set_Sensitivity( $_sensitivity = false )
    {
        if ( $_sensitivity === false )
            $_sensitivity = $this->msgSensitivity;

        $_retValue = $this->SMTPsTest->set_Sensitivity($_sensitivity);

        $this->assertEquals($_sensitivity, $_retValue, "Set Message Sensitivity: ");

        return ( $_retValue == $_sensitivity );
    }

    function test_get_Sensitivity( $_sensitivity = false, $_results = false )
    {
        if ( $_sensitivity === false )
            $_sensitivity = $this->msgSensitivity;

        if ( $_results === false )
            $_results = $this->msgSensitivityResults;

        $_retValue = $this->SMTPsTest->get_Sensitivity($_sensitivity);

        $this->assertEquals($_results, $_retValue, "Get Message Sensitivity: ");

        return ( $_retValue == $_results );
    }

   /*
    * Message Content Priority
    * Message Priority values:
    *  - [0] 'Bulk'
    *  - [1] 'Highest'
    *  - [2] 'High'
    *  - [3] 'Normal' - default
    *  - [4] 'Low'
    *  - [5] 'Lowest
    */
    function test_set_Priority( $_priority = false )
    {
        if ( $_priority === false )
            $_priority = $this->msgPriority;

        $_retValue = $this->SMTPsTest->set_Priority($_priority);

        $this->assertEquals($_priority, $_retValue, "Set Message Priority: ");

        return ( $_retValue == $_priority );
    }

    function test_get_Priority( $_priority = false, $_results = false )
    {
        if ( $_priority === false )
            $_priority = $this->msgPriority;

        if ( $_results === false )
            $_results = $this->msgPriorityResults;

        $_retValue = $this->SMTPsTest->get_Priority($_priority);

        $this->assertEquals($_results, $_retValue, "Get Message Priority: ");

        return ( $_retValue == $_results );
    }

    function test_set_Subject( $_subject = false )
    {
        if ( $_subject === false )
            $_subject = $this->subject;

        $_retValue = $this->SMTPsTest->set_Subject($_subject);

        $this->assertEquals($_subject, $_retValue, "Set Message Subject: ");

        return ( $_retValue == $_subject );
    }

    function test_get_Subject( $_subject = false )
    {
        if ( $_subject === false )
            $_subject = $this->subject;

        $_retValue = $this->SMTPsTest->get_Subject($_subject);

        $this->assertEquals($_subject, $_retValue, "Get Message Subject: ");

        return ( $_retValue == $_subject );
    }

    function test_set_Msg_Boundary()
    {
        $_retValue = $this->SMTPsTest->set_Msg_Boundary();
        $_retValue = (! empty ( $_retValue ) );

        $this->assertTrue($_retValue, "Set Message Boundary: ");

       return $_retValue;
    }

    function test_get_Msg_Boundary()
    {
        $_retValue = $this->SMTPsTest->get_Msg_Boundary();
        $_retValue = empty ( $_retValue );

        $this->assertFalse($_retValue, "Get Message Boundary: ");

       return $_retValue;
    }

    function test_get_Msg_Header()
    {
        $_retValue = $this->SMTPsTest->get_Msg_Header();

        $_retValue = empty ( $_retValue );

        $this->assertFalse($_retValue, "Get Message Header: ");

       return $_retValue;
    }

    function test_set_From_Address( $_sender = false, $_sender_array = false )
    {
        if ( $_sender === false )
            $_sender = $this->sender;

        if ( $_sender_array === false )
            $_sender_array = $this->sender_array;

        $_retValue = $this->SMTPsTest->set_From_Address($_sender);

        $this->assertEquals($_sender_array, $_retValue, "Set FROM Address: ");

        return ( $_retValue == $_sender_array );
    }

    function test_get_From_Address( $_sender = false, $_sender_array = false )
    {
        if ( $_sender === false )
            $_sender = $this->sender;

        if ( $_sender_array === false )
            $_sender_array = $this->sender_array;

        $_retValue = $this->SMTPsTest->get_From_Address($_sender);

        $this->assertEquals($_sender_array, $_retValue, "Get FROM Address: ");

        return ( $_retValue == $_sender_array );
    }

    function test_set_Email_List_To( $_fullAddress = false, $_to_array = false )
    {
        if ( $_fullAddress === false )
            $_fullAddress = $this->to_full_address;

        if ( $_to_array === false )
            $_to_array = $this->to_array;

        $_retValue = $this->SMTPsTest->set_Email_List_TO($_fullAddress);

        $this->assertEquals($_retValue, $_to_array, "Set TO Email Address: ");

        return ( $_to_array == $_retValue );
    }

    function test_get_Email_List_To( $_fullAddress = false )
    {
        if ( $_fullAddress === false )
            $_fullAddress = $this->to_full_address;

        $_retValue = $this->SMTPsTest->get_Email_List_TO($_fullAddress);

        $this->assertEquals($_retValue, $_fullAddress, "Get TO Email Address: ");

        return ( $_fullAddress == $_retValue );
    }

    function test_set_Email_List_CC( $_fullAddress = false, $_cc_array = false )
    {
        if ( $_fullAddress === false )
            $_fullAddress = $this->cc_full_address;

        if ( $_cc_array === false )
            $_cc_array = $this->cc_array;

        $_retValue = $this->SMTPsTest->set_Email_List_CC($_fullAddress);

        $this->assertEquals($_retValue, $_cc_array, "Set CC Email Address: ");

        return ( $_to_array == $_retValue );
    }

    function test_get_Email_List_CC( $_fullAddress = false )
    {
        if ( $_fullAddress === false )
            $_fullAddress = $this->cc_full_address;

        $_retValue = $this->SMTPsTest->get_Email_List_CC($_fullAddress);

        $this->assertEquals($_retValue, $_fullAddress, "Get CC Email Address: ");

        return ( $_fullAddress == $_retValue );
    }

    function test_set_Email_List_BCC( $_fullAddress = false, $_bcc_array = false )
    {
        if ( $_fullAddress === false )
            $_fullAddress = $this->bcc_full_address;

        if ( $_bcc_array === false )
            $_bcc_array = $this->bcc_array;

        $_retValue = $this->SMTPsTest->set_Email_List_BCC($_fullAddress);

        $this->assertEquals($_retValue, $_bcc_array, "Set BCC Email Address: ");

        return ( $_to_array == $_retValue );
    }

    function test_get_Email_List_BCC( $_fullAddress = false )
    {
        if ( $_fullAddress === false )
            $_fullAddress = $this->bcc_full_address;

        $_retValue = $this->SMTPsTest->get_Email_List_BCC($_fullAddress);

        $this->assertEquals($_retValue, $_fullAddress, "Get BCC Email Address: ");

        return ( $_fullAddress == $_retValue );
    }

    function test_get_RCPT_List( $_to_addr = false, $_cc_addr = false, $_bcc_addr = false, $_rcpt_array = false )
    {
        if ( $_to_addr === false )
            $_to_addr = $this->to_full_address;

        if ( $_cc_addr === false )
            $_cc_addr = $this->cc_full_address;

        if ( $_bcc_addr === false )
            $_bcc_addr = $this->bcc_full_address;

        if ( $_rcpt_array === false )
            $_rcpt_array = $this->rcpt_array;

        $this->SMTPs->setTO($_to_addr);
        $this->SMTPs->setCC($_cc_addr);
        $this->SMTPs->setBCC($_bcc_addr);

        $_retValue = $this->SMTPsTest->get_RCPT_List( $_to_addr, $_cc_addr, $_bcc_addr );

        $this->assertEquals($_retValue, $_rcpt_array, "Recieptent List - TO Field: ");
    }

    function test_Strip_Email( $_fullAddress = false, $_arrayStripped = false )
    {
        if ( $_fullAddress === false )
            $_fullAddress = $this->to_full_address;

        if ( $_arrayStripped === false )
            $_arrayStripped = $this->to_address_array;

        $this->assertEquals($_arrayStripped, $this->SMTPsTest->Strip_Email($_fullAddress), "Strip Email: ");

        return ( $this->SMTPs->_strip_email($_fullAddress) == $_arrayStripped );
    }

    function test_set_Xheader( $_xheader = false )
    {
        if ( $_xheader === false )
            $_xheader = $this->xheader;

        $_retValue = $this->SMTPsTest->set_Xheader($_xheader);

        $this->assertEquals($_xheader, current ( $_retValue ), "Set Xheader: ");

        return ( current ( $_retValue ) == $_xheader );
    }

    function test_get_Xheader( $_xheader = false )
    {
        if ( $_xheader === false )
            $_xheader = $this->xheader;

        $_retValue = $this->SMTPsTest->get_Xheader($_xheader);

        $this->assertEquals($_xheader, current ( $_retValue ), "Get Xheader: ");

        return ( current ( $_retValue ) == $_xheader );
    }

    function test_set_Body_Content( $_content = false, $_contentType = false, $_contentRawArray = false )
    {
        if ( $_content === false )
            $_content = $this->content;

        if ( $_contentType === false )
            $_contentType = $this->contentType;

        if ( $_contentRawArray === false )
            $_contentRawArray = $this->contentRawArray;

        $_retValue = $this->SMTPsTest->set_Body_Content($_content, $_contentType);

        $this->assertEquals($_contentRawArray, $_retValue, "Set Body Content: ");

        return ( $_retValue == $_contentRawArray );
    }

    function test_get_Body_Content( $_content = false, $_contentType = false, $_contentMsg = false )
    {
        if ( $_content === false )
            $_content = $this->content;

        if ( $_contentType === false )
            $_contentType = $this->contentType;

        if ( $_contentMsg === false )
            $_contentMsg = $this->contentMsg;

        $_retValue = $this->SMTPsTest->get_Body_Content($_content, $_contentType);

        $this->assertEquals($_contentMsg, $_retValue, "Get Body Content: ");

        return ( $_retValue == $_contentMsg );
    }

    function test_set_Attachment( $_strContent = false, $_strFileName = false, $_strMimeType = false, $_attachArray = false )
    {
        if ( $_strContent === false )
            $_strContent = $this->content;

        if ( $_strFileName === false )
            $_strFileName = $this->fileName;

        if ( $_strMimeType === false )
            $_strMimeType = $this->mimeType;

        if ( $_attachArray === false )
            $_attachArray = $this->attachArray;

        $_retValue = $this->SMTPsTest->set_Attachment($_strContent, $_strFileName, $_strMimeType);

        $this->assertEquals($_attachArray, $_retValue, "Set_Attachment: ");

        return ( $_retValue == $_attachArray );
    }
};

// ***********************************************************************
// Test Class Method Boundaries

Class SMTPsBoundariesTest extends PHPUnit_TestCase {

    function SMTPsBoundariesTest( $name = "SMTPsBoundariesTest" ) {
        $this->PHPUnit_TestCase( $name );
    }

    function setUp() {
        $_mainTest = new SMTPsPropertiesTest ();
        $this->SMTPs = new SMTPs();
        $this->SMTPsTest = new SMTPs_Test();
    }

    function test_set_Port() {
        SMTPsPropertiesTest::test_set_Port( 'port');
    }


};

// ***********************************************************************
// Test Class Method Properties

Class SMTPsFailuresTest extends PHPUnit_TestCase {

    function SMTPsFailuresTest( $name = "SMTPsFailuresTest" ) {
        $this->PHPUnit_TestCase( $name );
    }

    function setUp() {
//        $_mainTest = new SMTPsPropertiesTest ();
        $this->SMTPs = new SMTPs();
        $this->SMTPsTest = new SMTPs_Test();

        include ( 'SMTPs_test_config.php' );
    }

   /*
    * set property above 'legal' value
    *
    * Determines the method inwhich the message are to be sent.
    * - 'sockets' [0] - conect via network to SMTP server - default
    * - 'pipe     [1] - use UNIX path to EXE
    * - 'phpmail  [2] - use the PHP built-in mail function
    */
    function test_set_Transport_Type__out_of_bounds()
    {
        // Define value "out of range"
        $_type = 5;

        // Call to "parent" class for processing
        $_returned = $this->SMTPsTest->get_Transport_Type($_type);

        // Determine if we received what we think we should have
        $this->assertFalse(($_type == $_returned), "Set Transport Type: ");
    }

    function test_set_Transport_Type__set_to_string()
    {
        // Define value as a string.
        $_type = 'transport';

        // Call to "parent" class for processing
        $_returned = $this->SMTPsTest->get_Transport_Type($_type);

        // Determine if we received what we think we should have
        $this->assertFalse(($_type === $_returned), "Set Transport Type: ");
    }

    function test_set_Host__invalid_IP()
    {
        $_host = '123456';

        $_returned = $this->SMTPsTest->get_Host( $_host );

        // Determine if we received what we think we should have
        $this->assertFalse( ($_host == $_returned), "Set Host: ");
    }

    function test_set_Host__invalid_Domain()
    {
        $_host = 'bad*name';

        $_returned = $this->SMTPsTest->get_Host( $_host );

        // Determine if we received what we think we should have
        $this->assertFalse( ($_host == $_returned), "Set Host: ");
    }

    function test_set_Port__out_of_bounds()
    {
        $_port = '123456';

        $_returned = $this->SMTPsTest->get_Port( $_port );

        // Determine if we received what we think we should have
        $this->assertFalse( ($_port == $_returned), "Set Port: ");
    }

    function test_set_Port__set_to_string()
    {
        $_port = 'port';

        $_returned = $this->SMTPsTest->get_Port( $_port );

        // Determine if we received what we think we should have
        $this->assertFalse( ($_port == $_returned), "Set Port: ");
    }

    function test_set_Trans_Encode__invalid_string()
    {
        $_encodeType = '9bit';

        $_retValue = $this->SMTPsTest->get_Trans_Encode($_encodeType);

        $this->assertFalse(($_encodeType == $_retValue), "Set Content Transfer Encoding: ");

        return ( $_retValue == $_encode );
    }

   /*
    * Message Content Sensitivity
    * Message Sensitivity values:
    *   - [0] None - default
    *   - [1] Personal
    *   - [2] Private
    *   - [3] Company Confidential
    */
    function test_set_Sensitivity__out_of_bounds()
    {
        $_sensitivity = 5;

        $_retValue = $this->SMTPsTest->set_Sensitivity($_sensitivity);

        $this->assertFalse(($_sensitivity == $_retValue), "Set Message Sensitivity: ");
    }

   /*
    * Message Content Priority
    * Message Priority values:
    *  - [0] 'Bulk'
    *  - [1] 'Highest'
    *  - [2] 'High'
    *  - [3] 'Normal' - default
    *  - [4] 'Low'
    *  - [5] 'Lowest
    */
    function test_set_Priority__out_of_bounds( $_priority = false )
    {
        $_priority = 6;

        $_retValue = $this->SMTPsTest->set_Priority($_priority);

        $this->assertFalse(($_priority == $_retValue), "Set Message Priority: ");
    }

    function test_set_From__bad_address()
    {
        $_sender = 'bad-address';

        $_retValue = $this->SMTPsTest->get_From_Address($_sender);

        $this->assertEquals($_sender, $_retValue, "Set FROM Address: ");
    }

    function test_set_TO_Email__bad_address()
    {
        $_addr = 'bad-address';

        $_retValue = $this->SMTPsTest->get_Email_List_TO($_addr);

        $this->assertEquals($_addr, $_retValue, "Set TO Email Address: ");
    }

    function test_set_CC_Email__bad_address()
    {
        $_addr = 'bad-address';

        $_retValue = $this->SMTPsTest->get_Email_List_CC($_addr);

        $this->assertEquals($_addr, $_retValue, "Set CC Email Address: ");
    }

    function test_set_BCC_Email__bad_address()
    {
        $_addr = 'bad-address';

        $_retValue = $this->SMTPsTest->get_Email_List_BCC($_addr);

        $this->assertEquals($_addr, $_retValue, "Set BCC Email Address: ");
    }

    function xx_test_get_RCPT_List__no_address()
    {
        $_addr = '';

        $_retValue = $this->SMTPsTest->get_RCPT_List( $_to_addr, $_cc_addr, $_bcc_addr );

        $this->assertEquals($_retValue, $_retValue, "Recieptent List: ");
    }

    function test_Strip_Email_bad_address()
    {
        $_addr = 'bad-address';

        $_retValue = $this->SMTPsTest->Strip_Email($_addr);

        $this->assertEquals($_addr, $_retValue, "Strip Email: ");
    }

    function test_get_Body_Content()
    {
        $_retValue = $this->SMTPsTest->get_Body_Content($this->content, $this->contentType);

        $this->assertEquals($this->contentMsg, $_retValue, "Get Body Content: ");
    }

    function test_set_Attachment( )
    {
        if ( $_strContent === false )
            $_strContent = $this->content;

        if ( $_strFileName === false )
            $_strFileName = $this->fileName;

        if ( $_strMimeType === false )
            $_strMimeType = $this->mimeType;

        if ( $_attachArray === false )
            $_attachArray = $this->attachArray;

        $_retValue = $this->SMTPsTest->set_Attachment($this->content, $this->fileName, $this->mimeType);

        $this->assertEquals($this->attachArray, $_retValue, "Set_Attachment: ");

    }
};

// ***********************************************************************
// Test Message Content Construction

Class SMTPsMessageContentDisplay extends PHPUnit_TestCase {

    function SMTPsFailuresTest( $name = "SMTPsMessageContentDisplay" ) {
        $this->PHPUnit_TestCase( $name );
    }

    function setUp() {
//        $_mainTest = new SMTPsPropertiesTest ();
        $this->SMTPs = new SMTPs();
        $this->SMTPsTest = new SMTPs_Test();

        include ( 'SMTPs_test_config.php' );
    }

    function test_Body_Content()
    {
        $_retValue = $this->SMTPsTest->get_Body_Content($this->content, $this->contentType);

        $this->fail ( $_retValue);
    }

    function test_Body_HTML_Content()
    {
        $_retValue = $this->SMTPsTest->get_Body_Content($this->contentHTML, 'html');

        $this->fail ( $_retValue);
    }

    function test_Attachment( )
    {
        $_retValue = $this->SMTPs->setBodyContent($this->content, $this->contentType);
        $_retValue = $this->SMTPs->setBodyContent($this->contentHTML, 'html');
        $_retValue = $this->SMTPs->setAttachment($this->content, $this->fileName, $this->mimeType);
        $_retValue = $this->SMTPs->getBodyContent($this->content, $this->contentType);

        $this->fail ( $_retValue);
    }
};


// ***********************************************************************
// Test Message Content Construction

Class SMTPsServerAuthentication extends PHPUnit_TestCase {

    function SMTPsFailuresTest( $name = "SMTPsServerAuthentication" ) {
        $this->PHPUnit_TestCase( $name );
    }

    function setUp() {
        $this->SMTPs = new SMTPs();
        $this->SMTPsTest = new SMTPs_Test();

        include ( 'SMTPs_test_config.php' );
    }

    function xx_test_Server_Connectivity()
    {
       /**
        * Default return value
        *
        * Returns constructed SELECT Object string or boolean upon failure
        * Default value is set at FALSE
        *
        * @var mixed $_retVal Indicates if Object was created or not
        * @access private
        * @static
        */
        $_retVal = false;

        if ( $this->SMTPs->setConfig($this->ini_path) )
        {
            if ( ! $this->SMTPs->server_connect() )
            {
                $this->fail ( $this->SMTPs->getErrors() );
                $_retVal = true;
            }
        }
        else
        {
            $this->fail ( 'INI File could not be read.');
        }

        return $_retVal;
    }

    function test_Server_Authentication()
    {
       /**
        * Default return value
        *
        * Returns constructed SELECT Object string or boolean upon failure
        * Default value is set at FALSE
        *
        * @var mixed $_retVal Indicates if Object was created or not
        * @access private
        * @static
        */
        $_retVal = false;

        if ( $this->SMTPs->setConfig($this->ini_path) )
        {
            if ($this->SMTPs->_server_connect() )
            {
                if ( $this->SMTPs->_server_authenticate() )
                {
                    $_retVal = true;
                }
                else
                {
                    $this->fail ( $this->SMTPs->getErrors());
                }
            }
            else
            {
                $this->fail ( $this->SMTPs->getErrors() );
            }
        }
        else
        {
            $this->fail ( 'INI File could not be read.');
        }

        return $_retVal;
    }

};


// ***********************************************************************
// ***********************************************************************

// $this->fail ( 'failed' );


// Define Test Classes
$propertiesSuite = new PHPUnit_TestSuite( "SMTPsPropertiesTest" );
// $boundariesSuite = new PHPUnit_TestSuite( "SMTPsBoundariesTest" );
$failuresSuite   = new PHPUnit_TestSuite( "SMTPsFailuresTest" );
$contentSuite    = new PHPUnit_TestSuite( "SMTPsMessageContentDisplay" );
$authSuite       = new PHPUnit_TestSuite( "SMTPsServerAuthentication" );

// Insert Suites into Test Harness
$display = new PHPUnit_GUI_HTML(array( $propertiesSuite,
                                  //     $boundariesSuite,
                                       $failuresSuite,
                                       $contentSuite,
                                       $authSuite ) );

// Display Test Harness
$display->show();

// =============================================================
// =============================================================
// ** CVS Version Control Info

 /**
  * $Log: SMTPs_test.php,v $
  * Revision 1.7  2005/08/26 19:38:01  jswalter
  *  - pulled config data into an external file
  *
  * Revision 1.6  2005/08/22 15:57:37  braverock
  * - remove debug code
  *
  * Revision 1.5  2005/08/19 20:42:39  jswalter
  *  - added 'SMTPsServerAuthentication' to test server connection and authentication
  *
  * Revision 1.4  2005/08/19 15:19:40  jswalter
  *  - added 'SMTPsMessageContentDisplay' to display message content construction
  *
  * Revision 1.3  2005/08/19 15:17:00  walter
  *  - corrected 'SMTPsBoundariesTest' comment error
  *
  * Revision 1.2  2005/08/19 00:21:04  jswalter
  *  - commented 'boundaries' suite until it is further defined
  *  - completed prelim 'failures' suite
  *
  * Revision 1.1  2005/08/18 15:59:20  walter
  *  - initial commit
  *  - full property access
  *  - complete property testing
  *  - shell for 'boundary' testing
  *  - shell for 'failure' testing
  *
  *
  */
?>