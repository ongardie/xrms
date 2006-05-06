<?php
/**
 * workflow-activities.php -  generates activities that are linked to
 *                            the workflow status when the status is changed.
 *
 * @author Brad Marshall
 * @author Brian Peterson
 *
 * $Id: workflow-activities.php,v 1.16 2006/05/06 09:33:03 vanmer Exp $
 *
 * @todo To extend and internationalize activity template substitution,
 *       we would need to add a table to the database that would hold
 *       the substitution string and the sql to execute to return
 *       a single field to substitute.
 *       Then, this page would retrieve the result set for string/sql pairs, and
 *       run through the result set and do a test/select/substitute for each member
 *       the substitution result set.
 */

 require_once($include_directory.'utils-activities.php');
 require_once($include_directory.'utils-workflow.php');

//this page is now deprecated, and should not be used.  This function call is here to allow backward compability

add_workflow_activities($con, $on_what_table_template, $on_what_id_template, $on_what_table, $on_what_id, $company_id, $contact_id, $template_sort_order);


/**
 * $Log: workflow-activities.php,v $
 * Revision 1.16  2006/05/06 09:33:03  vanmer
 * - removed code from workflow-activities, now in utils-workflow.php
 * - added function call to duplicate old workflow-activities functionality
 *
 * Revision 1.15  2005/09/29 14:51:52  vanmer
 * - moved template-specific handling of activity types to below other processing of activity title and description
 * - added hook for system template activities
 * - added code to create a new workflow for process activity templates, to allow forking of workflow
 *
 * Revision 1.14  2005/07/08 02:36:18  vanmer
 * - changed to use session_user_id if no user_id was found through least busy method
 *
 * Revision 1.13  2005/07/07 20:57:41  vanmer
 * - changed to use newly created least busy user function
 *
 * Revision 1.12  2005/07/06 23:42:01  vanmer
 * - added initial handling of actions on workflow templates
 * - changed to use add_activity API when instantiating workflow activities
 * - added sort order to activity template query, so that only activities at a certain sort order get instantiated
 *
 * Revision 1.11  2005/02/10 14:40:03  maulani
 * - Set last modified info when creating activities
 *
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