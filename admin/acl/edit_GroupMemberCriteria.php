<?php

/**
 * edit_GroupMemberCriteria.php - Manage criteria for a group member entry
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @author Aaron van Meerten
 * $Id: edit_GroupMemberCriteria.php,v 1.1 2005/08/02 00:45:50 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check('', 'Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');

getGlobalVar($criteria_action, 'criteria_action');
getGlobalVar($GroupMember_id, 'GroupMember_id');
getGlobalVar($GroupMemberCriteria_id, 'GroupMemberCriteria_id');
getGlobalVar($criteria_fieldname, 'criteria_fieldname');
getGlobalVar($criteria_value, 'criteria_value');
getGlobalVar($criteria_operator, 'criteria_operator');
getGlobalVar($return_url, 'return_url');

global $symbol_precendence;

$con = get_acl_dbconnection();

$default_return_url="$http_site_root/admin/acl/one_GroupMember.php?form_action=edit&GroupMember_id=$GroupMember_id";
if (!$return_url) { $return_url=$default_return_url; }
switch ($criteria_action) {
    case 'addCriteria':
        if ($GroupMember_id AND $criteria_fieldname AND $criteria_value AND $criteria_operator) {
            $ret=add_group_member_criteria($con, $GroupMember_id, $criteria_fieldname, $criteria_value, $criteria_operator);
            if ($ret) { $return_url=$default_return_url.'&msg='.urlencode(_("Successfully added criteria")); }
        }
    case 'deleteCriteria':
        if ($GroupMemberCriteria_id) {
            $ret=remove_group_member_criteria($con, $GroupMemberCriteria_id);
            if ($ret) { $return_url=$default_return_url.'&msg='.urlencode(_("Successfully removed criteria")); }
        }
    break;
}

Header("Location: $return_url");
exit;

 /**
  * $Log: edit_GroupMemberCriteria.php,v $
  * Revision 1.1  2005/08/02 00:45:50  vanmer
  * - Initial revision of a backend which can add and remove criteria on group member entries
  *
  *
**/
?>   