<?php

// =============================================================
// CVS Id Info
// $Id: SMTPs.php,v 1.5 2005/04/13 15:23:59 jswalter Exp $

  /**
   * Class SMTPs
   *
   * Class to construct and send SMTP compliant email, even to a secure
   * SMTP server, regardless of platform.
   *
   * Goals:
   *  - mime compliant
   *  - multiple Reciptiants
   *    - TO
   *    - CC
   *    - BCC
   *  - multi-part message
   *    - plain text
   *    - HTML
   *    - attachements
   *  - GPG access
   *
   * This Class is based off of 'SMTP PHP MAIL'
   *    by Dirk Paehl, http://www.paehl.de
   *
   * @package SMTPs
   *
   * @tutorial /path/to/tutorial.php Complete Class tutorial
   * @example url://path/to/example.php description
   *
   * @reference http://db.ilug-bom.org.in/lug-authors/philip/docs/mail-stuff/smtp-intro.html
   * @reference http://www.gordano.com/kb.htm?q=344
   * @reference http://www.gordano.com/kb.htm?q=803
   *
   * @author Walter Torres <walter@torres.ws> [with a *lot* of help!]
   *
   * @version $Revision: 1.5 $
   * @copyright copyright information
   * @license URL name of license
   *
   **/

// =============================================================
// ** Class Constants

   /**
    * Version number of Class
    * @const SMTPs_VER
    *
    */
    define('SMTPs_VER', '1.5', false);

   /**
    * SMTPs Success value
    * @const SMTPs_SUCCEED
    *
    */
    define('SMTPs_SUCCEED', true, false);

   /**
    * SMTPs Fail value
    * @const SMTPs_FAIL
    *
    */
    define('SMTPs_FAIL', false, false);


// =============================================================
// ** Error codes and messages

   /**
    * Improper parameters
    * @const SMTPs_INVALID_PARAMETERS
    *
    */
    define('SMTPs_INVALID_PARAMETERS', 50, false);


// =============================================================
// =============================================================
// ** Class

  /**
   * Class SMTPs
   *
   * Class to construct and send SMTP compliant email, even to
   * a secure SMTP server, regardless of platform.
   *
   * @package SMTPs
   *
   **/
class SMTPs
{
// =============================================================
// ** Class Properties

   /**
    * Property private string $_smtpsHost
    *
    * @property private string Host Name or IP of SMTP Server to use
    * @name $_smtpsHost
    *
    * Host Name or IP of SMTP Server to use. Default value of 'localhost'
    * This can be defined via a INI file or via a setter method
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_smtpsHost = 'localhost';

   /**
    * Property private int $_smtpsPort
    *
    * @property private int SMTP Server Port definition. 25 is default value
    * @name var_name
    *
    * SMTP Server Port definition. 25 is default value
    * This can be defined via a INI file or via a setter method
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_smtpsPort = '25';

   /**
    * Property private string $_smtpsID
    *
    * @property private string Secure SMTP Server access ID
    * @name $_smtpsID
    *
    * Secure SMTP Server access ID
    * This can be defined via a INI file or via a setter method
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_smtpsID = null;

   /**
    * Property private string var $_smtpsPW
    *
    * @property private string Secure SMTP Server access Password
    * @name var $_smtpsPW
    *
    * Secure SMTP Server access Password
    * This can be defined via a INI file or via a setter method
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_smtpsPW = null;

   /**
    * Property private string var $_msgFrom
    *
    * @property private string Who sent the Message
    * @name var $_msgFrom
    *
    * Who sent the Message
    * This can be defined via a INI file or via a setter method
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_msgFrom = null;

   /**
    * Property private string var $_msgReplyTo
    *
    * @property private string Where are replies and errors to be sent to
    * @name var $_msgReplyTo
    *
    * Where are replies and errors to be sent to
    * This can be defined via a INI file or via a setter method
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_msgReplyTo = null;

   /**
    * Property private array var $_msgRecipients
    *
    * @property private array Who will the Message be sent to; TO, CC, BCC
    * @name var $_msgRecipients
    *
    * Who will the Message be sent to; TO, CC, BCC
    * Multi-diminsional array containg addresses the message will
    * be sent TO, CC or BCC
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_msgRecipients = null;


   /**
    * Property private string var $_msgSubject
    *
    * @property private string Message Subject
    * @name var $_msgSubject
    *
    * Message Subject
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_msgSubject = null;

   /**
    * Property private string var $_msgContent
    *
    * @property private string Message Content
    * @name var $_msgContent
    *
    * Message Content
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_msgContent = null;

   /**
    * Property private int var $_msgSensitivity
    *
    * @property private string Message Sensitivity
    * @name var $_msgSensitivity
    *
    * Message Sensitivity
    * Defaults to ZERO - None
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_msgSensitivity = 0;

   /**
    * Property private array var $_arySensitivity
    *
    * @property private array Sensitivity string values
    * @name var $_arySensitivity
    *
    * Message Sensitivity
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_arySensitivity = array (false, 'Personal', 'Private', 'Company Confidential' );

   /**
    * Property private int var $_msgPriority
    *
    * @property private int Message Priority
    * @name var $_msgPriority
    *
    * Message Sensitivity
    * Defaults to 3 - Normal
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_msgPriority = 3;

   /**
    * Property private array var $_aryPriority
    *
    * @property private array Priority string values
    * @name var $_aryPriority
    *
    * Message Priority
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_aryPriority = array ('Bulk', 'Highest', 'High', 'Normal', 'Low', 'Lowest' );


   /**
    * Property private string var $_msgXheader
    *
    * @property private array Custom X-Headers
    * @name var $_msgXheader
    *
    * Custom X-Headers
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_msgXheader = null;

   /**
    * Property private string var $_smtpsCharSet
    *
    * @property private string Character set
    * @name var $_smtpsCharSet
    *
    * Character set
    * Defaulted to 'iso-8859-1'
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_smtpsCharSet = 'iso-8859-1';


   /**
    * Property private string var $_smtpsTransEncode
    *
    * @property private string Character set
    * @name var $_smtpsTransEncode
    *
    * Content-Transfer-Encoding
    * Defaulted to '7bit'
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_smtpsTransEncode = '7bit';

   /**
    * Property private string var $_smtpsBoundry
    *
    * @property private string Boundry String for MIME seperation
    * @name var $_smtpsBoundry
    *
    * Boundry String for MIME seperation
    *
    * @access private
    * @static
    * @since 1.0
    *
    */
    var $_smtpsBoundry = null;



// =============================================================
// ** Class methods


   /**
    * Method public void sendMsg( void )
    *
    * Now send the message
    *
    * @name sendMsg()
    *
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param void
    * @return void
    *
    */
    function buildRCPTlist()
    {
        // Pull TO list
        $_aryToList = $this->getTO();

    }


   /**
    * Method public void sendMsg( void )
    *
    * Now send the message
    *
    * @name sendMsg()
    *
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param void
    * @return void
    *
    */
    function sendMsg ( )
    {
        // Create Message Boundry
        $this->_setBoundry();

        //See if we can connect to the SMTP server
        if( !$socket = fsockopen($this->getHost(), $this->getPort(), $errno, $errstr, 20) )
            die('Could not connect to smtp host [' . $this->getHost() . '] : ' . $errno . ' : ' . $errstr );

        // Check responce from Server
        $this->server_parse($socket, "220");

        // If a User ID (with or without password) is given, assume Authentication is needed
        if( !empty($this->_smtpsID) && !empty($this->_smtpsPW) )
        {
            // Send the RFC2554 specified EHLO.
            // This improvment as provided by SirSir to
            // accomodate both SMTP AND ESMTP capable servers
            fputs($socket, "EHLO " . $this->getHost() . "\r\n");
            $this->server_parse($socket, "250");

            // Send Authenticationto Server
            // Check for errors along the way
            fputs($socket, "AUTH LOGIN\r\n");
            $this->server_parse($socket, "334");

            fputs($socket, base64_encode($this->_smtpsID) . "\r\n");
            $this->server_parse($socket, "334");

            fputs($socket, base64_encode($this->_smtpsPW) . "\r\n");
            $this->server_parse($socket, "235");
        }

        // This is a "normal" SMTP Server "handshack"
        else
        {
            // Send the RFC821 specified HELO.
            fputs($socket, "HELO " . $this->getHost() . "\r\n");
            $this->server_parse($socket, "250");
        }

        // From this point onward most server response codes should be 250
        // Specify who the mail is from....
        // This has to be the raw email address, strip the "name" off
        fputs($socket, "MAIL FROM: " . $this->getFrom( 'addr' ) . "\r\n");
        $this->server_parse($socket, "250");

        // 'RCPT TO:' must be given a single address, so this has to loop
        // through the list of addresses, regardless of TO, CC or BCC
        // and send it out "single file"
        foreach ( $this->get_RCPT_list() as $_address )
        {
            fputs( $socket, 'RCPT TO: <' . $_address . ">\r\n" );

            // After each 'RCPT TO:' is sent, we need to make sure it was kosher,
            // if not, the whole message will fail
            // If any email address fails, we will need to RESET the connection,
            // mark the last address as "bad" and start the address loop over again.
            // If any address fails, the entire message fails.
            $this->server_parse( $socket, "250" );
        }

        // Ok now we tell the server we are ready to start sending data
        fputs($socket, "DATA\r\n");

        // Now any custom headers....
        fputs($socket, $this->getHeader());

        // This is the last response code we look for until the end of the message.
        $this->server_parse($socket, "354");

        // Ok now we are ready for the message...
        fputs($socket, $this->getBodyContent() );
        fputs($socket, $msg);

        // Ok the all the ingredients are mixed in let's cook this puppy...
        //fputs($socket, "\r\n.\r\n");
        $this->server_parse($socket, "250");

        // Now tell the server we are done and close the socket...
        fputs($socket, "QUIT\r\n");
        fclose($socket);
    }

// =============================================================
// ** Setter & Getter methods

// ** Basic System configuration

   /**
    * Method public void setConfig( mixed )
    *
    * The method is used to populate select class properties from either
    * a user defined INI file or the systems 'php.ini' file
    *
    * If a user defined INI is to be used, its complete path is passed
    * a the method single parameter. The INI can define and class properties
    * and user properties.
    *
    * If the systems 'php.ini' file is to be used, the method is called without
    * parameters. In this case, only HOST, PORT and FROM properties will be set
    * as they are the only properties that are defined within the 'php.ini'.
    *
    * If secure SMTP is to be used, the user ID and Password can be defined with
    * the user INI file, but the properties are not defined with the systems
    * 'php.ini'file, they must be defined via their setter methods
    *
    * This method can be called twice, if desired. Once without a parameter to
    * load the properties as defined within the systems 'php.ini' file, and a
    * second time, with a path to a user INI file for other properties to be
    * defined.
    *
    * @name setConfig()
    *
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param mixed $_strConfigPath path to config file or VOID
    * @return void
    *
    */
    function setConfig ( $_strConfigPath = null )
    {
        // if we have a path...
        if ( ! empty ($_strConfigPath) )
            include ( $_strConfigPath );

        else
        {
            $this->setHost ( ini_get ('SMTP') );
            $this->setPort ( ini_get ('smtp_port') );
            $this->setFrom ( ini_get ('sendmail_from') );
        }
    }


   /**
    * Method public void setHost( string )
    *
    * Defines the Host Name or IP of the Mail Server to use.
    * This is defaulted to 'localhost'
    *
    * @name setHost()
    *
    * @uses Class property $_smtpsHost
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_strHost Host Name or IP of the Mail Server to use
    * @return void
    *
    */
    function setHost ( $_strHost )
    {
        $this->_smtpsHost = $_strHost;
    }

   /**
    * Method public string getHost( void )
    *
    * Retrieves the Host Name or IP of the Mail Server to use
    *
    * @name getHost()
    *
    * @uses Class property $_smtpsHost
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_strHost Host Name or IP of the Mail Server to use
    *
    */
    function getHost ()
    {
        return $this->_smtpsHost;
    }

   /**
    * Method public void setPort( int )
    *
    * Defines the Port Number of the Mail Server to use
    * This is defaulted to '25'
    *
    * @name setPort()
    *
    * @uses Class property $_smtpsPort
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param int $_smtpsPort Port Number of the Mail Server to use
    * @return void
    *
    */
    function setPort ( $_strPort )
    {
        $this->_smtpsPort = $_strPort;
    }

   /**
    * Method public string getPort( void )
    *
    * Retrieves the Port Number of the Mail Server to use
    *
    * @name getPort()
    *
    * @uses Class property $_smtpsPort
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_smtpsPort Port Number of the Mail Server to use
    *
    */
    function getPort ()
    {
        return $this->_smtpsPort;
    }

   /**
    * Method public void setID( string )
    *
    * User Name for authentication on Mail Server
    *
    * @name setID()
    *
    * @uses Class property $_smtpsID
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_strID User Name for authentication on Mail Server
    * @return void
    *
    */
    function setID ( $_strID )
    {
        $this->_smtpsID = $_strID;
    }

   /**
    * Method public string getID( void )
    *
    * Retrieves the User Name for authentication on Mail Server
    *
    * @name getID()
    *
    * @uses Class property $_smtpsPort
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string _smtpsID User Name for authentication on Mail Server
    *
    */
    function getID ()
    {
        return $this->_smtpsID;
    }

   /**
    * Method public void setPW( string )
    *
    * User Password for authentication on Mail Server
    *
    * @name setPW()
    *
    * @uses Class property $_smtpsPW
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_strPW User Password for authentication on Mail Server
    * @return void
    *
    */
    function setPW ( $_strPW )
    {
        $this->_smtpsPW = $_strPW;
    }

   /**
    * Method public string getPW( void )
    *
    * Retrieves the User Password for authentication on Mail Server
    *
    * @name getPW()
    *
    * @uses Class property $_smtpsPW
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_smtpsPW User Password for authentication on Mail Server
    *
    */
    function getPW ()
    {
        return $this->_smtpsPW;
    }

   /**
    * Method public void setCharSet( string )
    *
    * Character set used for current message
    * Character set is defaulted to 'iso-8859-1';
    *
    * @name setCharSet()
    *
    * @uses Class property $_smtpsCharSet
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_strCharSet Character set used for current message
    * @return void
    *
    */
    function setCharSet ( $_strCharSet )
    {
        $this->_smtpsCharSet = $_strPW;
    }

   /**
    * Method public string getCharSet( void )
    *
    * Retrieves the Character set used for current message
    *
    * @name getCharSet()
    *
    * @uses Class property $_smtpsCharSet
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_smtpsCharSet Character set used for current message
    *
    */
    function getCharSet ()
    {
        return $this->_smtpsCharSet;
    }

   /**
    * Method public void setTransEncode( string )
    *
    * Content-Transfer-Encoding, Defaulted to '7bit'
    * This can be changed for 2byte characers sets
    *
    * @name setTransEncode()
    *
    * @uses Class property $_smtpsTransEncode
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_strTransEncode Content-Transfer-Encoding
    * @return void
    *
    */
    function setTransEncode ( $_strTransEncode )
    {
        $this->_smtpsTransEncode = $_strTransEncode;
    }

   /**
    * Method public string getTransEncode( void )
    *
    * Retrieves the Content-Transfer-Encoding
    *
    * @name getTransEncode()
    *
    * @uses Class property $_smtpsCharSet
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_smtpsTransEncode Content-Transfer-Encoding
    *
    */
    function getTransEncode ()
    {
        return $this->_smtpsTransEncode;
    }


// ** Message Construction

   /**
    * Method public void setFrom( string )
    *
    * FROM Address from which mail will be sent
    *
    * @name setFrom()
    *
    * @uses Class property $_msgFrom
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_msgFrom Address from which mail will be sent
    * @return void
    *
    */
    function setFrom ( $_strFrom )
    {
        $this->_msgFrom = $this->_strip_email ( $_strFrom );
    }

   /**
    * Method public string getFrom( void )
    *
    * Retrieves the Address from which mail will be sent
    *
    * @name getFrom()
    *
    * @uses Class property $_msgFrom
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  boolean $_strip To "strip" 'Real name' from address
    * @return string $_msgFrom Address from which mail will be sent
    *
    */
    function getFrom ( $_part = true )
    {
        $_retValue = '';

        if ( $_part === true )
             $_retValue = $this->_msgFrom;
        else
             $_retValue = $this->_msgFrom[$_part];

        return $_retValue;
    }

   /**
    * Method private array _buildAddrList( void )
    *
    * Inserts given addresses into structured format
    * This method takes a list of given addresses, via an array
    * or a COMMA delimted string, and inserts them into a highly
    * structured array. This array is designed to remove duplicate
    * addresses and to sort them by Domain.
    *
    * @name _buildAddrList()
    *
    * @uses Class property $_msgRecipients
    * @final
    * @access private
    *
    * @since 1.0
    *
    * @param string $_type TO, CC, or BCC lists to add addrresses into
    * @param mixed $_addrList Array or COMMA delimited string of addresses
    * @return void
    *
    */
    function _buildAddrList( $_type, $_addrList )
    {
        // Pull existing list
        $aryHost = $this->_msgRecipients;

        // Only run this if we have something
        if ( !empty ($_addrList ))
        {
            // $_addrList can be a STRING or an array
            if ( is_string ($_addrList) )
            {
                // This could be a COMMA delimited string
                if ( strstr ($_addrList, ',') )
                    // "explode "list" into an array
                    $_addrList = explode ( ',', $_addrList );

                // Stick it in an array
                else
                    $_addrList = array($_addrList);
            }

            // take the array of address and split them further
            foreach ( $_addrList as $_strAddr )
            {
                // Strip off the end '>'
                $_strAddr = str_replace ( '>', '', $_strAddr );

                // Seperate "Real Name" from eMail address
                $_tmpaddr = null;
                $_tmpaddr = split ( '\<', $_strAddr );

                // We have a "Real Name" and eMail address
                if ( count ($_tmpaddr) == 2 )
                {
                    $_tmpHost = split ( '@', $_tmpaddr[1] );
                    $_tmpaddr[0] = trim ( $_tmpaddr[0], ' ">' );
                    $aryHost[$_tmpHost[1]][$_type][$_tmpHost[0]] = $_tmpaddr[0];
                }
                // We only have an eMail address
                else
                {
                    // Strip off the beggining '<'
                    $_strAddr = str_replace ( '<', '', $_strAddr );

                    $_tmpHost = split ( '@', $_strAddr );
                    $_tmpHost[0] = trim ( $_tmpHost[0] );
                    $_tmpHost[1] = trim ( $_tmpHost[1] );

                    $aryHost[$_tmpHost[1]][$_type][$_tmpHost[0]] = '';
                }
            }
        }
        // replace list
        $this->_msgRecipients = $aryHost;
    }

   /**
    * Method private array _strip_email( string )
    *
    * Returns an array of the various parts of an email address
    *
    * This assumes a well formed address:
    * - "Real name" <username@domain.tld>
    * - "Real Name" is optional
    * - if "Real Name" does not exist, the square brackets are optional
    *
    * This will split an email address into 4 or 5 parts.
    * - $_aryEmail[org]  = orignal string
    * - $_aryEmail[real] = "real name" - if there is one
    * - $_aryEmail[addr] = address part "username@domain.tld"
    * - $_aryEmail[host] = "domain.tld"
    * - $_aryEmail[user] = userName
    *
    * @name _strip_email()
    *
    * @final
    * @access private
    *
    * @since 1.0
    *
    * @param void
    * @return array $_aryEmail An array of the various parts of an email address
    *
    */
    function _strip_email ( $_strAddr )
    {
        // Keep the orginal
        $_aryEmail['org'] = $_strAddr;

        // Set entire string to Lower Case
        $_strAddr = strtolower ( $_strAddr );

        // Drop "stuff' off the end
        $_strAddr = trim ( $_strAddr, ' ">' );

        // Seperate "Real Name" from eMail address, if we have one
        $_tmpAry = explode ( '<', $_strAddr );

        // Do we have a "Real name"
        if ( count ($_tmpAry) == 2 )
        {
            // We may not really have a "Real Name"
            if ( $_tmpAry[0])
                $_aryEmail['real'] = trim ( $_tmpAry[0], ' ">' );

            $_aryEmail['addr'] = $_tmpAry[1];
        }
        else
            $_aryEmail['addr'] = $_tmpAry[0];

        // Pull User Name and Host.tld apart
        list($_aryEmail['user'], $_aryEmail['host'] ) = explode ( '@', $_aryEmail['addr'] );

        // Put the brackets back around the address
        $_aryEmail['addr'] = '<' . $_aryEmail['addr'] . '>';

        return $_aryEmail;
    }

   /**
    * Method public array get_RCPT_list( void )
    *
    * Returns an array of bares addresses for use with 'RCPT TO:'
    *
    * @name get_RCPT_list()
    *
    * @uses Class property $_msgRecipients
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param void
    * @return array $_RCPT_list Returns an array of bares addresses
    *
    */
    function get_RCPT_list()
    {
        // walk down Recipients array and pull just email addresses
        foreach ( $this->_msgRecipients as $_host => $_list )
        {
            foreach ( $_list as $_subList )
            {
                foreach ( $_subList as $_name => $_addr )
                {
                    // build RCPT list
                    $_RCPT_list[] = $_name . '@' . $_host;
                }
            }
        }

        return $_RCPT_list;
    }

   /**
    * Method public array get_email_list( string )
    *
    * Returns an array of addresses for a specific type; TO, CC or BCC
    *
    * @name get_email_list()
    *
    * @uses Class property $_msgRecipients
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param mixed $_which Which collection of adresses to return
    * @return array $_RCPT_list Array of emaill address
    *
    */
    function get_email_list( $_which )
    {
        // walk down Recipients array and pull just email addresses
        foreach ( $this->_msgRecipients as $_host => $_list )
        {
            if ( $this->_msgRecipients[$_host][$_which] )

                foreach ( $this->_msgRecipients[$_host][$_which] as $_addr => $_realName )
                {

                    if ( $_realName )
                        $_realName = '"' . $_realName . '"';

                    $_RCPT_list[] = $_realName . ' <' . $_addr . '@' . $_host . '>';
                }

        }

        if ( $_RCPT_list )
            return implode(', ', $_RCPT_list);
    }

   /**
    * Method public void setTO( string )
    *
    * TO Address[es] inwhich to sent mail to
    *
    * @name setTO()
    *
    * @uses Class property $_msgRecipients
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param mixed $_addrTo TO Address[es] inwhich to sent mail to
    * @return void
    *
    */
    function setTO ( $_addrTo )
    {
        $this->_buildAddrList( 'to', $_addrTo );
    }

   /**
    * Method public string getTo( void )
    *
    * Retrieves the TO Address[es] inwhich to sent mail to
    *
    * @name getTo()
    *
    * @uses Class property $_msgRecipients
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_msgRecipients TO Address[es] inwhich to sent mail to
    *
    */
    function getTo ()
    {
        return $this->get_email_list( 'to' );
    }

   /**
    * Method public void setCC( string )
    *
    * CC Address[es] inwhich to sent mail to
    *
    * @name setCC()
    *
    * @uses Class property $_msgRecipients
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_msgRecipients CC Address[es] inwhich to sent mail to
    * @return void
    *
    */
    function setCC ( $_strCC )
    {
        $this->_buildAddrList( 'cc', $_strCC );
    }

   /**
    * Method public string getCC( void )
    *
    * Retrieves the CC Address[es] inwhich to sent mail to
    *
    * @name getCC()
    *
    * @uses Class property $_msgRecipients
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_msgRecipients CC Address[es] inwhich to sent mail to
    *
    */
    function getCC ()
    {
        return $this->get_email_list( 'cc' );
    }

   /**
    * Method public void setBCC( string )
    *
    * BCC Address[es] inwhich to sent mail to
    *
    * @name setBCC()
    *
    * @uses Class property $_msgRecipients
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_msgRecipients BCC Address[es] inwhich to sent mail to
    * @return void
    *
    */
    function setBCC ( $_strBCC )
    {
        $this->_buildAddrList( 'bcc', $_strBCC );
    }

   /**
    * Method public string getBCC( void )
    *
    * Retrieves the BCC Address[es] inwhich to sent mail to
    *
    * @name getBCC()
    *
    * @uses Class property $_msgRecipients
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_msgRecipients BCC Address[es] inwhich to sent mail to
    *
    */
    function getBCC ()
    {
        return $this->get_email_list( 'bcc' );
    }

   /**
    * Method public void setSubject( string )
    *
    * Message Subject
    *
    * @name setSubject()
    *
    * @uses Class property $_msgSubject
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_msgSubject Message Subject
    * @return void
    *
    */
    function setSubject ( $_strSubject )
    {
        $this->_msgSubject = $_strSubject;
    }

   /**
    * Method public string getHeader( void )
    *
    * Constructes and returns message header
    *
    * @name getHeader()
    *
    * @uses Class method getFrom() The FROM address
    * @uses Class method getTO() The TO address[es]
    * @uses Class method getCC() The CC address[es]
    * @uses Class method getBCC() The BCC address[es]
    * @uses Class method getSubject() The Message Subject
    * @uses Class method getSensitivity() Message Sensitivity
    * @uses Class method getPriority() Message Priority
    *
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string Complete message header
    *
    */
    function getHeader()
    {
        $_header = 'From: '       . $this->getFrom( 'org' ) . "\r\n"
                 . 'To: '         . $this->getTO()          . "\r\n";

        if ( $this->getCC() )
            $_header .= 'Cc: ' . $this->getCC()  . "\r\n";

        if ( $this->getBCC() )
            $_header .= 'Bcc: ' . $this->getBCC()  . "\r\n";

        $_header .= 'Subject: '    . $this->getSubject()     . "\r\n"
                 .  'Date: '       . date("r")               . "\r\n"
                 .  'Message-ID: <' . MD5( time() ) . '.SMPTs@' . $this->_siteDomain . ">\r\n";
//                 . 'Read-Receipt-To: '   . $this->getFrom( 'org' ) . "\r\n"
//                 . 'Return-Receipt-To: ' . $this->getFrom( 'org' ) . "\r\n";

        if ( $this->getSensitivity() )
            $_header .= 'Sensitivity: ' . $this->getSensitivity()  . "\r\n";

        if ( $this->_msgPriority != 3 )
            $_header .= $this->getPriority();

        $_header .= 'X-Mailer: SMTPs/PHP Mailer'                   . "\r\n"
                 .  'Mime-Version: 1.0'                            . "\r\n";

        return $_header;
    }

   /**
    * Method public string getSubject( void )
    *
    * Retrieves the Message Subject
    *
    * @name getSubject()
    *
    * @uses Class property $_msgSubject
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_msgSubject Message Subject
    *
    */
    function getSubject ()
    {
        return $this->_msgSubject;
    }

   /**
    * Method public void setBodyContent( string, string )
    *
    * Message Content
    *
    * @name setBodyContent()
    *
    * @uses Class property $_msgContent
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_msgContent Message Content
    * @return void
    *
    */
    function setBodyContent ( $strContent, $strType = 'text/plain' )
    {
        // Make RFC821 Compliant, replace bare linefeeds
        $strContent = preg_replace("/(?<!\r)\n/si", "\r\n", $strContent );

        $this->_msgContent[$strType] = $strContent;
    }

   /**
    * Method public string getBodyContent( void )
    *
    * Retrieves the Message Content
    *
    * @name getBodyContent()
    *
    * @uses Class property $_msgContent
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_msgContent Message Content
    *
    */
    function getBodyContent ()
    {
        // Generate a new boundry string
        $this->_setBoundry();

        // What type[s] of content do we have
        $_types = array_keys ( $this->_msgContent );

        // How many content types do we have
        $keyCount = count ( $_types );

        // If we have ZERO, we have a problem
        if( $keyCount === 0 )
            die ( "Sorry, no content" );

        // If we have ONE, we can use the simple format
        else if( $keyCount === 1 )
        {
            $content = 'Content-Type: ' . $_types[0] . '; charset="' . $this->getCharSet() . '"' . "\r\n"
                     . 'Content-Transfer-Encoding: ' . $this->getTransEncode() . "\r\n"
                     . 'Content-Disposition: inline' . "\r\n"
                     . 'Content-Description: message' . "\r\n"
                     . "\r\n"
                     . $this->_msgContent[$_types[0]] . "\r\n";
        }

        // If we have more than ONE, we use the multi-part format
        else if( $keyCount > 1 )
        {
            // Since this is an actual multi-part message
            // We need to define a content message boundry
            $content = 'Content-Type: multipart/alternative;' . "\r\n"
                     . '   boundary="' . $this->_getBoundry() . '"' . "\r\n"
                     . "\r\n"
                     . 'This is a multi-part message in MIME format.' . "\r\n";

            // Loop through message content array
            foreach ($this->_msgContent as $type => $_content )
            {
                $content .= "\r\n--" . $this->_getBoundry() . "\r\n"
                         . 'Content-Type: ' . $type . '; charset="' . $this->getCharSet() . '"' . "\r\n"
                         . 'Content-Transfer-Encoding: ' . $this->getTransEncode() . "\r\n"
                         . 'Content-Disposition: inline' . "\r\n"
                         . 'Content-Description: message' . "\r\n"
                         . "\r\n"
                         . $_content . "\r\n";
            }

            // Close message boundries
            $content .= "\r\n--" . $this->_getBoundry() . "\r\n";
        }

        // All email MUST end with a PERIOD on a line all by itself
        $content .= "\n.\n";

        return $content;
    }

   /**
    * Method public void setSensitivity( string )
    *
    * Message Content Sensitivity
    * Message Sensitivity values:
    *   - [0] None - default
    *   - [1] Personal
    *   - [2] Private
    *   - [3] Company Confidential
    *
    * @name setSensitivity()
    *
    * @uses Class property $_msgSensitivity
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_value Message Sensitivity
    * @return void
    *
    */
    function setSensitivity ( $_value = 0 )
    {
        $this->_msgSensitivity = $_value;
    }

   /**
    * Method public string getSensitivity( void )
    *
    * Returns Message Content Sensitivity string
    * Message Sensitivity values:
    *   - [0] None - default
    *   - [1] Personal
    *   - [2] Private
    *   - [3] Company Confidential
    *
    * @name getSensitivity()
    *
    * @uses Class property $_msgSensitivity
    * @uses Class property $_arySensitivity
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_msgSensitivity Message Sensitivity
    * @return void
    *
    */
    function getSensitivity()
    {
        return $this->_arySensitivity[$this->_msgSensitivity];
    }

   /**
    * Method public void setPriority( int )
    *
    * Message Content Priority
    * Message Priority values:
    *  - [0] 'Bulk'
    *  - [1] 'Highest'
    *  - [2] 'High'
    *  - [3] 'Normal' - default
    *  - [4] 'Low'
    *  - [5] 'Lowest'
    *
    * @name setPriority()
    *
    * @uses Class property $_msgPriority
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_value Message Priority
    * @return void
    *
    */
    function setPriority ( $_value = 0 )
    {
        $this->_msgPriority = $_value;
    }

   /**
    * Method public string getPriority( void )
    *
    * Message Content Priority
    * Message Priority values:
    *  - [0] 'Bulk'
    *  - [1] 'Highest'
    *  - [2] 'High'
    *  - [3] 'Normal' - default
    *  - [4] 'Low'
    *  - [5] 'Lowest'
    *
    * @name getPriority()
    *
    * @uses Class property $_msgPriority
    * @uses Class property $_aryPriority
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $_value Message Priority
    * @return void
    *
    */
    function getPriority()
    {
        return 'Importance: ' . $this->_aryPriority[$this->_msgPriority] . "\r\n"
             . 'Priority: '   . $this->_aryPriority[$this->_msgPriority] . "\r\n"
             . 'X-Priority: ' . $this->_msgPriority . ' (' . $this->_aryPriority[$this->_msgPriority] . ')' . "\r\n";
    }


   /**
    * Method public void setXheader( string )
    *
    * Message X-Header Content
    *
    * @name setXheader()
    *
    * @uses Class property $_msgXheader
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param string $strXdata Message X-Header Content
    * @return void
    *
    */
    function setXheader ( $strXdata )
    {
        $this->_msgXheader[] = $strXdata;
    }

   /**
    * Method public string getXheader( void )
    *
    * Retrieves the Message X-Header Content
    *
    * @name getXheader()
    *
    * @uses Class property $_msgContent
    * @final
    * @access public
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_msgContent Message X-Header Content
    *
    */
    function getXheader ()
    {
        return $this->_msgXheader;
    }

   /**
    * Method private void _setBoundry( string )
    *
    * Generates Random string for MIME message Boundry
    *
    * @name _setBoundry()
    *
    * @uses Class property $_smtpsBoundry
    * @final
    * @access private
    *
    * @since 1.0
    *
    * @param void
    * @return void
    *
    */
    function _setBoundry()
    {
        $this->_smtpsBoundry = "==multipart_x{" . md5(time()) . "}x_boundary==";
    }

   /**
    * Method private string _getBoundry( void )
    *
    * Retrieves the MIME message Boundry
    *
    * @name _getBoundry()
    *
    * @uses Class property $_smtpsBoundry
    * @final
    * @access private
    *
    * @since 1.0
    *
    * @param  void
    * @return string $_smtpsBoundry MIME message Boundry
    *
    */
    function _getBoundry()
    {
        return $this->_smtpsBoundry;
    }




//
// This function has been modified as provided
// by SirSir to allow multiline responses when
// using SMTP Extensions
//
function server_parse($socket, $response)
{

   $server_response = '';

   while ( substr($server_response,3,1) != ' ' )
   {
      if( !( $server_response = fgets($socket, 256) ) )
      {
         die("Couldn't get mail server response codes");
      }
   }

   if( !( substr($server_response, 0, 3) == $response ) )
   {
      die("Ran into problems sending Mail. Response: $server_response");
   }

}


// =============================================================
// ** Error handling methods




};

// =============================================================
// =============================================================
// ** CSV Version Control Info

 /**
  * $Log: SMTPs.php,v $
  * Revision 1.5  2005/04/13 15:23:59  jswalter
  *  - updated 'setConfig()' to handle external ini or 'php.ini'
  *
  * Revision 1.4  2005/03/21 05:38:56  jswalter
  *  - made 'CC' a conditional insert
  *  - made 'BCC' a conditional insert
  *  - fixed 'Message-ID'
  *
  * Revision 1.3  2005/03/21 05:24:27  jswalter
  *  - corrected 'getSensitivity()'
  *
  * Revision 1.2  2005/03/21 05:10:45  jswalter
  *  - modified '$_aryPriority[]' to proper values
  *
  * Revision 1.1  2005/03/17 20:40:57  jswalter
  *  - initial commit
  *  - cloned from PHP-YACS site
  *
  * Revision 1.6  2005/03/15 17:34:06  walter
  *  - corrected Message Sensitivity property and method comments
  *  - added array to Message Sensitivity
  *  - added getSensitivity() method to use new Sensitivity array
  *  - created seters and getter for Priority with new Prioity value array property
  *  - changed config file include from 'include_once'
  *  - modified getHeader() to ustilize new Message Sensitivity and Priorty properties
  *
  * Revision 1.5  2005/03/14 22:25:27  walter
  *  - added references
  *  - added Message sensitivity as a property with Getter/Setter methods
  *  - boundry is now a property with Getter/Setter methods
  *  - added 'builtRCPTlist()'
  *  - 'sendMsg()' now uses Object properties and methods to build message
  *  - 'setConfig()' to load external file
  *  - 'setForm()' will "strip" the email address out of "address" string
  *  - modifed 'getFrom()' to handle "striping" the email address
  *  - '_buildArrayList()' creates a multi-dimensional array of addresses
  *    by domain, TO, CC & BCC and then by User Name.
  *  - '_strip_email()' pulls email address out of "full Address" string'
  *  - 'get_RCPT_list()' pulls out "bare" emaill address form address array
  *  - 'getHeader()' builds message Header from Object properties
  *  - 'getBodyContent()' builds full messsage body, even multi-part
  *
  * Revision 1.4  2005/03/02 20:53:35  walter
  *  - core Setters & Getters defined
  *  - added additional Class Properties
  *
  * Revision 1.3  2005/03/02 18:51:51  walter
  *  - added base 'Class Properties'
  *
  * Revision 1.2  2005/03/01 19:37:52  walter
  *  - CVS logging tags
  *  - more comments
  *  - more "shell"
  *  - some constants
  *
  * Revision 1.1  2005/03/01 19:22:49  walter
  *  - initial commit
  *  - basic shell with some commets
  *
  */

?>
