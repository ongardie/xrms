<?php
/**
 * Utility functions for manipulating activities
 *
 * These functions create, retrieve, delete and modify companies
 * This file should be included anywhere companies need to be created or modified
 *
 * @author Aaron van Meerten
 * @author Brian Peterson
 *
 * $Id: utils-addresses.php,v 1.9 2006/04/28 02:42:52 vanmer Exp $
 *
 */


/**********************************************************************/
/**
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
 * - address_name                - Label used to identify this address - defaults to $address_info['city']." - ".$address_info['line1']
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
 * @param boolean         $_magic_quotes     F - inbound data is not "add slashes", T - data is "add slashes"
 *
 * @return $address_id with newly created address id, array of address records, a recordset object or false if failure occured
 */
function add_update_address($con, $address_data, $return_recordset = false, $_magic_quotes =  false  )
{
   //$con->debug=1;

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
    if ( ! $address_info ) {
        $address_info['address_id'] = 1;
        $address_info['primarykey'] = $_primary_key;

        return $address_info;
    } elseif (
        ! $address_info['line1'] &&
        ! $address_info['city']
    ) {
        //useless address, so set to unknown
        $address_info['address_id'] = 1;
        $address_info['primarykey'] = $_primary_key;

    }

    //if this is an unknown address, just return
    if ($address_info['address_id'] == 1) { return $address_info; }

    // If a company_id was not passed in, set it to ONE
    if ( ! $address_info['company_id'] ) {
        $address_info['company_id'] = 1;
    }

    //set up the country
    global $default_country_id;
    if ( ( ! $address_info['country_id'] ) && ( ! $address_info['country'] ) )
    {
        // If 'country' is not defined, use the systems default value
        $address_info['country_id'] = $default_country_id;
    } elseif ( ( ! $address_info['country_id']) && ( strlen(trim($address_info['country']))>0 )  ) {
        // Otherwise look up the country id from the string
        $_country_data = get_country($con, $address_info['country']);
        if ($_country_data['country_id']) {
            $address_info['country_id'] = $_country_data['country_id'];
        } else {
            $address_info['country_id'] = $default_country_id;
        }
    } //end country checks


    if ( $address_info['address_id'] )
    {
      // If we have a record ID, we don't need to find the record (as we have it)
      // it just needs to be updated.
      $_retVal = __record_update ( $con, $_table_name, $_primay_key, $address_info, $_magic_quotes, $_return_recordset, $_deleteRecord );
    } else {
        // Need to see if this address exists already
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

        // Determine if this address already exists
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

        // Define address name if one is not already defined
        if ( $address_info['address_name'] = _("Main") ) {
            //clear the 'Main' address... bad convention
            $address_info['address_name'] = '';
            //NOTE: this is still messy, and needs cleaning up
        }
        if ( ( ! strlen($address_info['address_name']) ) && ( ! strlen($found_data['address_name']) ) ) {
            if (strlen($address_info['city'])) {
                $address_info['address_name'] = $address_info['city']." - ".$address_info['line1'];
            } else {
                $address_info['address_name'] = _("Main");
            }
        }

        // If this address exists already
        if ( $found_data[$_primay_key] )
        {

            // We found it, so pull record ID
            $address_info[$_primay_key] = $found_data[$_primay_key];

            $_retVal = __record_update ( $con, $_table_name, $_primay_key, $address_info, $_magic_quotes, $_return_recordset, $_deleteRecord );

            if ( $_retVal[$_primary_key] == 0 )
            {
                $_retVal[$_primary_key] = $found_data[$_primay_key];
                $_retVal['primarykey'] = $_primary_key;
            }
        } else { // This is a new addresses
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
 * @param boolean         $_magic_quotes     F - inbound data is not "add slashes", T - data is "add slashes"
 *
 * @return $address_id with newly created address id, or false if failure occured
 */
function add_address($con, $address_data, $_magic_quotes=false)
{
    $add=add_update_address($con, $address_data, $return_recordset, $_magic_quotes);
    if ($add){
        return $add['address_id'];
    } else {
        return false;
    }
};

/**********************************************************************/
/**
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

    $sql = "SELECT addresses.*,countries.* FROM addresses JOIN countries ON addresses.country_id=countries.country_id WHERE address_id=$address_id";
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
 * Updates an address in XRMS from an associative array
 * Either an address_id must be explicitly set or an adodbrecordset for
 * the record to be updated must be passed in or the function will fail
 *
 * @param adodbconnection $con          ADOdb connection Object
 * @param array           $address_data with associative array defining address data to update
 * @param boolean         $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 * @param boolean         $_magic_quotes     F - inbound data is not "add slashes", T - data is "add slashes"
 *
 * @return boolean specifying if update succeeded
 */
function update_address($con, $address_data, $return_recordset=false, $_magic_quotes=false)
{
    $result = add_update_address($con, $address_data, $return_recordset, $_magic_quotes);

    if ($result && !$return_recordset) {
        return true;
    } elseif ($result && $return_recordset) {
        return $result;
    } else {
        return false;
    };
};

/**********************************************************************/
/**
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
        $sql = "SELECT * FROM addresses WHERE address_id = $address_id";
        $rst = $con->execute($sql);

        $rec = array();
        $rec['address_record_status'] = 'd';

        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        $_retVal = $con->execute($upd);
    }

    return $_retVal;
};

/**********************************************************************/

/**
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
 * Revision 1.9  2006/04/28 02:42:52  vanmer
 * - added country to address API, corrected address table in select
 *
 * Revision 1.8  2006/04/25 14:35:42  braverock
 * - multiple updates to add_update_address function
 *   - better checks to see if we have enough information to create an address
 *   - better handling of address name
 *   - better update handling on updated address_id
 *   - correct improper country lookups on new address
 *   - eliminate extra database calls where possible
 *
 * Revision 1.7  2006/04/21 22:56:25  braverock
 * - clean up error checking
 *
 * Revision 1.6  2006/04/21 22:03:36  braverock
 * - update add_address and update_address API to more closely match contacts and companies API
 *
 * Revision 1.5  2006/04/21 21:37:38  braverock
 * - implement add_address wrapper fn -> add_update_address
 * - implement update_address wrapper fn -> add_update_address
 * - implement correct handling of magic_quotes
 *
 * Revision 1.4  2006/04/21 20:30:07  braverock
 * - add better default address name
 * - implement delete_address function
 * - remove redundant code
 *
 * Revision 1.3  2005/12/22 22:39:26  jswalter
 *  - moved primary key retrieval to near top of method 'add_update_address()'
 *
 * Revision 1.2  2005/12/20 07:49:21  jswalter
 *  - fleshed out 'add_update_address()'
 * Bug 779
 *
 * Revision 1.1  2005/12/19 05:34:18  jswalter
 *  - initial commit
 **/
 ?>