<?php
/**
 * Utility functions for manipulating activities
 *
 * These functions create, retrieve, delete and modify companies
 * This file should be included anywhere companies need to be created or modified
 *
 * @author Aaron van Meerten
 *
 * $Id: utils-addresses.php,v 1.3 2005/12/22 22:39:26 jswalter Exp $
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

    $_table_name = 'addresses';

    // What is the primary key for this table
    $_primary_key = get_primarykey_from_table($con, $_table_name);

    // Retrieve address table fields from orginal data set
    $address_info = pull_address_fields ( $address_data );

    // If nothing was sent back, we can't make an address. Default to '1'
    if ( ! $address_info )
    {
        $address_info['address_id'] = 1;
        $address_info['primarykey'] = $_primary_key;

        return $address_info;
    }

    // If a company_is was not passed in, set it to ONE
    if ( ! $address_info['company_id'] )
        $address_info['company_id'] = 1;

    // If 'country' is not defined, use the systems default value
    if ( ( ! $address_info['country_id'] ) && ( ! $address_info['country'] ) )
    {
        global $default_country_id;
            $address_info['country_id'] = $default_country_id;
     }
    // Otherwise retrieve XRMS Country ID
    else
    {
        $_country_data = get_country($con, $address_info['country']);
        $address_info['country_id'] = $_country_data['country_id'];
    }

    // If we have a record ID, we don't need to find the record (as we have it)
    // it just needs to be updated.

    if ( 0 )
    {


    }
    // Need to see if this address exists already
    else
    {
        // Prep array for "search", only on these fields
        $extra_where = array();
        foreach ($address_info as $_field => $_value) {
            switch ($_field) {
                case 'line1':
                case 'line2':
                case 'postal_code':
                    $extra_where[$_field] = $_value;
                break;
            }
        }

        // Determine if this contact already exists
        $found_data = __record_find ( $con, $_table_name, $extra_where, $_magic_quotes );

        // Retrieve timezone and GMT data if not already defined
        if ( (! $found_data['daylight_savings_id'] ) || (! $found_data['gmt_offset'] ) )
        {
            $time_zone_offset = time_zone_offset($con, $found_data['address_id']);
            $address_info['daylight_savings_id'] = $time_zone_offset['daylight_savings_id'];
            $address_info['gmt_offset']          = $time_zone_offset['offset'];
        }

        // What's the primary key for this data set
        $_primay_key = $found_data['primarykey'];

        // If this contact exists already
        if ( $found_data[$_primay_key] )
        {
            // Define address name if one is not already defined
            if ( ( ! $address_info['address_name'] ) && ( ! $found_data['address_name'] ) )
                $address_info['address_name'] = 'Main';

            // We found it, so pull record ID
            $address_info[$_primay_key] = $found_data[$_primay_key];

            $_retVal = __record_update ( $con, $_table_name, $_primay_key, $address_info, $_magic_quotes, $_return_recordset, $_deleteRecord );

            if ( $_retVal[$_primary_key] == 0 )
            {
                $_retVal[$_primary_key] = $found_data[$_primay_key];
                $_retVal['primarykey'] = $_primary_key;
            }
        }
        // This is a new addresses
        else
        {
            // Define address name if one is not already defined
            if ( ! $address_info['address_name'] )
                $address_info['address_name'] = 'Main';

            // make new record
            $_retVal = __record_insert ( $con, $_table_name, $address_info, $_magic_quotes, $_return_recordset );
        }
    }

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
 * Revision 1.3  2005/12/22 22:39:26  jswalter
 *  - moved primary key retrieval to near top of method 'add_update_address()'
 *
 * Revision 1.2  2005/12/20 07:49:21  jswalter
 *  - fleshed out 'add_update_address()'
 * Bug 779
 *
 * Revision 1.1  2005/12/19 05:34:18  jswalter
 *  - initial commit
 *
 *
 *
**/
 ?>