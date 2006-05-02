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
 * $Id: utils-companies.php,v 1.14 2006/05/02 00:44:04 vanmer Exp $
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
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @return $company_id of newly created or modified company, or false if failure occured
 */
function add_update_company($con, $company_data, $magic_quotes=false)
{
   /**
    * Default return value
    *
    * Returns Record ID or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if company data was INSERTed or UPDATEd or not
    * @access private
    * @static
    */
    $_retVal = false;
    global $session_user_id;
    // This needs a company name
    if ( $company_data['company_name'] )
    {
        // Define who is adding/updating this record
//        global $session_user_id;
//        $company_data['user_id'] = $session_user_id;

        // Add/update/retrieve address ifno
        $_address_info = add_update_address($con, $company_data );

        // Insert address ID into data set
        $_address_id = $_address_info[$_address_info['primarykey']];

        $company_info = pull_company_fields ( $company_data );


        //set some sensible defaults if they are not yet set
        if (!array_key_exists('industry_id', $company_info)) {
            $company_info['industry_id']=1;
        }
        if (!array_key_exists('crm_status_id', $company_info)) {
            $company_info['crm_status_id']=1;
        }
        if (!array_key_exists('rating_id', $company_info)) {
            $company_info['rating_id']=1;
        }
        if (!array_key_exists('account_status_id', $company_info)) {
            $company_info['account_status_id']=1;
        }
        if (!array_key_exists('company_source_id', $company_info)) {
            $company_info['company_source_id']=1;
        }
        if (!array_key_exists('user_id', $company_info)) {
            $company_info['user_id']=$session_user_id;
        }


        // Because the way ADOdb is written, we can't let it take care of force
        // updates if a record exists, and INSERT if the record does not exist.
        // We have to do the checking, so... we need to use the XRMS version...
        $_retVal = __record_add_update ( $con, 'companies', 'company_name', $company_info, $magic_quotes );
    }

    // Place address ID into data set
    $_retVal['address_id'] = $_address_id;

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
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @return $company_id with newly created company, or false if failure occured
 */
function add_company($con, $company_data, $magic_quotes=false)
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
        $sql = $con->getInsertSQL($table, $company_data, $magic_quotes);
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
    $sql = "SELECT * FROM companies";

    if (array_key_exists('company_id',$company_data) AND trim($company_data['company_id'])) {
        $company= get_company($con, $company_id, $return_recordset);
        if ($company AND is_array($company)) return array($company);
        else return $company;
    } else {

        $extra_where=array();
        foreach ($company_data as $ckey=>$cval) {
            switch ($ckey) {
                case 'legal_name':
                case 'company_name':
                case 'tax_id':
                case 'profile':
                    unset($company_data[$ckey]);
                    $extra_where[]="$ckey LIKE ".$con->qstr($cval);
                break;
            }
        }
        if (!$show_deleted) $company_data['company_record_status']='a';

        /** CLEAN INCOMING DATA FIELDS ***/
        $company_phone_fields=array('work_phone','cell_phone','home_phone','fax');
        $phone_clean_count=clean_phone_fields($company_data, $company_phone_fields);

        if (count($extra_where)==0) $extra_where=false;
        $wherestr=make_where_string($con, $company_data, $tablename, $extra_where);
    }
    if ($wherestr) $sql.=" WHERE $wherestr";

    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    if ($rst->EOF) return false;
    else {
    if ($return_recordset) return $rst;
        while (!$rst->EOF) {
            $ret[]=$rst->fields;
            $rst->movenext();
        }
    }
    if (count($ret)>0) return $ret;
    else return false;

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
 * @return mixed with either an array of company fields, or a recordset object (false on failure)
*/
function get_company($con, $company_id, $return_rst=false, $include_extras=true)
{
    if (!$company_id)
        return false;

    $sql = 'SELECT companies.*';
    if ($include_extras) {
        $sql .=', cs.*,  account_status_display_html, account_status_short_name
                rating_short_name, rating_display_html,
                company_source_display_html, i.industry_pretty_name, companies.default_primary_address, ' .
                $con->Concat("u1.first_names", $con->qstr(' '), "u1.last_name") . " AS owner_username," .
                $con->Concat("u2.first_names", $con->qstr(' '), "u2.last_name") . " AS entered_by," .
                $con->Concat("u3.first_names", $con->qstr(' '), "u3.last_name") . " AS last_modified_by ";
    }
    $sql .= " FROM companies";
    if ($include_extras) {
        $sql .=" left outer join users u1 ON companies.user_id = u1.user_id
                    left outer join users u2 ON companies.entered_by = u2.user_id
                    left outer join users u3 ON companies.last_modified_by = u3.user_id
                    left outer join crm_statuses cs ON companies.crm_status_id = cs.crm_status_id
                    left outer join account_statuses as1 ON companies.account_status_id = as1.account_status_id
                    left outer join ratings r ON companies.rating_id = r.rating_id
                    left outer join company_sources cs2 ON companies.company_source_id = cs2.company_source_id
                    left outer join industries i ON companies.industry_id = i.industry_id";
    }
    $sql .=" WHERE companies.company_id = $company_id";

    $rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql); return false;
    } else {
        // Make sure we have a company record
        if ($rst->NumRows()) {
            if ($return_rst) {
                return $rst;
            } else return $rst->fields;
        } else return false;
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
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @return boolean specifying if update succeeded
 */
function update_company($con, $company_data, $company_id=false, $company_rst=false, $magic_quotes=false)
{

    global $session_user_id;

    if (!$company_data) return false;
    if (!$company_rst) {
        $company_rst=get_company($con, $company_id, true, false);
    }
    if (!$company_rst) return false;

    /** CLEAN INCOMING DATA FIELDS ***/
    $company_phone_fields=array('work_phone','cell_phone','home_phone','fax');
    $phone_clean_count=clean_phone_fields($company_data, $company_phone_fields);

    $rec['last_modified_at'] = time();
    $rec['last_modified_by'] = $session_user_id;


    $upd = $con->GetUpdateSQL($company_rst, $company_data, false, $magic_quotes);
    if ($upd) {
        $rst=$con->execute($upd);
        if (!$rst) { db_error_handler($con, $upd); return false; }
    }


    //this will run whether or not base company changed
    $param = array($company_rst, $company_data);
    do_hook_function('company_edit_2', $param);

    add_audit_item($con, $session_user_id, 'updated', 'companies', $company_id, 1);

    return true;

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
        $sql = "UPDATE companies SET companies_record_status=" . $con->qstr('d');
    }
    $sql .= "  WHERE company_id=$company_id";

    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

    return true;
}

/**
  * Function to update the companies table, adding the unknown company entry (potentially moving an existing company out of the way)
  * @param adodbconnection $con with handle to the database
  * @param integer $company_id should be 1 unless being run from the test functions
**/

function update_unknown_company($con, $company_id=1) {
    $sql = "SELECT * FROM companies WHERE company_id=$company_id";
    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

        $now=time();
        $unknown_company_data=array('company_id'=>$company_id, 'company_name'=>'Unknown Company');
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
        //update: always change the company key (uncomment the conditional to re-enable this check
//        if (($rst->fields['company_name'] =='Bushwood Components') OR ($rst->fields['company_record_status']=='d')) {
            $ret=change_company_key($con, $company_id, false, $rst);
            if (!$ret) return _("Failed to upgrade company entry for unknown company.") . '  ' ._("New contacts with no company will be attach to Company:") .  " {$rst->fields['company_name']} " ._("by default").'<br>';
//        }
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
        return $new_company_id;
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

/**********************************************************************/

/**
 *
 * Retrieves Company "owner"
 *
 * If the Company does not have an "owner" set, then FALSE will be
 * returned indicating no "owner" has been defined.
 *
 * @param int $company_id  $company_id of Company to retrieve owner
 *
 * @return int $user_id  Company "owner" id
 */
function get_division_owner ( $company_id )
{
   /**
    * Default return value
    *
    * Returns user_id or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if owner was found was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    // We need something to work on
    if ( $company_id )
    {
        // Did we find the company
        if ( $_company_data = get_company( get_xrms_dbconnection(), $company_id ) )
        {
            // Retrieve "owner"
            if ( $_company_data['user_id'] > 0 )
            {
                $_retVal = $_company_data['user_id'];
            }
        }
    }

    return $_retVal;
};

/**********************************************************************/
/**
 *
 * Gets a division based on the database identifer if that division exists
 *
 * @param adodbconnection $con with handle to the database
 * @param integer $division_id with ID of the division to get details about
 * @param boolean $return_recordset indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of division fields, or a recordset object (false on failure)
*/
function get_division($con, $division_id, $return_rst = false)
{
   /**
    * Default return value
    *
    * Returns division data or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Object was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    // need something to work with
    if ( $con && ($division_id > 0) )
    {

        $sql = "SELECT * FROM company_division
                 WHERE division_id = $division_id";

        if ( ! $rst = $con->execute($sql) )
        {
            db_error_handler($con, $sql);
            return false;
        }
        else
        {
            // Does this need to send back the record set
            if ($return_rst)
                $_retVal = $rst;

            else
                $_retVal = $rst->fields;
        }
    }

    // return what we have
    return $_retVal;
};

/**********************************************************************/

/**
 *
 * Pulls only company field data from given array
 *
 * @param array $array_data array to retrieve company data from
 *
 * @return array $company_fields company "only" fields found in given array
 */
function pull_company_fields ( $array_data )
{
    global $session_user_id;
    if ( ! $array_data )
        return $array_data;

    // Retrieve only the field names we can handle
    $company_fields = array ( 'company_id'                => '',
                              'user_id'                   => '',
                              'company_source_id'         => '',
                              'industry_id'               => '',
                              'crm_status_id'             => '',
                              'rating_id'                 => '',
                              'account_status_id'         => '',
                              'company_name'              => '',
                              'company_code'              => '',
                              'legal_name'                => '',
                              'tax_id'                    => '',
                              'profile'                   => '',
                              'phone'                     => '',
                              'phone2'                    => '',
                              'fax'                       => '',
                              'url'                       => '',
                              'employees'                 => '',
                              'revenue'                   => '',
                              'credit_limit'              => '',
                              'terms'                     => '',
                              'default_primary_address'   => '',
                              'default_billing_address'   => '',
                              'default_shipping_address'  => '',
                              'default_payment_address'   => '',
                              'custom1'                   => '',
                              'custom2'                   => '',
                              'custom3'                   => '',
                              'custom4'                   => '',
                              'extref1'                   => '',
                              'extref2'                   => '',
                              'extref3'                   => '',
                            );

    // Now pull out the fields we need
    return array_intersect_key_2($company_fields, $array_data);

}


/** Include the misc utilities file */
include_once $include_directory . 'utils-addresses.php';


/**********************************************************************/

 /**
 * $Log: utils-companies.php,v $
 * Revision 1.14  2006/05/02 00:44:04  vanmer
 * - changed get function to do left outer joins on all non-critical tables
 * - changed update function to request simplified select query recordset, for use in getUpdateSQL
 *
 * Revision 1.13  2006/05/01 17:31:07  braverock
 * - update get_company fn to be suitable for use in companies/one.php
 * - change SQL construction to use left join for resiliency
 *
 * Revision 1.12  2006/04/26 02:14:21  vanmer
 * - added sensible defaults to a new company, if not provided
 *
 * Revision 1.11  2006/04/11 00:41:50  vanmer
 * - added needed PHPDoc parameters
 *
 * Revision 1.10  2006/04/05 19:48:12  vanmer
 * - added magic quote parameter to companies API
 * - fixed find_company to search appropriate fields
 *
 * Revision 1.9  2006/01/17 03:14:14  vanmer
 * - disabled check that only updates a company if it is deleted or Bushwood, when replacing for unknown company entry
 * - added parameter to allow unknown company to replace any company (for tests)
 *
 * Revision 1.8  2006/01/17 02:24:40  vanmer
 * - implemented update_company function
 * - properly implemented find_company function
 * - added tests for change_company_key to ensure update of companies works
 *
 * Revision 1.7  2005/12/20 07:51:21  jswalter
 *  - fleshed out 'add_update_company()' a bit more. adds/updates seems to work OK.
 * Bug 778
 *
 * Revision 1.6  2005/12/14 23:55:52  jswalter
 *  - added 'get_owner()' block
 *  - added 'pull_data()' block to retrieve fields only used in "company" table
 *
 * Revision 1.5  2005/12/13 20:13:52  vanmer
 * - fixed typo for delete
 * - changed case on company table to reflect all lowercase name of companies table
 *
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