<?php
/**
 * Common Include file for XRMS Info Plugin
 *
 * $Id: info.inc,v 1.3 2005/02/11 13:52:39 braverock Exp $
 */
 
# How checkboxes values should be shown
$checkbox_set = _("yes");
$checkbox_clear = _("no");

# Titles
$info_heading = _("Information");
$info_list = _("List");
$info_heading = _("Item");
$company_heading = _("Company");
$edit_info_details = _("Edit Details");
$new_info_details = _("Add New Info");

# What we call different element types
$text = _("text");
$textarea = _("textbox");
$radio = _("radio buttons");
$checkbox = _("checkbox");
$select = _("combo");

# Sort by company or server name on the Server Info page
$sort_by_companyname = false;

/**
 * $Log: info.inc,v $
 * Revision 1.3  2005/02/11 13:52:39  braverock
 * - change info_info_heading to info_heading
 *
 * Revision 1.2  2005/02/11 13:49:46  braverock
 * - add phpdoc
 * - remove references to server_info and replace with just info
 *
 */
?>
