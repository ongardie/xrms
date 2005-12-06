<?php
/**
 * install/db_table_creation.php - This page creates the tables needed for xrms
 *
 * This file contains the routines that will create all of the xrms tables
 * These routines are non-destructive, so this can be run over a functioning
 * system without incident.
 *
 * These routines are called from the main installation file, which has already
 * checked for proper variable and path setup, and that a database connection exists.
 *
 * @author Beth Macknik
 * $Id: database.php,v 1.59 2005/12/06 22:39:37 vanmer Exp $
 */

/**
 * Create the miscellaneous tables.
 *
 */
function misc_db_tables($con, $table_list) {
    // recent_items
    if (!in_array('recent_items',$table_list)) {
        $sql ="create table recent_items (
               recent_item_id                          int not null primary key auto_increment,
               user_id                                 int not null default 0,
               on_what_table                           varchar(100) not null default '',
               recent_action                           varchar(100) not null default '',
               on_what_id                              int not null default 0,
               recent_item_timestamp                   datetime
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // audit_items
    if (!in_array('audit_items',$table_list)) {
        $sql ="create table audit_items (
               audit_item_id                           int not null primary key auto_increment,
               user_id                                 int not null default 0,
               audit_item_type                         varchar(50) default '',
               on_what_table                           varchar(100) default '',
               on_what_id                              varchar(10) default '',
               audit_item_timestamp                    datetime,
               audit_item_record_status                char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // files
    if (!in_array('files',$table_list)) {
        $sql ="create table files (
               file_id                                 int not null primary key auto_increment,
               file_name VARCHAR( 100 ) NOT NULL,
               file_pretty_name                        varchar(100) not null default '',
               file_description                        text not null default '',
               file_filesystem_name                    varchar(100) not null default '',
               file_size                               int not null default 0,
               file_type                               varchar(100) not null default '',
               on_what_table                           varchar(100) not null default '',
               on_what_id                              int not null default 0,
               entered_at                              datetime,
               entered_by                              int not null default 0,
               last_modified_on DATETIME NOT NULL,
               last_modified_by VARCHAR( 11 ) NOT NULL,               
               file_record_status                      char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // notes
    if (!in_array('notes',$table_list)) {
        $sql ="create table notes (
               note_id                                 int not null primary key auto_increment,
               note_description                        text not null default '',
               on_what_table                           varchar(100),
               on_what_id                              int not null default 0,
               entered_at                              datetime,
               entered_by                              int not null default 0,
               note_record_status                      char(1) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // categories
    if (!in_array('categories',$table_list)) {
        $sql ="create table categories (
               category_id                             int not null primary key auto_increment,
               category_short_name                     varchar(10) not null default '',
               category_pretty_name                    varchar(100) not null default '',
               category_pretty_plural                  varchar(100) not null default '',
               category_display_html                   varchar(100) not null default '',
               category_record_status                  char(1) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // category_scopes
    if (!in_array('category_scopes',$table_list)) {
        $sql ="create table category_scopes (
               category_scope_id                       int not null primary key auto_increment,
               category_scope_short_name               varchar(10) not null default '',
               category_scope_pretty_name              varchar(100) not null default '',
               category_scope_pretty_plural            varchar(100) not null default '',
               category_scope_display_html             varchar(100) not null default '',
               on_what_table                           varchar(100) not null default '',
               category_scope_record_status            char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // category_category_scope_map
    if (!in_array('category_category_scope_map',$table_list)) {
        $sql ="create table category_category_scope_map (
               category_id                             int not null default 0,
               category_scope_id                       int not null default 0
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // entity_category_map
    if (!in_array('entity_category_map',$table_list)) {
        $sql ="create table entity_category_map (
               category_id                             int not null default 0,
               on_what_table                           varchar(100) not null default '',
               on_what_id                              int not null default 0
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // countries
    if (!in_array('countries',$table_list)) {
        $sql ="create table countries (
               country_id                              int not null primary key auto_increment,
               address_format_string_id                int not null default 1,
               country_name                            varchar(100) not null default '',
               un_code                                 varchar(50) not null default '',
               iso_code1                               varchar(50) not null default '',
               iso_code2                               varchar(50) not null default '',
               iso_code3                               varchar(50) not null default '',
               telephone_code                          varchar(50) not null default '',
               country_record_status                   char(1) not null default 'a',
               phone_format                            varchar(25) not null default ''
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // salutations
    if (!in_array('salutations',$table_list)) {
        $sql ="create table salutations (
               salutation_id                           int not null primary key auto_increment,
               salutation                              varchar(20) not null default '',
               salutation_sort_value                   varchar(20) not null default ''
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // address_types
    if (!in_array('address_types',$table_list)) {
        $sql ="create table address_types (
               address_type_id                           int not null primary key auto_increment,
               address_type                              varchar(20) not null default '',
               address_type_sort_value                   varchar(20) not null default ''
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // address_format_strings
    if (!in_array('address_format_strings',$table_list)) {
        $sql ="create table address_format_strings (
               address_format_string_id                int not null primary key auto_increment,
               address_format_string                   varchar(255),
               address_format_string_record_status     char(1) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }
} // end misc_db_tables fn



/**
 * Create the user tables.
 *
 */
function user_db_tables($con, $table_list) {
    // users
    if (!in_array('users',$table_list)) {
        $sql ="create table users (
               user_id             int not null primary key auto_increment,
               user_contact_id     int not null default 0,
               username            varchar(100) not null default '' unique,
               password            varchar(100) not null default '',
               last_name           varchar(100) not null default '',
               first_names         varchar(100) not null default '',
               email               varchar(100) not null default '',
               language            varchar(50) not null default 'english',
               gmt_offset          int not null default 0,
               last_hit            datetime,
               user_record_status      char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('user_preference_types',$table_list)) {
        $sql="CREATE TABLE `user_preference_types` (
        `user_preference_type_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
        `user_preference_name` VARCHAR( 64 ) NOT NULL ,
        `user_preference_pretty_name` VARCHAR( 128 ) NOT NULL ,
        `user_preference_description` VARCHAR( 255 ) NOT NULL ,
        `allow_multiple_flag` TINYINT( 1 ) DEFAULT '0' NOT NULL ,
        `allow_user_edit_flag` TINYINT( 1 ) DEFAULT '0' NOT NULL ,
        `read_only` TINYINT( 1 ) DEFAULT '0' NOT NULL ,
        `user_preference_type_status` CHAR( 1 ) DEFAULT 'a' NOT NULL ,
        `preference_type_created_on` DATETIME NOT NULL ,
        `user_preference_type_modified_on` DATETIME NOT NULL ,
        `form_element_type` varchar(32) NOT NULL default 'text',
         PRIMARY KEY ( `user_preference_type_id` )
        )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('user_preferences',$table_list)) {
        $sql="CREATE TABLE `user_preferences` (
        `user_preference_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
        `user_id` INT NOT NULL,
        `user_preference_type_id` INT UNSIGNED NOT NULL ,
        `user_preference_name` VARCHAR( 255 ) ,
        `user_preference_value` LONGTEXT NOT NULL ,
        `user_preference_status` CHAR( 1 ) DEFAULT 'a' NOT NULL ,
        `user_preference_modified_on` DATETIME NOT NULL ,
        `user_preference_created_by` INT NOT NULL ,
        `user_preference_modified_by` INT NOT NULL ,
        PRIMARY KEY ( `user_preference_id` ) ,
        INDEX ( `user_preference_type_id` )
        );";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }
    if (!in_array('user_preference_type_options',$table_list)) {
        $sql = "CREATE TABLE `user_preference_type_options` (
        `up_option_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_preference_type_id` INT UNSIGNED NOT NULL,
        `option_value` VARCHAR(255) NOT NULL,
        `sort_order` INT UNSIGNED DEFAULT 1 NOT NULL,
        `option_record_status` CHAR(1) DEFAULT 'a' NOT NULL,
        `option_display` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`up_option_id`)
        );";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }
} // end user_db_tables fn



/**
 * Create the company tables.
 *
 */
function company_db_tables($con, $table_list) {
    // company_sources
    // where did each company come from?  how did they hear about us?  I like options like "trade show" and
    // "advertisement", but you could just as easily use more specific items -- e.g., "June Telemarketing" -- to track how
    // many leads are coming from each source.  These company sources are different from campaigns, which are only
    // associated with opportunities.  Of course, if you don't have any need to track this information, you can just rename
    // it and use the picklist to store another type of information entirely.
    if (!in_array('company_sources',$table_list)) {
        $sql ="create table company_sources (
               company_source_id               int not null primary key auto_increment,
               company_source_short_name   varchar(10) not null default '',
               company_source_pretty_name  varchar(100) not null default '',
               company_source_pretty_plural    varchar(100) not null default '',
               company_source_display_html varchar(100) not null default '',
               company_source_record_status    char(1) default 'a',
               company_source_score_adjustment int not null
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // industries
    // a user-readable list of industries -- by default I suggest things like "mining", "consulting", etc. But feel free
    // to modify this for your particular needs... if you deal with restaurants exclusively, you might want to use values
    // like "Mexican", "Thai", or "Caribbean".
    if (!in_array('industries',$table_list)) {
        $sql ="create table industries (
               industry_id                 int not null primary key auto_increment,
               industry_short_name     varchar(10) not null default '',
               industry_pretty_name        varchar(100) not null default '',
               industry_pretty_plural      varchar(100) not null default '',
               industry_display_html       varchar(100) not null default '',
               industry_record_status      char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // ratings
    // I think it's helpful to have some record of how good/reliable each company in your system is... just a quick "good",
    // "fair", or "poor" is enough for me, but you might add "excellent" or "horrible" if you need more options.
    if (!in_array('ratings',$table_list)) {
        $sql ="create table ratings (
               rating_id           int not null primary key auto_increment,
               rating_short_name       varchar(10) not null default '',
               rating_pretty_name      varchar(100) not null default '',
               rating_pretty_plural        varchar (100) not null default '',
               rating_display_html     varchar(100) not null default '',
               rating_record_status        char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // account_statuses
    // Some of you need to make sure that your customers have valid contracts, have paid their bills, aren't over their
    // credit limits, etc.
    if (!in_array('account_statuses',$table_list)) {
        $sql ="create table account_statuses (
               account_status_id               int not null primary key auto_increment,
               account_status_short_name       varchar(10) not null default '',
               account_status_pretty_name      varchar(100) not null default '',
               account_status_pretty_plural    varchar(100) not null default '',
               account_status_display_html     varchar(100) not null default '',
               account_status_record_status    char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // company_types
    // Companies can belong to zero or more of these types, but this table is here to represent high-level relationships
    // with your organization: partner, vendor, customer, competitor, etc.  If you're just using XRMS to track customers,
    // you won't have much need for these and they can safely be ignored.
    if (!in_array('company_types',$table_list)) {
        $sql ="create table company_types (
               company_type_id                 int not null primary key auto_increment,
               company_type_short_name         varchar(10) not null default '',
               company_type_pretty_name        varchar(100) not null default '',
               company_type_pretty_plural      varchar(100) not null default '',
               company_type_display_html       varchar(100) not null default '',
               company_type_record_status      char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // crm_statuses
    // Did you just find out about this company, or is this an old, well-developed account?  I like traditional options here
    // such as Lead, Prospect, Developed, etc.  Eventually we'll probably add a crm_status_transitions table to keep
    // tabs on how well companies are moving along through the CRM process.
    if (!in_array('crm_statuses',$table_list)) {
        $sql ="create table crm_statuses (
               crm_status_id               int not null primary key auto_increment,
	       sort_order                  tinyint not null default 1,
               crm_status_short_name       varchar(10) not null default '',
               crm_status_pretty_name      varchar(100) not null default '',
               crm_status_pretty_plural    varchar(100) not null default '',
               crm_status_display_html     varchar(100) not null default '',
               crm_status_record_status    char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    //workflow history, tracks changes in statuses of workflow entities
    if (!in_array('workflow_history',$table_list)) {
        $sql="CREATE TABLE `workflow_history` (
                `workflow_history_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                `on_what_table` VARCHAR( 32 ) NOT NULL ,
                `on_what_id` INT UNSIGNED NOT NULL ,
                `old_status` INT UNSIGNED NOT NULL ,
                `new_status` INT UNSIGNED NOT NULL ,
                `status_change_timestamp` DATETIME NOT NULL ,
                `status_change_by` INT UNSIGNED NOT NULL ,
                PRIMARY KEY ( `workflow_history_id` )
                );";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // companies
    // this is the Big Daddy table of companies/organizations.  Lots of references to the above-mentioned tables, a few
    // things that should probably be stored in other software (credit_limit, terms), and three "extref" columns to store
    // keys to link these companies with their representations in other software for reporting/integration purposes.
    if (!in_array('companies',$table_list)) {
        $sql ="create table companies (
               company_id                       int not null primary key auto_increment,
               user_id                          int not null default 0,
               company_source_id                int not null default 0,
               industry_id                      int not null default 0,
               crm_status_id                    int not null default 0,
               rating_id                        int not null default 0,
               account_status_id                int not null default 0,
               company_name                     varchar(100) not null default '',
               company_code                     varchar(10) not null default '',
               legal_name                       varchar(100) not null default '',
               tax_id                           varchar(100) not null default '',
               profile                          text not null default '',
               phone                            varchar(50) not null default '',
               phone2                           varchar(50) not null default '',
               fax                              varchar(50) not null default '',
               url                              varchar(50) not null default '',
               employees                        varchar(50) not null default '',
               revenue                          varchar(50) not null default '',
               credit_limit                     int not null default 0,
               terms                            int not null default 0,
               entered_at                       datetime,
               entered_by                       int not null default 0,
               last_modified_at                 datetime,
               last_modified_by                 int not null default 0,
               default_primary_address          int not null default 0,
               default_billing_address          int not null default 0,
               default_shipping_address         int not null default 0,
               default_payment_address          int not null default 0,
               custom1                          varchar(100) not null default '',
               custom2                          varchar(100) not null default '',
               custom3                          varchar(100) not null default '',
               custom4                          varchar(100) not null default '',
               extref1                          varchar(50) not null default '',
               extref2                          varchar(50) not null default '',
               extref3                          varchar(50) not null default '',
               company_record_status            char(1) default 'a',
               INDEX company_record_status (company_record_status)
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // addresses
    // each company can have one or more address (one gets automatically added with the company) -- and you can select via
    // radio button which one should be the default for billing, shipping, and payments.  I think this might be better as a
    // "facilities" table, with contacts belonging to one facility, but for now this should be good enough.
    if (!in_array('addresses',$table_list)) {
        $sql ="create table addresses (
               address_id          int not null primary key auto_increment,
               company_id          int not null default 0,
               country_id          int not null default 1,
               address_name            varchar(100) not null default '',
               address_body            varchar(255) not null default '',
               line1               varchar(255) not null default '',
               line2               varchar(255) not null default '',
               city                varchar(255) not null default '',
               province            varchar(255) not null default '',
               postal_code         varchar(255) not null default '',
               address_type        varchar(20) not null default 'unknown',
               use_pretty_address      char(1) not null default 'f',
               offset               float,
               daylight_savings_id     int unsigned,
               address_record_status       char(1) not null default 'a',
               INDEX company_id (company_id),
               INDEX city (city),
               INDEX province (province),
               INDEX address_record_status (address_record_status)
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // company_division
    // each company can have zero or more divisions.  This is probably not usefult for small companies, so its use is optional.
    if (!in_array('company_division',$table_list)) {
        $sql ="create table company_division (
               division_id                      int not null primary key auto_increment,
               company_id                       int not null,
               address_id                       int,
               user_id                          int not null default 0,
               company_source_id                int not null default 0,
               industry_id                      int not null default 0,
               division_name                    varchar(100) not null default '',
               description                      text not null default '',
               entered_at                       datetime,
               entered_by                       int not null default 0,
               last_modified_at                 datetime,
               last_modified_by                 int not null default 0,
               custom1                          varchar(100) not null default '',
               custom2                          varchar(100) not null default '',
               custom3                          varchar(100) not null default '',
               custom4                          varchar(100) not null default '',
               division_record_status           char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // contacts
    // I could have made separate tables for titles ("President", "Marketing Director", etc.) and summaries
    // ("Decision Maker", "Influencer", etc.) but constraining these often seems to just get in the way.  If you'd
    // like to use specific values here, just come to some kind of agreement as to what they should be and have
    // your employees use them consistently.
    if (!in_array('contacts',$table_list)) {
        $sql ="create table contacts (
               contact_id                      int not null primary key auto_increment,
               company_id                      int not null default 0,
               division_id                     int not null default 0,
               address_id                      int not null default 1,
               home_address_id                 int not null default 1,
               salutation                      varchar(20) not null default '',
               last_name                       varchar(100) not null default '',
               first_names                     varchar(100) not null default '',
               gender                          char(1) not null default 'u',
               date_of_birth                   varchar(100) not null default '',
               summary                         varchar(100) not null default '',
               title                           varchar(100) not null default '',
               description                     varchar(100) not null default '',
               email                           varchar(100) not null default '',
               email_status                    char(1) default 'a',
               work_phone                      varchar(50) not null default '',
               work_phone_ext                  int not null default '',
               cell_phone                      varchar(50) not null default '',
               home_phone                      varchar(50) not null default '',
               fax                             varchar(50) not null default '',
               aol_name                        varchar(50) not null default '',
               yahoo_name                      varchar(50) not null default '',
               msn_name                        varchar(50) not null default '',
               interests                       varchar(50) not null default '',
               profile                         text not null default '',
               custom1                         varchar(50) not null default '',
               custom2                         varchar(50) not null default '',
               custom3                         varchar(50) not null default '',
               custom4                         varchar(50) not null default '',
               entered_at                      datetime,
               entered_by                      int not null default 0,
               last_modified_at                datetime,
               last_modified_by                int not null default 0,
               contact_record_status           char(1) not null default 'a',
               INDEX company_id (company_id),
               INDEX contact_record_status (contact_record_status)
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

   if (!in_array('contact_former_companies',$table_list)) {
        $sql ="CREATE TABLE contact_former_companies (
        cfc_id INT unsigned NOT NULL auto_increment,
        contact_id int(11) NOT NULL default '0',
        companychange_at datetime NOT NULL default '0000-00-00 00:00:00',
        former_company_id int(11) NOT NULL,
        PRIMARY KEY cfc_id (cfc_id),
        KEY (contact_id),
        KEY (former_company_id)
        )";
        $rst=$con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
   }  
    
    // email_templates
    // for the bulk e-mail stuff, where you can store things like "Dear ##CONTACT_FIRST_NAMES## - " and the system will
    // replace the ##CONTACT_FIRST_NAMES## token with the contact's actual first names
    if (!in_array('email_templates',$table_list)) {
        $sql ="create table email_templates (
               email_template_id                   int not null primary key auto_increment,
               email_template_type_id              int not null default 0,
               email_template_title                varchar(100) not null default '',
               email_template_body                 text not null default '',
               email_template_record_status        char(1) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('email_template_type',$table_list)) {
        $sql ="create table email_template_type (
              email_template_type_id int not null primary key auto_increment,
              email_template_type_name varchar(64) not null default '',
              modified_by int not null default '0',
              modified_on datetime,
              created_by int not null default '0',
              created_on datetime
              )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }


    // activity_templates
    // for relating several activities to an opportunity status. links to the opportunity status
    // table on opportunity_status_id, and stores the important data about the activities that are
    // triggered when an opportunity moves to that status.
    if (!in_array('activity_templates',$table_list)) {
        $sql ="create table activity_templates (
                activity_template_id    int not null primary key auto_increment,
                role_id                 int not null default 0,
                activity_type_id        int not null default 0,
                on_what_table           varchar(100) not null default '',
                on_what_id              int not null default 0,
                activity_title          varchar(100) not null default '',
                activity_description    text not null default '',
                default_text            text,
                duration                varchar(20) default 1 not null,
                sort_order              tinyint not null default 1,
                activity_template_record_status         char not null default 'a'
                )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // company_former_names
    // Keep track of company name changes
    if (!in_array('company_former_names',$table_list)) {
        $sql ="create table company_former_names (
               company_id       int NOT NULL default '0',
               namechange_at    datetime NOT NULL default '0000-00-00 00:00:00',
               former_name      varchar(100) NOT NULL default '',
               description      varchar(100) default NULL,
               KEY company_id (company_id)
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // company_relationship
    // Track relationships between companies
    if (!in_array('company_relationship',$table_list)) {
        $sql ="create table company_relationship (
               company_from_id      int NOT NULL default '0',
               relationship_type    varchar(100) NOT NULL default '',
               company_to_id        int NOT NULL default '0',
               established_at       datetime NOT NULL default '0000-00-00 00:00:00',
               KEY company_from_id (company_from_id,company_to_id)
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('relationship_types',$table_list)) {
        $sql ="CREATE TABLE relationship_types (
                relationship_type_id int(10) unsigned NOT NULL auto_increment,
                relationship_name varchar(48) NOT NULL default '',
                from_what_table varchar(24) NOT NULL default '',
                to_what_table varchar(24) NOT NULL default '',
                from_what_text varchar(32) NOT NULL default '',
                to_what_text varchar(32) NOT NULL default '',
                relationship_status char(1) NOT NULL default 'a',
                pre_formatting varchar(25) default NULL,
                post_formatting varchar(25) default NULL,
                PRIMARY KEY  (relationship_type_id)
                )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('saved_actions',$table_list)) {
        $sql = "CREATE TABLE saved_actions (
                 saved_id int(10) unsigned NOT NULL auto_increment,
                 saved_title varchar(100) NOT NULL default '',
                 user_id int(10) unsigned NOT NULL default '0',
                 on_what_table varchar(100) NOT NULL default '',
                 saved_action varchar(100) NOT NULL default '',
                 group_item int(1) NOT NULL default '0',
                 saved_data text NOT NULL,
                 saved_status char(1) NOT NULL default 'a',
                 PRIMARY KEY  (saved_id),
                 KEY user_id (user_id),
                 KEY group_item (group_item)
                 )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('relationships',$table_list)) {
        // create the relationships table if we need it
        $sql ="CREATE TABLE relationships (
                relationship_id int(10) unsigned NOT NULL auto_increment,
                from_what_id int(10) unsigned NOT NULL default '0',
                to_what_id int(10) unsigned NOT NULL default '0',
                relationship_type_id int(10) unsigned NOT NULL default '0',
                established_at datetime default NULL,
                ended_on datetime default NULL,
                relationship_status char(1) NOT NULL default 'a',
                PRIMARY KEY  (relationship_id),
                KEY from_what_id (from_what_id),
                KEY to_what_id (to_what_id)
                )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

} // end company_db_tables fn

/**
 * Create the opportunity tables.
 *
 */
function opportunity_db_tables($con, $table_list) {
    // opportunities
    if (!in_array('opportunities',$table_list)) {
        $sql ="create table opportunities (
               opportunity_id               int not null primary key auto_increment,
               opportunity_type_id int not null default '1',
               opportunity_status_id        int not null default 0,
               campaign_id                  int,
               company_id                   int not null default 0,
               division_id                      int,
               contact_id                   int not null default 0,
               user_id                      int not null default 0,
               opportunity_title            varchar(100) not null default '',
               opportunity_description      text not null default '',
               next_step                    varchar(100) not null default '',
               size                         decimal(10,2) not null default 0,
               probability                  int not null default 0,
               close_at                     datetime,
               entered_at                   datetime,
               entered_by                   int not null default 0,
               last_modified_at             datetime,
               last_modified_by             int not null default 0,
               owned_at                     datetime,
               owned_by                     int not null default 0,
               closed_at                    datetime,
               closed_by                    int not null default 0,
               opportunity_record_status    char(1) default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // opportunity_statuses
    if (!in_array('opportunity_statuses',$table_list)) {
        $sql ="create table opportunity_statuses (
               opportunity_status_id            int not null primary key auto_increment,
               sort_order                       tinyint default '1' not null,
               status_open_indicator            char( 1 ) default 'o' not null,
               opportunity_status_short_name    varchar(10) not null default '',
               opportunity_status_pretty_name   varchar(100) not null default '',
               opportunity_status_pretty_plural varchar(100) not null default '',
               opportunity_status_display_html  varchar(100) not null default '',
               opportunity_status_record_status char(1) not null default 'a',
               opportunity_status_long_desc     varchar(255) not null default ''
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

   // opportunity_types
   if (!in_array('opportunity_types',$table_list)) {
        $sql="create table opportunity_types (
              opportunity_type_id int(11) not null auto_increment,
              opportunity_type_short_name varchar(10) not null default '',
              opportunity_type_pretty_name varchar(100) not null default '',
              opportunity_type_pretty_plural varchar(100) not null default '',
              opportunity_type_display_html varchar(100) not null default '',
              opportunity_type_record_status char(1) not null default 'a',
              primary key  (opportunity_type_id)
              )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }
    if (confirm_no_records($con, 'opportunity_types')) {
        $sql = "INSERT INTO `opportunity_types`
                ( `opportunity_type_id` , `opportunity_type_short_name` , `opportunity_type_pretty_name` , `opportunity_type_pretty_plural` , `opportunity_type_display_html` , `opportunity_type_record_status` )
                VALUES
                ('', 'sale', 'Sales Opportunity', 'Sales Opportunity', 'Sales Opportunity', 'a');";
       //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
        if ($rst) {
            $msg .= _("Successfully added default opportunity type record.").'<BR><BR>';
            $type_id = $con->insert_id();
            $sql = "ALTER TABLE `opportunity_statuses` ADD `opportunity_type_id` INT DEFAULT '$type_id' NOT NULL AFTER `opportunity_status_id`";
            $rst = $con->execute($sql);
            if (!$rst) {
                db_error_handler ($con, $sql);
            }
            if ($rst) {
                $msg .= _("Successfully added opportunity type to opportunity status table.").'<BR><BR>';
            }
        }
    }

    if (!in_array('time_daylight_savings',$table_list)) {
    // create the time_daylight_savings table if we need it
        $sql ="CREATE TABLE time_daylight_savings (
               daylight_savings_id              int(11) NOT NULL auto_increment,
               start_position                   varchar(5) NOT NULL default '',
               start_day                        varchar(10) NOT NULL default '',
               start_month                      int(2) NOT NULL default '0',
               end_position                     varchar(5) NOT NULL default '',
               end_day                          varchar(10) NOT NULL default '',
               end_month                        int(2) NOT NULL default '0',
               hour_shift                       float NOT NULL default '0',
               last_update                      date NOT NULL default '0000-00-00',
               current_hour_shift               float NOT NULL default '0',
               PRIMARY KEY (daylight_savings_id)
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('time_zones',$table_list)) {
    // create the time_zones table if we need it
        $sql ="CREATE TABLE time_zones (
               time_zone_id int(11) NOT NULL auto_increment,
               country_id int(11) NOT NULL default '0',
               province varchar(255) default NULL,
               city varchar(255) default NULL,
               postal_code varchar(24) default NULL,
               daylight_savings_id int(11) NOT NULL default '0',
               offset float NOT NULL default '0',
               confirmed char(1) NOT NULL default '',
               PRIMARY KEY  (time_zone_id),
               KEY country_id (country_id),
               KEY province (province)
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
   }
} // end opportunity_db_tables fn


/**
 * Create the case tables.
 *
 */
function case_db_tables($con, $table_list) {
    // cases
    if (!in_array('cases',$table_list)) {
        $sql ="create table cases (
               case_id                 int not null primary key auto_increment,
               case_type_id            int not null default 0,
               case_status_id          int not null default 0,
               case_priority_id        int not null default 0,
               company_id              int not null default 0,
               division_id                 int,
               contact_id              int not null default 0,
               user_id                 int not null default 0,
               priority                int not null default 0,
               case_title              varchar(100) not null default '',
               case_description        text not null default '',
               due_at                  datetime,
               entered_at              datetime,
               entered_by              int not null default 0,
               last_modified_at        datetime,
               last_modified_by        int not null default 0,
               owned_at                datetime,
               owned_by                int not null default 0,
               closed_at               datetime,
               closed_by               int not null default 0,
               case_record_status      char(1) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // case_types
    if (!in_array('case_types',$table_list)) {
        $sql ="create table case_types (
               case_type_id            int not null primary key auto_increment,
               case_type_short_name        varchar(10) not null default '',
               case_type_pretty_name       varchar(100) not null default '',
               case_type_pretty_plural     varchar(100) not null default '',
               case_type_display_html      varchar(100) not null default '',
               case_type_record_status     char(1) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // case_statuses
    if (!in_array('case_statuses',$table_list)) {
        $sql ="create table case_statuses (
               case_status_id              int not null primary key auto_increment,
               sort_order                  tinyint default '1' not null,
               status_open_indicator       char( 1 ) default 'o' not null,
               case_status_short_name      varchar(10) not null default '',
               case_status_pretty_name     varchar(100) not null default '',
               case_status_pretty_plural   varchar(100) not null default '',
               case_status_display_html    varchar(100) not null default '',
               case_status_long_desc       varchar(200) not null default '',
               case_type_id                int not null default '1',
               case_status_record_status   char(1) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // case_priorities
    if (!in_array('case_priorities',$table_list)) {
        $sql ="create table case_priorities (
               case_priority_id                 int not null primary key auto_increment,
               case_priority_short_name         varchar(10) not null default '',
               case_priority_pretty_name        varchar(100) not null default '',
               case_priority_pretty_plural      varchar(100) not null default '',
               case_priority_display_html       varchar(100) not null default '',
               case_priority_score_adjustment   int not null,
               case_priority_record_status      char(1) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

} // end case_db_tables fn


/**
 * Create the campaign tables.
 *
 */
function campaign_db_tables($con, $table_list) {
    // campaign_types
    if (!in_array('campaign_types',$table_list)) {
        $sql ="create table campaign_types (
               campaign_type_id                                            int not null primary key auto_increment,
               campaign_type_short_name                                    varchar(10) not null default '',
               campaign_type_pretty_name                                   varchar(100) not null default '',
               campaign_type_pretty_plural                                 varchar(100) not null default '',
               campaign_type_display_html                                  varchar(100) not null default '',
               campaign_type_record_status                                 char(3) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // campaign_statuses
    if (!in_array('campaign_statuses',$table_list)) {
        $sql ="create table campaign_statuses (
               campaign_status_id                                          int not null primary key auto_increment,
               campaign_status_short_name                                  varchar(10) not null default '',
               campaign_status_pretty_name                                 varchar(100) not null default '',
               campaign_status_pretty_plural                               varchar(100) not null default '',
               campaign_status_display_html                                varchar(100) not null default '',
               campaign_status_record_status                               char(3) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // campaigns
    if (!in_array('campaigns',$table_list)) {
        $sql ="create table campaigns (
               campaign_id                                                 int not null primary key auto_increment,
               campaign_type_id                                            int not null default 0,
               campaign_status_id                                          int not null default 0,
               user_id                                                     int not null default 0,
               campaign_title                                              varchar(100) not null default '',
               campaign_description                                        text not null default '',
               starts_at                                                   datetime,
               ends_at                                                     datetime,
               cost                                                        decimal(8,2) not null default 0.01,
               entered_at                                                  datetime,
               entered_by                                                  int not null default 0,
               last_modified_at                                            datetime,
               last_modified_by                                            int not null default 0,
               campaign_record_status                                      char(1) not null default 'a'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

} // end campaign_db_tables fn


/**
 * Create the activity tables.
 *
 */
function activity_db_tables($con, $table_list) {
    // activity_types
    // I've used default activity types like "call to," "call from," "e-mail to," "e-mail from," etc.  Using these,
    // you'd probably end up writing things like "Introduction," "Sent Marketing Materials," or "Received Bank/Trade
    // References" in the subject line for each activity.  If your organization has a more defined process for customer
    // relationships, you might change these to reflect stages of that process.  For example, you might insert activity types
    // like "Introduction," "Sent Marketing Materials," "Received Bank/Trade References," etc., in which case you could make
    // the subject lines even more descriptive.
    if (!in_array('activity_types',$table_list)) {
        $sql ="create table activity_types (
               activity_type_id                   int not null primary key auto_increment,
               activity_type_short_name           varchar(10) not null default '',
               activity_type_pretty_name          varchar(100) not null default '',
               activity_type_pretty_plural        varchar(100) not null default '',
               activity_type_display_html         varchar(100) not null default '',
               activity_type_score_adjustment     int not null default 0,
               activity_type_record_status        char(1) not null default 'a',
               sort_order                         tinyint not null default '1',
               user_editable_flag         tinyint not null default '1'
               )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    // activities
    // Activities are assumed to belong to exactly one company, contact, opportunity, or case, because I think (hope) this
    // may be good enough.  The ideal data model should be more complicated, and an activity should probably be called
    // something like "work effort,"  but I can't find a data model that seems right.
    if (!in_array('activities',$table_list)) {
        $sql ="create table activities (
               activity_id                     int not null primary key auto_increment,
               activity_type_id                int not null default 0,
               user_id                         int not null default 0,
               company_id                      int not null default 0,
               contact_id                      int not null default 0,
               address_id                    int unsigned,
               on_what_table                   varchar(100) not null default '',
               on_what_id                      int not null default 0,
               on_what_status                  int not null default 0,
               activity_template_id        int not null default 0,
               activity_title                  varchar(100) not null default '',
               activity_description            text not null default '',
               entered_at                      datetime,
               entered_by                      int not null default 0,
               last_modified_at                datetime,
               last_modified_by                int not null default 0,
               thread_id                       int not null default 0,
               followup_from_id                int not null default 0,
               scheduled_at                    datetime,
               ends_at                         datetime,
               completed_at                    datetime,
               completed_by                    int,
               activity_status                 char(1) default 'o',
               activity_record_status          char(1) default 'a',
               activity_recurrence_id          int default 0,
                activity_resolution_type_id INT ( 11 ),
                activity_priority_id INT( 11 ),
                resolution_description TEXT
                )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('activity_participants',$table_list)) {
        $sql ="CREATE TABLE activity_participants (
                    activity_participant_id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                    activity_id INT UNSIGNED NOT NULL ,
                    contact_id INT UNSIGNED NOT NULL ,
                    activity_participant_position_id INT UNSIGNED NOT NULL ,
                    ap_record_status CHAR(1) DEFAULT 'a' NOT NULL,
                    PRIMARY KEY ( activity_participant_id ) ,
                    INDEX ( activity_id ),
                    INDEX ( contact_id ),
                    INDEX ( activity_participant_position_id )
                    ); ";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('activity_participant_positions',$table_list)) {
        $sql ="CREATE TABLE activity_participant_positions (
                    activity_participant_position_id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                    activity_type_id INT UNSIGNED NULL ,
                    participant_position_name VARCHAR(50) NOT NULL ,
                    global_flag TINYINT UNSIGNED DEFAULT '0' NOT NULL,
                    PRIMARY KEY ( activity_participant_position_id ) ,
                    INDEX ( activity_type_id )
                    ); ";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('activities_recurrence',$table_list)) {
        $sql="CREATE TABLE activities_recurrence (
                activity_recurrence_id int(11) NOT NULL auto_increment,
                activity_id int(11) NOT NULL default '0',
                start_datetime datetime default NULL,
                end_datetime datetime default NULL,
                end_count int(11) default '0',
                frequency int(11) NOT NULL default '0',
                period varchar(100) NOT NULL default '',
                day_offset int(11) default '0',
                month_offset int(11) default '0',
                week_offset int(11) default '0',
                week_days varchar(100) default '',
                PRIMARY KEY  (activity_recurrence_id));";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

    if (!in_array('activity_resolution_types',$table_list)) {
            $sql = "CREATE TABLE `activity_resolution_types` (
            `activity_resolution_type_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
            `resolution_short_name` VARCHAR( 10 ) NOT NULL ,
            `resolution_pretty_name` VARCHAR( 100 ) NOT NULL ,
            `resolution_type_record_status` CHAR( 1 ) DEFAULT 'a' NOT NULL ,
            `sort_order` TINYINT( 4 ) DEFAULT '0' NOT NULL ,
            PRIMARY KEY ( `activity_resolution_type_id` )
            )";
        //execute
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }



} // end activity_db_tables fn



/**
 * Create the tables.
 *
 */
function create_db_tables($con) {
    $table_list = list_db_tables($con);
    misc_db_tables($con, $table_list);
    user_db_tables($con, $table_list);
    company_db_tables($con, $table_list);
    opportunity_db_tables($con, $table_list);
    case_db_tables($con, $table_list);
    campaign_db_tables($con, $table_list);
    activity_db_tables($con, $table_list);
} // end create_db_tables fn


/**
 * $Log: database.php,v $
 * Revision 1.59  2005/12/06 22:39:37  vanmer
 * - removed system parameters tables from database structure
 *
 * Revision 1.58  2005/11/30 00:43:41  vanmer
 * - added read-only flag for preferences
 *
 * Revision 1.57  2005/10/06 04:30:06  vanmer
 * - updated log entries to reflect addition of code by Diego Ongaro at ETSZONE
 *
 * Revision 1.56  2005/10/04 23:21:44  vanmer
 * Patch to allow sort_order on the company CRM status field, thanks to Diego Ongaro at ETSZONE
 *
 * Revision 1.55  2005/10/03 21:18:46  vanmer
 * - changed timestamp fields into datetime fields to reflect standard SQL fields
 *
 * Revision 1.54  2005/09/21 20:56:48  vanmer
 * - added address_id to activity table, to allow tracking of location of an activity
 *
 * Revision 1.53  2005/08/10 22:44:55  vanmer
 * - added opportunity type id as default field in opportunity table
 *
 * Revision 1.52  2005/08/04 22:55:51  vanmer
 * - added missing user preference type options table, needed for install to complete successfully
 *
 * Revision 1.51  2005/08/04 18:58:08  vanmer
 * - added table to track former contact's companies
 *
 * Revision 1.50  2005/07/06 21:49:00  vanmer
 * - added activity_template_id to track which template an activity was spawned from
 *
 * Revision 1.49  2005/07/06 21:26:35  braverock
 * - add opportunity types
 *
 * Revision 1.48  2005/07/06 20:04:51  vanmer
 * - changed to reflect standard fieldnames
 *
 * Revision 1.47  2005/07/06 19:54:09  vanmer
 * - added needed fields for the files table
 *
 * Revision 1.46  2005/06/30 04:50:02  vanmer
 * - added fields and tables for resolution and priority on activities
 *
 * Revision 1.45  2005/06/03 18:27:10  daturaarutad
 * add activity_recurrence_id to activities
 *
 * Revision 1.44  2005/05/25 05:36:32  vanmer
 * - added field to control user editing of activity types (defaults to 1, editable)
 * - added field to tell who completed an activity
 *
 * Revision 1.43  2005/05/25 05:25:38  daturaarutad
 * added activities_recurrence
 *
 * Revision 1.42  2005/05/24 23:01:47  braverock
 * - add email_template_type table in advance of email template type support in core
 *
 * Revision 1.41  2005/05/19 19:58:05  daturaarutad
 * added thread_id and followup_from_id to activities
 *
 * Revision 1.40  2005/05/18 21:42:54  vanmer
 * - added table for workflow_history to initial install
 *
 * Revision 1.39  2005/05/18 06:20:44  vanmer
 * - removed roles table from initial install
 * - removed role_id field from users table
 *
 * Revision 1.38  2005/05/06 00:58:01  vanmer
 * - added fields for user preferences
 *
 * Revision 1.37  2005/05/01 01:27:37  braverock
 * - remove InnoDB requirement from install and update scripts as
 *   it causes problems in non-MySQL env. or MySQL env w/o InnoDB support
 *
 * Revision 1.36  2005/04/26 18:11:00  gpowers
 * - added contacts.work_phone_ext
 *
 * Revision 1.35  2005/04/23 17:50:02  vanmer
 * - fixed database layout to reflect shorter fieldname for record status on activity participants
 *
 * Revision 1.34  2005/04/15 07:47:24  vanmer
 * - added tables for activity participants and positions
 *
 * Revision 1.33  2005/04/10 23:47:09  maulani
 * - Add address types
 *
 * Revision 1.32  2005/04/07 13:57:03  maulani
 * - Add salutation table to allow installation configurable list.  Also add
 *   many more default entries.
 *   RFE 913526 by algon.
 *
 * Revision 1.31  2005/03/20 01:45:54  maulani
 * - Remove company_company_type_map table because it is not used.
 *
 * Revision 1.30  2005/02/10 14:29:29  maulani
 * - Add last modified timestamp and user fields to activities
 *
 * Revision 1.29  2005/01/25 06:03:56  vanmer
 * - added tables for user preferences to install
 *
 * Revision 1.28  2005/01/24 00:17:19  maulani
 * - Add description to system parameters
 *
 * Revision 1.27  2005/01/09 18:27:59  braverock
 * - add test on time_zones and time_daylight_savings table creation
 *   resolves SF bug 1023849
 *
 * Revision 1.26  2005/01/06 21:51:25  vanmer
 * - added address_id to company_division table, for use in specifying addresses for divisions
 *
 * Revision 1.25  2005/01/06 20:45:11  vanmer
 * - added optional division_id to cases and opportunities
 *
 * Revision 1.24  2004/12/31 18:08:03  braverock
 * - add case_type_id FK to case_statuses
 * - add long_desc to case_statuses for consistency
 *
 * Revision 1.23  2004/09/16 19:45:28  vanmer
 * -added KEY for province in definition of time_zone table
 * -fixes problem with forced key during address addition
 *
 * Revision 1.22  2004/09/02 15:06:57  maulani
 * - Add indexes to addresses city and province to speed company search
 *
 * Revision 1.21  2004/08/21 01:41:09  d2uhlman
 * bad cut and paste job, missing comma on install sql
 *
 * Revision 1.20  2004/08/19 21:52:07  neildogg
 * - Adds field to activity templates for default text
 *
 * Revision 1.19  2004/08/11 16:56:01  gpowers
 * - Removed extra }'s
 *   - missing 'if' statements?
 *
 * Revision 1.18  2004/08/03 15:51:00  neildogg
 * - Added daylight savings and time zones tables and data for US
 *
 * Revision 1.17  2004/07/28 20:41:52  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.16  2004/07/21 20:30:30  neildogg
 * - Added saved_actions table
 *
 * Revision 1.15  2004/07/17 11:54:01  braverock
 * - add db_error_handler to each table creation for error reporting
 *
 * Revision 1.14  2004/07/15 15:12:52  maulani
 * - Fix activity_types creation error reported by jalperin with patch submitted
 *     by kerkness
 *
 * Revision 1.13  2004/07/13 18:15:59  neildogg
 * - Add database entries to allow a contact to be tied to the user
 *
 * Revision 1.12  2004/07/12 12:56:21  braverock
 * - add sort_order to activity_types table on install
 *   - resolves SF bug 987492 reported by kennyg1
 *
 * Revision 1.11  2004/07/07 20:48:16  neildogg
 * - Added database structure changes
 *
 * Revision 1.10  2004/07/01 12:56:34  braverock
 * - add relationships and relationship_types tables and data to install and update
 *
 * Revision 1.9  2004/06/04 14:54:08  braverock
 * - change activity_templates duration to varchar for advanced date functionality
 *
 * Revision 1.8  2004/06/03 16:23:13  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.7  2004/05/14 18:46:54  braverock
 * - change default gender to 'u'
 *
 * Revision 1.6  2004/05/04 23:48:03  maulani
 * - Added a system parameters table to the database.  This table can be used
 *   for items that would otherwise be dumped into the vars.php file. These
 *   include config items that are not required for database connectivity nor
 *   have access speed performance implications.  Accessor and setor functions
 *   added to utils-misc.
 * - Still need to create editing screen in admin section
 *
 * Revision 1.5  2004/04/13 15:47:12  maulani
 * - add data integrity check so all companies have addresses
 *
 * Revision 1.4  2004/04/13 15:06:42  maulani
 * - Add active contact data integrity check to database cleanup
 *
 * Revision 1.3  2004/04/12 14:34:02  maulani
 * - Add indexes for foreign key company_id
 *
 * Revision 1.2  2004/03/22 02:05:07  braverock
 * - add case_priority_score_adjustment to fix SF bug 906413
 *
 * Revision 1.1  2004/03/18 01:07:18  maulani
 * - Create installation tests to check whether the include location and
 *   vars.php have been configured.
 * - Create PHP-based database installation to replace old SQL scripts
 * - Create PHP-update routine to update users to latest schema/data as
 *   XRMS evolves.
 *
 */
?>