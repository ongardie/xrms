<?php

  /**
   * Configuration file for the SMTPs Class Test harness
   *
   * @package SMTPs_Test
   *
   * @author Walter Torres <walter@torres.ws>
   *
   * @version $Revision: 1.1 $
   * @copyright copyright information
   * @license URL name of license
   *
   **/

    // Modify this path so this Test Suite can locate the Class INI file.
    // And don't forget to place valid data in the Class INI file.
    $this->ini_path = '../SMTPs.ini.php';


    // DO NOT MODIFY BELOW THIS LINE
    // =============================================================

    // SMTP server info
    $this->host = 'localhost';
    $this->port = 21;
    $this->id = 'tester';
    $this->pw = 'testing';

    // eMail addresses and their structures
    $this->sender = '<test_sender@test.com>';
    $this->sender_array = array('org'=>'<test_sender@test.com>',
                                'addr'=>'<test_sender@test.com>',
                                'host'=>'test.com',
                                'user'=>'test_sender');
    $this->sender_host = 'test.com';
    $this->sender_user = 'test_sender';

    $this->to_domain = 'test.com';

    $this->to_full_address = '"send to" <to@test.com>';
    $this->to_address = 'to@test.com';
    $this->to_user = 'to';
    $this->to_address_array = array('org'=>'"send to" <to@test.com>',
                                    'real'=>"send to",
                                    'addr'=>'<to@test.com>',
                                    'host'=>'test.com',
                                    'user'=>'to');
    $this->to_array = array('test.com'=>array('to'=>array('to'=>'send to')));

    $this->cc_full_address = '"send cc" <cc@test.com>';
    $this->cc_address = 'cc@test.com';
    $this->cc_user = 'cc';
    $this->cc_address_array = array('org'=>'"send cc" <cc@test.com>',
                                    'real'=>"send cc",
                                    'addr'=>'<cc@test.com>',
                                    'host'=>'test.com',
                                    'user'=>'cc');
    $this->cc_array = array('test.com'=>array('cc'=>array('cc'=>'send cc')));

    $this->bcc_full_address = '"send bcc" <bcc@test.com>';
    $this->bcc_address = 'bcc@test.com';
    $this->bcc_user = 'bcc';
    $this->bcc_address_array = array('org'=>'"send bcc" <bcc@test.com>',
                                     'real'=>"send bcc",
                                     'addr'=>'<bcc@test.com>',
                                     'host'=>'test.com',
                                     'user'=>'bcc');
    $this->bcc_array = array('test.com'=>array('bcc'=>array('bcc'=>'send bcc')));

    $this->rcpt_array = array($this->to_address,
                              $this->cc_address,
                              $this->bcc_address);

    // Message Subject
    $this->subject = "Test Subject";

    // Message Sensitivity
    $this->msgSensitivity = 1;
    $this->msgSensitivityResults = 'Personal';

    // Mesage Priority
    $this->msgPriority = 2;
    $this->msgPriorityResults = "Importance: High\r\nPriority: High\r\nX-Priority: 2 (High)\r\n";

    // Message Tranport Type - not yet implemented
    $this->transportType = 0;

    // Message Format
    $this->charSet = 'iso-8859-1';
    $this->transEncode = '7bit' ;

    // X-Header
    $this->xheader = 'X-test: test';

    // Plain Text Content
    $this->content = 'This is a test message';
    $this->contentType = 'plain';
    $this->contentRawArray = array('plain'=>array('mimeType'=>'text/plain',
                                                  'data'=>$this->content));
    $this->contentMsg = "Content-Type: text/plain; charset=\"iso-8859-1\"\r\nContent-Transfer-Encoding: 7bit\r\nContent-Disposition: inline\r\nContent-Description:  message\r\n\r\n$this->content\r\n";

    // HTML Content
    $this->contentHTML = '<b>This</b> is a <i>test</i> message';
    $this->contentHTMLType = 'html';
    $this->contentHTMLRawArray = array('html'=>array('mimeType'=>'text/html',
                                                     'data'=>$this->contentHTML));
    $this->contentHTMLMsg = "Content-Type: text/plain; charset=\"iso-8859-1\"\r\nContent-Transfer-Encoding: 7bit\r\nContent-Disposition: inline\r\nContent-Description:  message\r\n\r\n$this->contentHTML";

    // Word DOC File Content
    $this->contentDOC = $this->content;
    $this->fileName = 'test.doc';
    $this->mimeType = 'application/msword';
    $this->attachArray = array('attachment'=>array('test.doc'=>array('mimeType'=>'application/msword',
                                                                     'fileName'=>'test.doc',
                                                                     'data'=>'VGhpcyBpcyBhIHRlc3QgbWVzc2FnZQ==')));


// =============================================================
// =============================================================
// ** CSV Version Control Info

 /**
  * $Log: SMTPs_test_config.php,v $
  * Revision 1.1  2005/08/26 19:33:10  jswalter
  *  - pulled config data into its own external file
  *
  *
  */

?>