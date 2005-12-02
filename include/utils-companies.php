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
 * $Id: utils-companies.php,v 1.2 2005/12/02 01:53:32 vanmer Exp $
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
 * These 'companies' tables fields are required.
 * This method will fail without them.

 *
 * These fields are optional, some may be derived from other fields if not defined.

 *
 * Do not define these fields, they are auto-defined
 * - entered_at              - when was record created
 * - entered_by              - who created the record
 * - last_modified_at        - when was record modified - this will be the same as 'entered_at'
 * - last_modified_by        - who modified the record  - this will be the same as 'entered_by'
 * - company_record_status   - the database defaults this to [a] Active
 *
 * @param adodbconnection $con with handle to the database
 * @param array $company_data with data about the company, to add
 *
 * @return $company_id of newly created or modified company, or false if failure occured
 */
function add_update_company($con, $company_data)
{
    global $session_user_id;




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
    global $session_user_id;


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
function find_company($con, $company_data, $show_deleted=false, $return_recordset=false)
{


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
    if (!$rst)
        db_error_handler($con, $sql); return false;
    else {
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

 /**
 * $Log: utils-companies.php,v $
 * Revision 1.2  2005/12/02 01:53:32  vanmer
 * - added XRMS_API package tag
 *
 * Revision 1.1  2005/11/22 20:38:34  jswalter
 *  - Initial revision of an API for managing companies in XRMS
 *
 *
**/
 ?>