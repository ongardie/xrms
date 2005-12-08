<?php
/**
 * Utility functions for manipulating activities
 *
 * These functions create, retrieve, delete and modify companies
 * This file should be included anywhere companies need to be created or modified
 *
 * @author Aaron van Meerten
 * @package XRMS_API
 *
 * $Id: utils-companies.php,v 1.4 2005/12/08 22:49:47 vanmer Exp $
 *
 */


/**********************************************************************/
/**
 *
 * Adds or modifies a company within XRMS, based on array of data about the company
 *
 * Define this field if the record should be updated, otherwise leave out of array
 * - company_id              - Company ID, once a company record is created
 *
 * These 'companies' table fields are required.
 * This method will fail without them.
 * - company_name                - Company Name
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * Unless otherwise stated, these fields will default to NULL.
 * - user_id                     - "Account Owner" of company data, Defaults to who created the record
 * - company_source_id           - [FK] Company Source Type, Default '1'
 * - industry_id                 - [FK] Industry Type, Default '1'
 * - crm_status_id               - [FK] CRM Status Type, Default '1'
 * - rating_id                   - [FK] Ratings, Default '1'
 * - account_status_id           - [FK] Account Status, Default '1'
 * - company_code                - Internal Company Identifier, defaults to "C[compnay_id]"
 * - legal_name                  - Company Legal Name
 * - tax_id                      - Company Tax ID Number, or personal SSN
 * - profile                     - Description
 * - phone                       - Primary Phone
 * - phone2                      - Secondary Phone
 * - fax                         - FAX Number
 * - url                         - Company Web Address
 * - employees                   - Number of Employess
 * - revenue                     - Company Size or Income potential
 * - credit_limit                - Internal Credit Limit
 * - terms                       - Payable Terms (30/60/90), default NULL
 * - default_primary_address     - [FK] address_id from Address Table
 * - default_billing_address     - [FK] address_id from Address Table
 * - default_shipping_address    - [FK] address_id from Address Table
 * - default_payment_address     - [FK] address_id from Address Table
 * - custom1                     - Custom Field #1
 * - custom2                     - Custom Field #2
 * - custom3                     - Custom Field #3
 * - custom4                     - Custom Field #4
 * - extref1                     - External Reference #1 for system integration
 * - extref2                     - External Reference #2 for system integration
 * - extref3                     - External Reference #3 for system integration
 *
 * Do not define these fields, defined values will be ignored
 * - entered_at              - when was record created
 * - entered_by              - who created the record
 * - last_modified_at        - when was record modified - this will be the same as 'entered_at' on ADD
 * - last_modified_by        - who modified the record  - this will be the same as 'entered_by' on ADD
 * - company_record_status   - the database defaults this to [a] Active
 *
 * @param adodbconnection $con with handle to the database
 * @param array $company_data with data about the company, to add
 *
 * @return $company_id of newly created or modified company, or false if failure occured
 */
function add_update_company($con, $company_data)
{
   /**
    * Default return value
    *
    * Returns Record ID or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Statement was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    // This needs a DB connection Object, an array of company data and a company name
    if ( $con && $company_data && $company_data['company_name'] )
    {
        // Define who is adding/updating this record
        global $session_user_id;
        $company_data['user_id'] = $session_user_id;

        // Because the way ADOdb is written, we can't let it take care of force
        // updates if a record exists, and INSERT if the record does not exist.
        // We have to do the checking, so... we need to use the XRMS version...
        $_retVal = __record_add_update ( $con, 'companies', 'company_name', $company_data );
    }

    // Send back what we have
    return $_retVal;
};


/**
 *
 * Adds a company to the system, based on array of data about the company
 * Runs hook functions and adds audit items when complete
 *
 * @param adodbconnection $con with handle to the database
 * @param array $company_data with data about the company, to add
 *
 * @return $company_id with newly created company, or false if failure occured
 */
function add_company($con, $company_data)
{
   /**
    * Default return value
    *
    * Returns company_id or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Object was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    // 'company_name' is required, without that, we have nothing to work with
    if ( ($company_data['company_name']) && ( strlen($company_data['company_name']) > 0 ) )
    {
        // Session data
        global $session_user_id;
        $table='companies';
        $sql = $con->getInsertSQL($table, $company_data);
        if ($sql) {
            $rst=$con->execute($sql);
            if (!$rst) {db_error_handler($con, $sql); return false; }
            else {
                $ret=$con->Insert_ID();
                if ($ret)
                    return $ret;
                else return true;
            }
        } else return false;
    }

    // return what we have
    return $_retVal;

};

/**********************************************************************/
/**
 *
 * Searches for a company based on data about the company
 *
 * @param adodbconnection $con with handle to the database
 * @param array $company_data with fields to search for
 * @param boolean $show_deleted specifying if deleted companies should be included (defaults to false)
 * @param boolean $return_recordset indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of company records, or a recordset object (false on failure)
*/
function find_company($con, $company_data, $show_deleted = false, $return_recordset = false)
{

    $sql_fetch_company_id = "select comp.company_id,cont.contact_id from companies comp, contacts cont where
                            cont.company_id =  comp.company_id and
                            comp.company_name = '" . addslashes($company_name) ."' and ";
    if ( $contact_first_name = '' )
    {
        $sql_fetch_company_id .= "cont.first_names = '" . addslashes($contact_first_names) . "' and";
    }
    $sql_fetch_company_id .= " cont.last_name = '" . addslashes($contact_last_name) . "' and
                            cont.contact_record_status='a' and
                            comp.company_record_status='a' " ;

    //echo "\n<br><pre> "._("Search Complete").' '. $sql_fetch_company_id . "\n</pre>" ;

    $rst_company_id = $con->execute($sql_fetch_company_id);

    if ( $rst_company_id )
    {
        $company_id = $rst_company_id->fields['company_id'];
        $contact_id = $rst_company_id->fields['contact_id'];

        $rst_company_id->close();
    }
    else
    {
        $company_id = 0;
    }



};

/**********************************************************************/
/**
 *
 * Gets a company based on the database identifer if that company exists
 *
 * @param adodbconnection $con with handle to the database
 * @param integer $company_id with ID of the company to get details about
 * @param boolean $return_recordset indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of company fields, or a recordset object (false on failure)
*/
function get_company($con, $company_id, $return_rst=false)
{
    if (!$company_id)
        return false;

    $sql = "SELECT * FROM companIES WHERE company_id=$company_id";
    $rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql); return false;
    } else {
        if ($return_rst) {
            return $rst;
       } else return $rst->fields;
    }

    //shouldn't ever get here
    return false;
}

/**********************************************************************/
/**
 *
 * Updates an company in XRMS from an associative array
 * Either an company_id must be explicitly set or an adodbrecordset for the record to be updated
 * must be passed in or the function will fail
 *
 * @param adodbconnection $con handle to the database
 * @param array $company_data with associative array defining company data to update
 * @param integer $company_id optionally identifying company in the database (required if not passing in a ecordset to $company_rst)
 * @param adodbrecordset $company_rst optionally providing a recordset to use for the update (required if not passing in an integer for $company_id)
 *
 * @return boolean specifying if update succeeded
 */
function update_company($con, $company_data, $company_id=false, $company_rst=false)
{
    global $session_user_id;


};

/**********************************************************************/
/**
 *
 * Deletes an company from XRMS, based on passed in company_id
 * Can delete company from database or mark as removed using record status
 *
 * @param adodbconnection $con handle to the database
 * @param integer $company_id identifying which company to delete
 * @param boolean $delete_from_database specifying if company should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 *
 * @return boolean indicating success of delete operation
 */
function delete_company($con, $company_id, $delete_from_database=false) {
    if (!$company_id) return false;
    if ($delete_from_database) {
        $sql = "DELETE FROM companies";
    } else {
        $sql = "UPDATE companies SET ccompanies_record_status=" . $con->qstr('d');
    }
    $sql .= "  WHERE company_id=$company_id";

    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

    return true;
}

function update_unknown_company($con) {
    $sql = "SELECT * FROM companies WHERE company_id=1";
    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

        $now=time();
        $unknown_company_data=array('company_id'=>1, 'company_name'=>'Unknown Company');
        $unknown_company_data['company_record_status']='a';
        $unknown_company_data['industry_id']=1;
        $unknown_company_data['crm_status_id']=1;
        $unknown_company_data['rating_id']=3;
        $unknown_company_data['account_status_id']=4;
        $unknown_company_data['company_source_id']=1;
        $unknown_company_data['company_code']='NOCOMPANY';
        $unknown_company_data['user_id']=1;
        $unknown_company_data['entered_by']=1;
        $unknown_company_data['last_modified_by']=1;
        $unknown_company_data['entered_at']=$now;
        $unknown_company_data['last_modified_at']=$now;

//default_primary_address, default_billing_address, default_shipping_address, default_payment_address, user_id, company_source_id, crm_status_id, industry_id, account_status_id, rating_id, company_name, company_code, profile,entered_at, entered_by, last_modified_at, last_modified_by

    if (!$rst->EOF) {
        //already ran this upgrade, so return
        if ($rst->fields['company_name']=='Unknown Company') return '';

        //company exists, so if it's either the sample data default of Bushwood Components, or a deleted company, change the company key, then add new Unknown Company
        if (($rst->fields['company_name'] =='Bushwood Components') OR ($rst->fields['company_record_status']=='d')) {
            $ret=change_company_key($con, 1, false, $rst);
            if (!$ret) return _("Failed to upgrade company entry for unknown company.") . '  ' ._("New contacts with no company will be attach to Company:") .  " {$rst->fields['company_name']} " ._("by default").'<br>';
        }
    }

    //adding new company, since either old company has been moved or no old company was found
    $new_company_id=add_company($con, $unknown_company_data);
    if ($new_company_id) {
        return _("Upgraded company entry for Unknown Company.  New contacts will be attached here if no company is specified.") . '<br>';
    } else {
        return _("Failed to upgrade company entry for unknown company.");
    }
}

function change_company_key($con, $old_company_id, $new_company_id=false, $company_rst=false) {
    if (!$company_rst) {
        $sql = "SELECT * FROM companies WHERE company_id=$old_company_id";
        $company_rst=$con->execute($sql);
    }
    $company_data=$company_rst->fields;
    //unset old company id
    unset($company_data['company_id']);
    if ($new_company_id) {
        $company_data['company_id']=$new_company_id;
    }
    $ret=add_company($con, $company_data);
    if (!$ret) {
        //error moving company, so fail
        return false;
    }
    $new_company_id = $ret;

    //move all entities related to company
    $ret=change_company_key_related_tables($con, $old_company_id, $new_company_id);

    //let plugins also have a chance to update their keys
    $param=array($old_company_id, $new_company_id);
    do_hook_function('change_company_key',$param);

    if (!$ret) {
        //remove new company, updates did not succeed, remove new company from the database
        delete_company($con, $new_company_id);
        return false;
    } else {
        //update succeeded, remove old company from database entirely
        delete_company($con, $old_company_id, true);
        return true;
    }
}

function change_company_key_related_tables($con, $old_company_id, $new_company_id) {
    $table_list = list_db_tables($con);
    foreach ($table_list as $table) {
        $columns=$con->MetaColumns($table);
        if (($table!='companies') AND array_key_exists('COMPANY_ID',$columns)) {
            $sql = "UPDATE $table SET company_id=$new_company_id WHERE company_id=$old_company_id";
            $rst=$con->execute($sql);
            if (!$rst) { db_error_handler($con, $sql); return false; }
        }
    }
    return true;
}

 /**
 * $Log: utils-companies.php,v $
 * Revision 1.4  2005/12/08 22:49:47  vanmer
 * - added a working function (with no checks) for add_company
 * - this should be updated to do all the relevant checks and add sensible defaults
 * - added function to add a new unknown company at company_id 1, moving whatever company was already on company_id 1 to a new slot
 * - added code to change the key of a company from one company_id to another
 *
 * Revision 1.3  2005/12/08 22:09:05  jswalter
 *  - 'find_company()' is orginal and has not been modified
 *  - 'add_update_contact()' is new and does not work yet
 *  - 'add_company()' is new, does not wok, and may not even be completed
 *  - 'update_company ' is new, does not wok, and may not even be completed
 *
 * Revision 1.2  2005/12/02 01:53:32  vanmer
 * - added XRMS_API package tag
 *
 * Revision 1.1  2005/11/22 20:38:34  jswalter
 *  - Initial revision of an API for managing companies in XRMS
 *
 *
**/
 ?>