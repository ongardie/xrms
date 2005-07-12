<?php
/**
 * Form for creating a new file
 *
 * $Id: new.php,v 1.17 2005/07/12 16:38:19 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-files.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');


// Pull SESSION data
$session_user_id = session_check('','Create');

    // See if we have an error message from anywhere
    // $msg = isset($_GET['msg']) ? $_GET['msg'] : '';
    if ( isset($_GET['msg']) )
        $msg = $_GET['msg'];

    else if ( isset($_POST['msg']) )
        $msg = $_POST['msg'];

    else
        $msg = null;

// if 'act' is not defeined, this is our first time through
if ( $_POST['act'] == 'up' )
{

    // Make sure we have what we need
    if ( empty ($_POST['file_pretty_name']) )
    {
        $msg .= _("Please give a Display Name for this File") . '. ';
        $msg .= '<br />';
    }

    // Process Uploaded File
    else if ( $objUpFile = getFileUpLoad ( 'file1' ) )
    {
        // Pull File info
        $file_pretty_name = (strlen(trim($_POST['file_pretty_name'])) > 0) ? $_POST['file_pretty_name'] : $file_name;
        $file_name        = $objUpFile->getFilename();
        $file_type        = $objUpFile->getFileMimeType();
        $file_size        = $objUpFile->getFileSize();

        //save to database
        $rec = array();
        $rec['file_pretty_name']     = $file_pretty_name;
        $rec['file_description']     = $_POST['file_description'];
        $rec['file_name']            = $file_name;
        $rec['file_size']            = $objUpFile->getFileSize();
        $rec['file_type']            = $objUpFile->getFileMimeType();
        $rec['on_what_table']        = $_POST['on_what_table'];
        $rec['on_what_id']           = $_POST['on_what_id'];
        $rec['entered_at']           = time();
        $rec['entered_by']           = $session_user_id;
        $rec['modified_on']          = $rec['entered_at'];
        $rec['modified_by']          = $rec['entered_by'];

        // files plugin hook allows external storage of files.  see plugins/owl/README for example
        // params: (file_field_name, record associative array)
        $file_plugin_params = array('file1', $rec);
        do_hook_function('file_add_file', &$file_plugin_params);

        if($file_plugin_params['external_id']) {
            $rec['external_id'] = $file_plugin_params['external_id'];
        }

        // Make DB connection
        $con = &adonewconnection($xrms_db_dbtype);
        $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
        // $con->debug = 1;

        // INSERT values into table
        $tbl = 'files';
        $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
        $con->execute($ins);

        // What ID where we given
        $file_id = $con->insert_id();

        // Now we need to UPDATE that same record
        // update the file record
        $sql = "SELECT * FROM files WHERE file_id = $file_id";
        $rst = $con->execute($sql);

        // We need to RENAME the 'file_filesystem_name' name with the record ID
        $rec = array();
        $rec['file_filesystem_name'] = $file_id . '_' . $file_name;

        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);
        $con->close();

        // The file needs to be renamed to add the record index to it
        rename_file ( $file_name, $rec['file_filesystem_name'] );
    }   // if ( $objUpFile = getFileUpLoad ( 'file1' ) )

    if (! $msg)
    {
        // go back to our orginal page
        header("Location: " . $http_site_root . $_POST['return_url']);

        // Just to make sure we stop here
        exit;
    }

}

// First time through here, or we have an error to fix

    // Inbound DB info
    $on_what_table = $_POST['on_what_table'];
    $on_what_id    = $_POST['on_what_id'];
    $return_url    = $_POST['return_url'];

    $con = &adonewconnection($xrms_db_dbtype);
    $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

    if ($on_what_table == 'opportunities')
    {
        $sql = "SELECT opportunity_title
                    AS attached_to_name
                FROM opportunities
                WHERE opportunity_id = $on_what_id";
    }
    elseif ($on_what_table == 'cases')
    {
        $sql = "SELECT case_title
                    AS attached_to_name
                FROM cases
                WHERE case_id = $on_what_id";
    }
    elseif ($on_what_table == 'companies')
    {
        $sql = "SELECT company_name
                    AS attached_to_name
                FROM companies
                WHERE company_id = $on_what_id";
    }
    elseif ($on_what_table == 'contacts')
    {
        $sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . "
                    AS attached_to_name
                FROM contacts
                WHERE contact_id = $on_what_id";
    }
    elseif ($on_what_table == 'campaigns')
    {
        $sql = "SELECT campaign_title
                    AS attached_to_name
                FROM campaigns
                WHERE campaign_id = $on_what_id";
    }
    else
    {
        $table_name = table_name($on_what_table);
        $table_name = $con->Concat(implode(", ' ', ", table_name($on_what_table)));
        $table_singular = make_singular($on_what_table);

        if ($table_singular AND $table_name)
        {
            $sql = "SELECT $table_name
                        AS attached_to_name from $on_what_table
                    WHERE {$table_singular}_id=$on_what_id";
        }
    }

    $rst = $con->execute($sql);

    if ($rst) {
    if ( !$rst->EOF ) {
        $attached_to_name = $rst->fields['attached_to_name'];
    } else {
        $attached_to_name = '';
    }
    $rst->close();
    }

    $con->close();

    // Get actual FORM
    include_once 'edit-form.php';


// ========================================================================
// ========================================================================

/**
 * $Log: new.php,v $
 * Revision 1.17  2005/07/12 16:38:19  braverock
 * - remove spurious debug.php include
 *
 * Revision 1.16  2005/07/06 17:58:17  jswalter
 *  - pulled HTML form out to external file: 'edit-form.php'
 *  - looking for '$msg' in POST as well as GET
 *  - added file upload processing to this file, don't need 'new-2.php' anymore
 *  Bug 311
 *
 * Revision 1.15  2005/06/24 22:55:29  vanmer
 * - added link for arbitrary file link
 *
 * Revision 1.14  2005/05/04 14:36:53  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.13  2005/01/13 18:51:23  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.12  2004/08/03 18:05:56  cpsource
 * - Set mime type when database entry is created
 *
 * Revision 1.11  2004/07/30 12:59:19  cpsource
 * - Handle $msg in the standard way
 *   Fix problem with Date field displaying garbage because
 *     date was undefined, and if E_ALL is turned on.
 *
 * Revision 1.10  2004/07/25 16:40:31  johnfawcett
 * - added gettext calls
 *
 * Revision 1.9  2004/06/15 14:24:44  gpowers
 * - placed calendar setup code inside <script> tag
 *
 * Revision 1.8  2004/06/12 07:20:40  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.7  2004/06/04 17:28:03  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.6  2004/04/17 16:04:30  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.5  2004/04/16 22:22:06  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.4  2004/04/08 17:00:11  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 * Revision 1.3  2004/03/24 12:28:01  braverock
 * - allow editing of more file proprerties
 * - updated code provided by Olivier Colonna of Fontaine Consulting
 * - add phpdoc
 *
 */
?>
