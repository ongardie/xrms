<?php
/**
 * Utility functions for manipulating Divisions
 *
 * These functions create, retrieve, delete and modify divisions
 * This file should be included anywhere divisions need to be created or modified
 *
 * @author Aaron van Meerten
 * @package XRMS_API
 *
 * $Id: utils-division.php,v 1.2 2005/12/15 00:20:21 jswalter Exp $
 *
 */


/**********************************************************************/
/**
 *
 * Adds or modifies a division within XRMS, based on array of data about the division
 *
 * Define this field if the record should be updated, otherwise leave out of array
 * - division_id             - division ID, once a division record is created
 *
 * These 'divisions' table fields are required.
 * This method will fail without them.
 * - division_name           - Division Name
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * Unless otherwise stated, these fields will default to NULL.
 * - user_id                 - "Account Owner" of division data, Defaults to parent Company Owner
 * - company_source_id       - [FK] Company Source Type, Default '1'
 * - industry_id             - [FK] Industry Type, Default '1'
 * - company_id              - [FK] Company this Division belongs to
 * - address_id              - [FK] Default Address
 * - description             - Description of Division
 *
 * Do not define these fields, defined values will be ignored
 * - entered_at              - when was record created
 * - entered_by              - who created the record
 * - last_modified_at        - when was record modified - this will be the same as 'entered_at' on ADD
 * - last_modified_by        - who modified the record  - this will be the same as 'entered_by' on ADD
 * - division_record_status  - the database defaults this to [a] Active
 *
 * @param adodbconnection $con with handle to the database
 * @param array $division_data with data about the division, to add
 *
 * @return $division_id of newly created or modified division, or false if failure occured
 */
function add_update_division($con, $division_data)
{
   /**
    * Default return value
    *
    * Returns Record ID or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Division was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    // This needs a DB connection Object, an array of division data and a division name
    if ( $con && $division_data && $division_data['division_name'] )
    {

    }

    // Send back what we have
    return $_retVal;
};


/**
 *
 * Adds a division to the system, based on array of data about the division
 * Runs hook functions and adds audit items when complete
 *
 * @param adodbconnection $con with handle to the database
 * @param array $division_data with data about the division, to add
 *
 * @return $division_id with newly created division, or false if failure occured
 */
function add_division($con, $division_data)
{
   /**
    * Default return value
    *
    * Returns division_id or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Object was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    // 'division_name' is required, without that, we have nothing to work with
    if ( ($division_data['division_name']) && ( strlen($division_data['division_name']) > 0 ) )
    {

    }

    // return what we have
    return $_retVal;

};

/**********************************************************************/
/**
 *
 * Searches for a division based on data about the division
 *
 * @param adodbconnection $con with handle to the database
 * @param array $division_data with fields to search for
 * @param boolean $show_deleted specifying if deleted divisions should be included (defaults to false)
 * @param boolean $return_recordset indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of division records, or a recordset object (false on failure)
*/
function find_division($con, $division_data, $show_deleted = false, $return_recordset = false)
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
    if ( $con && $division_data )
    {

    }

    // return what we have
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
 * Updates an division in XRMS from an associative array
 * Either an division_id must be explicitly set or an adodbrecordset for the record to be updated
 * must be passed in or the function will fail
 *
 * @param adodbconnection $con handle to the database
 * @param array $division_data with associative array defining division data to update
 * @param adodbrecordset $division_rst optionally providing a recordset to use for the update (required if not passing in an integer for $division_id)
 *
 * @return boolean specifying if update succeeded
 */
function update_division($con, $division_data, $division_rst = false)
{
   /**
    * Default return value
    *
    * Returns division_id or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Object was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    // need something to work with
    if ( $con && $division_data )
    {

    }

    // return what we have
    return $_retVal;
};

/**********************************************************************/
/**
 *
 * Deletes an division from XRMS, based on passed in division_id
 * Can delete division from database or mark as removed using record status
 *
 * @param adodbconnection $con handle to the database
 * @param integer $division_id identifying which division to delete
 * @param boolean $delete_from_database specifying if division should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 *
 * @return boolean indicating success of delete operation
 */
function delete_division($con, $division_id, $delete_from_database = false)
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

    }

    // return what we have
    return $_retVal;
};

/**********************************************************************/

/**
 *
 * Retrieves division "owner"
 *
 * If the Division does not have an "owner" set, the Divisions parent Company
 * will be checked and its owner used. But, if the Company does not have an
 * "owner" defined, then FALSE will be returned indicating no "owner"
 * has been defined.
 *
 * @param int $division_id  division_id of division to retrieve owner
 *
 * @return int $user_id  division "owner" id
 */
function get_division_owner ( $_division_id )
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
    if ( $_division_id )
    {
        // Did we find the Division
        if ( $_division_data = get_division( get_xrms_dbconnection(), $_division_id ) )
        {
            // If the owner is not set, check the Company for its owner
            if ( $_division_data['user_id'] )
            {
                $_retVal = $_division_data['user_id'];
            }
            // The owner is not set, check the Company for its owner
            else
            {
                /* Include the misc utilities file */
                global $include_directory;
                include_once $include_directory . 'utils-companies.php';

                 // If the owner is not set, set FALSE
                $_company_data = get_company_owner( $_division_data['company_id']);

                if ( $_company_data['user_id'] )
                {
                    $_retVal = $_company_data['user_id'];;
                }
            }
        }
    }

    return $_retVal;
};


/**********************************************************************/

/**
 *
 * Pulls only division field data from given array
 *
 * @param array $array_data array to retrieve division data from
 *
 * @return array $division_fields division "only" fields found in given array
 */
function pull_division_fields ( $array_data )
{
    if ( ! $array_data )
        return $array_data;

    // Retrieve only the field names we can handle
    $division_fields = array ( 'division_id'        => '',
                               'division_name'      => '',
                               'user_id'            => '',
                               'company_source_id'  => '',
                               'industry_id'        => '',
                               'company_id'         => '',
                               'address_id'         => '',
                               'description'        => '',
                             );

    // Now pull out the fields we need
    return array_intersect_key_2($division_fields, $array_data);
}

/**********************************************************************/

 /**
  * $Log: utils-division.php,v $
  * Revision 1.2  2005/12/15 00:20:21  jswalter
  *  - fleshed out "get_division()" and "get_division_owner()'
  *
  * Revision 1.1  2005/12/13 19:19:36  jswalter
  *  - Initial revision of an API for managing divisions within XRMS
  *
  *
  *
  */
 ?>