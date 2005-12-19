<?php
/**
 * Utility functions for manipulating activities
 *
 * These functions create, retrieve, delete and modify companies
 * This file should be included anywhere companies need to be created or modified
 *
 * @author Aaron van Meerten
 *
 * $Id: utils-addresses.php,v 1.1 2005/12/19 05:34:18 jswalter Exp $
 *
 */


/**********************************************************************/
/**
 *
 * Adds or modifies a address within XRMS, based on array of data about the address
 *
 * Define this field if the record should be updated, otherwise leave out of array
 * - address_id              - address ID, once a address record is created
 *
 * These 'companies' table fields are required.
 * This method will fail without them.
 * - address_name                - address Name
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * Unless otherwise stated, these fields will default to NULL.
 * - user_id                     - "Account Owner" of address data, Defaults to who created the record
 * - company_id                  - [FK] Company ID to which this address belongs to
 * - country_id                  - [FK] Country ID, this has many uses within XRMS
 * - address_name                - Label used to identify this address - defaults to "Main"
 * - address_body                - A 'non-standard' address format
 * - line1                       - First Line of address
 * - line2                       - Second Line of address
 * - city                        - City
 * - province                    - State or Province
 * - postal_code                 - ZIP/Postal Code
 * - address_type                - [FK] Address Type, commercial, residential, shipping - or others as defined - default [1] 'unknown'
 * - use_pretty_address          - t|f, whether to use the "non standard address" as defined in 'address_body' field - default [f]
 * - offset                      - GMT offset
 * - daylight_savings_id         - Does this area observe DST
 *
 * Do not define these fields, defined values will be ignored
 * - created_on                - when was record created
 * - created_by                - who created the record
 * - modified_on               - when was record modified - this will be the same as 'created_on' on ADD
 * - modified_by               - who modified the record  - this will be the same as 'created_by' on ADD
 * - addresses_record_status   - the database defaults this to [a] Active
 *
 * @param adodbconnection $con               ADOdb connection Object
 * @param array           $address_data      with data about the address to add
 * @param boolean         $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return $address_id with newly created address id, array of address records, a recordset object or false if failure occured
 */
function add_update_address($con, $address_data, $return_recordset = false )
{
   /**
    * Default return value
    *
    * Returns newly created address id, array of address records, a recordset object or false if failure occured
    * Default value is set at FALSE
    *
    * @var mixed $_retVal newly created address id, array of address records, a recordset object or false if failure occured
    * @access private
    * @static
    */
    $_retVal = false;

    // Retrieve address table fields from orginal data set
    $address_info = pull_address_fields ( $address_data );

    return $_retVal;
};


/**
 *
 * Adds a address to the system, based on array of data about the address
 * Runs hook functions and adds audit items when complete
 *
 * @param adodbconnection $con            ADOdb connection Object
 * @param array           $address_data   with data about the address, to add
 *
 * @return $address_id with newly created address id, or false if failure occured
 */
function add_address($con, $address_data)
{
   /**
    * Default return value
    *
    * Returns newly created address id, or false if failure occured
    * Default value is set at FALSE
    *
    * @var mixed $_retVal newly created address id, or false if failure occured
    * @access private
    * @static
    */
    $_retVal = false;

    if ( $con && $address_id )
    {
        global $session_user_id;

    }

    return $_retVal;
};

/**********************************************************************/
/**
 *
 * Searches for a address based on data about the address
 *
 * @param adodbconnection $con               ADOdb connection Object
 * @param array           $address_data      with fields to search for
 * @param boolean         $show_deleted      specifying if deleted companies should be included (defaults to false)
 * @param boolean         $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of an address record, or a recordset object (false on failure)
*/
function find_address($con, $address_data, $show_deleted = false, $return_recordset = false)
{
   /**
    * Default return value
    *
    * Returns an array of address records, or a recordset object (false on failure)
    * Default value is set at FALSE
    *
    * @var mixed $_retVal An array of address records, or a recordset object (false on failure)
    * @access private
    * @static
    */
    $_retVal = false;

   /**
    * Fields to search DB with
    *
    * Creates an array of table fields for SQL search of DB
    *
    * @var array $_retVal array of table fields
    * @access private
    * @static
    */
    $where_fields = array();

    foreach ($address_data as $_field => $_value) {
        switch ($_field) {
            case 'city':
            case 'province':
            case 'postal_code':
            case 'line1':
            case 'line2':
                $extra_where[$_field] = $_value;
        }
    }

    $_retVal = __record_find ( $con, $_table_name, $where_fields, $show_deleted, $return_recordset );

    return $_retVal;
};

/**********************************************************************/
/**
 *
 * Gets a address based on the database identifer if that address exists
 *
 * @param adodbconnection $con                ADOdb connection Object
 * @param integer         $address_id         with ID of the address to get details about
 * @param boolean         $return_recordset   indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of address fields, or a recordset object (false on failure)
*/
function get_address($con, $address_id, $return_rst=false)
{
    if (!$address_id)
        return false;

    $sql = "SELECT * FROM address WHERE address_id=$address_id";
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
 * Updates an address in XRMS from an associative array
 * Either an address_id must be explicitly set or an adodbrecordset for
 * the record to be updated must be passed in or the function will fail
 *
 * @param adodbconnection $con          ADOdb connection Object
 * @param array           $address_data with associative array defining address data to update
 * @param integer         $address_id   optionally identifying address in the database (required if not passing in a ecordset to $address_rst)
 * @param adodbrecordset  $address_rst  optionally providing a recordset to use for the update (required if not passing in an integer for $address_id)
 *
 * @return boolean specifying if update succeeded
 */
function update_address($con, $address_data, $address_id=false, $address_rst=false)
{
   /**
    * Default return value
    *
    * Returns a boolean of success or failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates success or failure
    * @access private
    * @static
    */
    $_retVal = false;

    if ( $con && $address_id )
    {
        global $session_user_id;

    }

    return $_retVal;
};

/**********************************************************************/
/**
 *
 * Deletes an address from XRMS, based on passed in address_id
 * Can delete address from database or mark as removed using record status
 *
 * @param adodbconnection $con                   ADOdb connection Object
 * @param integer         $address_id            Identifying which address to delete
 * @param boolean         $delete_from_database  Specifying if address should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 *
 * @return boolean indicating success of delete operation
 */
function delete_address($con, $address_id = false, $delete_from_database = false)
{
   /**
    * Default return value
    *
    * Returns a boolean of success or failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates success or failure
    * @access private
    * @static
    */
    $_retVal = false;

    if ( $con && $address_id )
    {

    }

    return $_retVal;
};

/**********************************************************************/

/**
 *
 * Pulls only address field data from given array
 *
 * @param array $array_data array to retrieve address data from
 *
 * @return array $address_fields address "only" fields found in given array
 */
function pull_address_fields ( $array_data )
{
    if ( ! $array_data )
        return $array_data;

    // Retrieve only the field names we can handle
    $address_fields = array ( 'address_id'           => '',
                              'company_id'           => '',
                              'country_id'           => '',
                              'address_name'         => '',
                              'address_body'         => '',
                              'line1'                => '',
                              'line2'                => '',
                              'city'                 => '',
                              'province'             => '',
                              'postal_code'          => '',
                              'address_type'         => '',
                              'use_pretty_address'   => '',
                              'offset'               => '',
                              'daylight_savings_id'  => '',
                            );


    // Now pull out the fields we need
    return array_intersect_key_2($address_fields, $array_data);

}
/**********************************************************************/
/**********************************************************************/

 /**
 * $Log: utils-addresses.php,v $
 * Revision 1.1  2005/12/19 05:34:18  jswalter
 *  - initial commit
 *
 *
 *
**/
 ?>