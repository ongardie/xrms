<?php
/**
 * workflow-activities.php -  generates activities that are linked to
 *                            the workflow status when the status is changed.
 *
 * @author Brad Marshall
 * @author Brian Peterson
 *
 * $Id: workflow-activities.php,v 1.10 2005/01/10 21:47:10 vanmer Exp $
 *
 * @todo To extend and internationalize activity template substitution,
 *       we would need to add a table to the database that would hold
 *       the substitution string and the sql to execute to return
 *       a single field to substitute.
 *       Then, this page would retrieve the result set for string/sql pairs, and
 *       run through the result set and do a test/select/substitute for each member
 *       the substitution result set.
 */

$sql = "select * from activity_templates
    where on_what_table='$on_what_table_template'
    and on_what_id=$on_what_id_template
    and activity_template_record_status='a'
    order by sort_order";

$rst = $con->execute($sql);

//generates insert statement to add activities to the current list
$cnt = 0;
if(empty($activity_record_status)) {
    $activity_record_status = 'a';
}
if ($rst) {
    while (!$rst->EOF) {

        //get the field values from the next record in the query
        $activity_template_id = $rst->fields['activity_template_id'];
        $activity_type_id = $rst->fields['activity_type_id'];
        $activity_title = $rst->fields['activity_title'];
        $default_text = $rst->fields['default_text'];
        $activity_description = $rst->fields['activity_description'];
        $duration = $rst->fields['duration'];

        //calculate ends_at, based on duration and current date
        if ( is_numeric("$duration") ) {
            $duration = $duration.' days';
        }
        $ends_at = date('Y-m-d',strtotime($duration));

        /**
         * Do variable substitution on the Activity Title in an Activity Template
         *
         * @todo Move variable substitutions for actvity templates into a user-definable table.
         */
        if (strpos($activity_title, 'company_name')) {
            //get the company name for substitutions
            $company_sql = "select company_name from companies where company_id=$company_id and company_record_status='a'";
            $company_name = $con->GetOne($company_sql);
            if ($company_name) {
                $activity_title = str_replace('company_name',$company_name,$activity_title);
            } else {
                db_error_handler ($con, $company_sql);
            }
        }
        if (strpos($activity_title, 'contact_name')) {
            // get the contact name for variable substitution
            $contact_sql = "
            SELECT " . $con->Concat("first_names","' '","last_name") . " AS contact_name
            FROM contacts
            WHERE company_id = $company_id
            AND contact_id = $contact_id
            AND contact_record_status = 'a'
            ";
            $contact_name = $con->GetOne($contact_sql);
            if ($contact_name) {
                $activity_title = str_replace('contact_name',$contact_name,$activity_title);
            } else {
                db_error_handler ($con, $contact_sql);
            }
        }
        //save to database
        $rec = array();
        $rec['activity_type_id'] = $activity_type_id;
        $rec['activity_description'] = $default_text;
        $rec['ends_at'] = strtotime($ends_at);
        $rec['user_id'] = $user_id;
        $rec['company_id'] = $company_id;
        $rec['contact_id'] = $contact_id;
        $rec['on_what_table'] = $on_what_table;
        $rec['on_what_id'] = $on_what_id;
        $rec['on_what_status'] = $on_what_id_template;
        $rec['activity_title'] = $activity_title;
        $rec['entered_at'] = time();
        $rec['entered_by'] = $user_id;
        $rec['scheduled_at'] = time();
        $rec['activity_status'] = 'o';
        $rec['activity_record_status'] = $activity_record_status;

        $tbl = 'activities';
        $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
        $ins_rst=$con->execute($ins);
        if (!$ins_rst) { db_error_handler($con, $sql); }
//        echo "INSERTED ". $con->Insert_ID();
        
        do_hook_function('workflow_addition', $activity_template_id);

        $rst->movenext();
    }
    $rst->close();
}

/**
 * $Log: workflow-activities.php,v $
 * Revision 1.10  2005/01/10 21:47:10  vanmer
 * - added db_error_handler to the Insert SQL used for creating new activities
 *
 * Revision 1.9  2004/12/24 15:59:03  braverock
 * - clean up todo item about internationalization of activity template substitution
 *
 * Revision 1.8  2004/09/17 20:02:15  neildogg
 * - Remove uninitialized values
 *  - Added hook
 *
 * Revision 1.7  2004/08/19 21:41:50  neildogg
 * - Allows a default description added to
 *  - auto created activities
 *
 * Revision 1.6  2004/07/07 21:51:11  braverock
 * - fix parse error after $tbl change on line 97
 *
 * Revision 1.5  2004/07/07 21:27:37  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.4  2004/06/21 14:26:48  braverock
 * - add variable substitution
 * - add phpdoc
 */
?>