<?php
/**
 * install/data.php - This page populates the initial data needed for xrms
 *
 * This file contains the routines that will create all of the inital data for
 * the xrms tables.
 *
 * These routines are called from the main installation file, which has already
 * checked for proper variable and path setup; that a database connection exists;
 * and that all of the tables exist.
 *
 * @author Beth Macknik
 * $Id: data.php,v 1.7 2004/07/05 21:03:04 introspectshun Exp $
 */

/**
 * Create the miscellaneous table data.
 *
 */
function misc_db_data($con) {
    // system_parameters
    if (confirm_no_records($con, 'system_parameters')) {
        $sql ="insert into system_parameters (param_id, int_val) values ('Default GST Offset', -5)";
        $rst = $con->execute($sql);
    }

    // categories
    if (confirm_no_records($con, 'categories')) {
        $sql ="insert into categories (category_short_name, category_pretty_name, category_pretty_plural, category_display_html) values ('TEST1', 'Test Category 1', 'Test Category 1', 'Test Category 1')";
        $rst = $con->execute($sql);
        $sql ="insert into categories (category_short_name, category_pretty_name, category_pretty_plural, category_display_html) values ('TEST2', 'Test Category 2', 'Test Category 2', 'Test Category 2')";
        $rst = $con->execute($sql);
        $sql ="insert into categories (category_short_name, category_pretty_name, category_pretty_plural, category_display_html) values ('TEST3', 'Test Category 3', 'Test Category 3', 'Test Category 3')";
        $rst = $con->execute($sql);
    }

    // category_scopes
    if (confirm_no_records($con, 'category_scopes')) {
        $sql ="insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('COMP', 'Company', 'Companies', 'Company', 'companies')";
        $rst = $con->execute($sql);
        $sql ="insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('CONT', 'Contact', 'Contacts', 'Contact', 'contacts')";
        $rst = $con->execute($sql);
        $sql ="insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('OPP', 'Opportunity', 'Opportunities', 'Opportunity', 'opportunities')";
        $rst = $con->execute($sql);
        $sql ="insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('CASE', 'Case', 'Cases', 'Case', 'cases')";
        $rst = $con->execute($sql);
        $sql ="insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('CAMP', 'Campaign', 'Campaigns', 'Campaign', 'campaigns')";
        $rst = $con->execute($sql);
    }

    // category_category_scope_map
    if (confirm_no_records($con, 'category_category_scope_map')) {
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (1, 1)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (1, 2)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (1, 3)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (1, 4)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (1, 5)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (2, 1)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (2, 2)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (2, 3)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (2, 4)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (2, 5)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (3, 1)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (3, 2)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (3, 3)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (3, 4)";
        $rst = $con->execute($sql);
        $sql ="insert into category_category_scope_map (category_id, category_scope_id) values (3, 5)";
        $rst = $con->execute($sql);
    }

    // countries
    if (confirm_no_records($con, 'countries')) {
        $sql ="insert into countries (address_format_string_id, country_name, iso_code1, iso_code2, iso_code3, telephone_code) values (1, ' ', '', '', '', '')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Afghanistan', '004', 'AF', 'AFG', '93')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Albania', '008', 'AL', 'ALB', '355')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Algeria', '012', 'DZ', 'DZA', '213')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'American Samoa', '016', 'AS', 'ASM', '684')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Andorra', '020', 'AD', 'AND', '376')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Angola', '024', 'AO', 'AGO', '244')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Anguilla', '660', 'AI', 'AIA', '1 264')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Antarctica', '010', 'AQ', 'ATA', '672')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Antigua and Barbuda', '028', 'AG', 'ATG', '1 268')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (2, 'Argentina', '032', 'AR', 'ARG', '54')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Armenia', '051', 'AM', 'ARM', '374')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Aruba', '533', 'AW', 'ABW', '297')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (4, 'Australia', '036', 'AU', 'AUS', '61')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Austria', '040', 'AT', 'AUT', '43')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Azerbaijan', '031', 'AZ', 'AZE', '994')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Bahamas', '044', 'BS', 'BHS', '1 242')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Bahrain', '048', 'BH', 'BHR', '973')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Bangladesh', '050', 'BD', 'BGD', '880')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Barbados', '052', 'BB', 'BRB', '1 246')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Belarus', '112', 'BY', 'BLR', '375')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Belgium', '056', 'BE', 'BEL', '32')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Belize', '084', 'BZ', 'BLZ', '501')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Benin', '204', 'BJ', 'BEN', '229')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Bermuda', '060', 'BM', 'BMU', '1 441')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Bhutan', '064', 'BT', 'BTN', '975')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Bolivia', '068', 'BO', 'BOL', '591')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Bosnia and Herzegovina', '070', 'BA', 'BIH', '387')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Botswana', '072', 'BW', 'BWA', '267')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (3, 'Brazil', '076', 'BR', 'BRA', '55')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'British Virgin Islands', '092', 'VG', 'VGB', '1 284')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Brunei Darussalam', '096', 'BN', 'BRN', '673')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Bulgaria', '100', 'BG', 'BGR', '359')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Burkina Faso', '854', 'BF', 'BFA', '226')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Burundi', '108', 'BI', 'BDI', '257')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Cambodia', '116', 'KH', 'KHM', '855')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Cameroon', '120', 'CM', 'CMR', '237')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (4, 'Canada', '124', 'CA', 'CAN', '1')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Cape Verde', '132', 'CV', 'CPV', '238')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Cayman Islands', '136', 'KY', 'CYM', '1 345')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Central African Republic', '140', 'CF', 'CAF', '236')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Chad', '148', 'TD', 'TCD', '235')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Chile', '152', 'CL', 'CHL', '56')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (3, 'China', '156', 'CN', 'CHN', '86')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Christmas Island', '162', 'CX', 'CXR', '61')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Cocos (Keeling) Islands', '166', 'CC', 'CCK', '61')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Colombia', '170', 'CO', 'COL', '57')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Comoros', '174', 'KM', 'COM', '269')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Congo', '178', 'CG', 'COG', '242')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Cook Islands', '184', 'CK', 'COK', '682')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Costa Rica', '188', 'CR', 'CRI', '506')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Côte d’Ivoire', '384', 'CI', 'CIV', '225')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Croatia', '191', 'HR', 'HRV', '385')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Cuba', '192', 'CU', 'CUB', '53')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Cyprus', '196', 'CY', 'CYP', '357')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Czech Republic', '203', 'CZ', 'CZE', '420')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Democratic People''s Republic of Korea', '408', 'KP', 'PRK', '850')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Democratic Republic of the Congo', '180', 'CD', 'COD', '243')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (5, 'Denmark', '208', 'DK', 'DNK', '45')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Djibouti', '262', 'DJ', 'DJI', '253')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Dominica', '212', 'DM', 'DMA', '1 767')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Dominican Republic', '214', 'DO', 'DOM', '1 809')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Ecuador', '218', 'EC', 'ECU', '593')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Egypt', '818', 'EG', 'EGY', '20')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'El Salvador', '222', 'SV', 'SLV', '503')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Equatorial Guinea', '226', 'GQ', 'GNQ', '240')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Eritrea', '232', 'ER', 'ERI', '291')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Estonia', '233', 'EE', 'EST', '372')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Ethiopia', '231', 'ET', 'ETH', '251')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Faeroe Islands', '234', 'FO', 'FRO', '298')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Falkland Islands (Malvinas)', '238', 'FK', 'FLK', '500')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Federated States of Micronesia', '583', 'FM', 'FSM', '691')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Fiji', '242', 'FJ', 'FJI', '679')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Finland', '246', 'FI', 'FIN', '358')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'France', '250', 'FR', 'FRA', '33')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'France, metropolitan', '249', 'FX', 'FXX', '33')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'French Guiana', '254', 'GF', 'GUF', '594')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'French Polynesia', '258', 'PF', 'PYF', '689')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Gabon', '266', 'GA', 'GAB', '241')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Gambia', '270', 'GM', 'GMB', '220')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Georgia', '268', 'GE', 'GEO', '995')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Germany', '276', 'DE', 'DEU', '49')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Ghana', '288', 'GH', 'GHA', '233')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Gibraltar', '292', 'GI', 'GIB', '350')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Greece', '300', 'GR', 'GRC', '30')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Greenland', '304', 'GL', 'GRL', '299')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Grenada', '308', 'GD', 'GRD', '1 473')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Guadeloupe', '312', 'GP', 'GLP', '590')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Guam', '316', 'GU', 'GUM', '1 671')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Guatemala', '320', 'GT', 'GTM', '502')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Guinea', '324', 'GN', 'GIN', '224')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Guinea-Bissau', '624', 'GW', 'GNB', '245')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Guyana', '328', 'GY', 'GUY', '592')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Haiti', '332', 'HT', 'HTI', '509')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Holy See', '336', 'VA', 'VAT', '39')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Honduras', '340', 'HN', 'HND', '504')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (4, 'Hong Kong Special Administrative Region of China', '344', 'HK', 'HKG', '852')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (7, 'Hungary', '348', 'HU', 'HUN', '36')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Iceland', '352', 'IS', 'ISL', '354')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (8, 'India', '356', 'IN', 'IND', '91')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (9, 'Indonesia', '360', 'ID', 'IDN', '62')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Iran', '364', 'IR', 'IRN', '98')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Iraq', '368', 'IQ', 'IRQ', '964')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (4, 'Ireland', '372', 'IE', 'IRL', '353')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Israel', '376', 'IL', 'ISR', '972')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (3, 'Italy', '380', 'IT', 'ITA', '39')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Jamaica', '388', 'JM', 'JAM', '1 876')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (10, 'Japan', '392', 'JP', 'JPN', '81')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Jordan', '400', 'JO', 'JOR', '962')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Kazakhstan', '398', 'KZ', 'KAZ', '7')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Kenya', '404', 'KE', 'KEN', '254')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Kiribati', '296', 'KI', 'KIR', '686')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (2, 'Kuwait', '414', 'KW', 'KWT', '965')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Kyrgyzstan', '417', 'KG', 'KGZ', '996')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Lao People''s Democratic Republic', '418', 'LA', 'LAO', '856')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Latvia', '428', 'LV', 'LVA', '371')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Lebanon', '422', 'LB', 'LBN', '961')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Lesotho', '426', 'LS', 'LSO', '266')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Liberia', '430', 'LR', 'LBR', '231')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Libyan Arab Jamahiriya', '434', 'LY', 'LBY', '218')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Liechtenstein', '438', 'LI', 'LIE', '423')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Lithuania', '440', 'LT', 'LTU', '370')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Luxembourg', '442', 'LU', 'LUX', '352')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Macau', '446', 'MO', 'MAC', '853')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Madagascar', '450', 'MG', 'MDG', '261')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Malawi', '454', 'MW', 'MWI', '265')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Malaysia', '458', 'MY', 'MYS', '60')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Maldives', '462', 'MV', 'MDV', '960')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Mali', '466', 'ML', 'MLI', '223')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Malta', '470', 'MT', 'MLT', '356')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Marshall Islands', '584', 'MH', 'MHL', '692')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Martinique', '474', 'MQ', 'MTQ', '596')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Mauritania', '478', 'MR', 'MRT', '222')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Mauritius', '480', 'MU', 'MUS', '230')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Mayotte', '175', 'YT', 'MYT', '269')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (3, 'Mexico', '484', 'MX', 'MEX', '52')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Monaco', '492', 'MC', 'MCO', '377')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Mongolia', '496', 'MN', 'MNG', '976')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Montserrat', '500', 'MS', 'MSR', '1 664')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Morocco', '504', 'MA', 'MAR', '212')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Mozambique', '508', 'MZ', 'MOZ', '258')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Myanmar', '104', 'MM', 'MMR', '95')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Namibia', '516', 'NA', 'NAM', '264')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Nauru', '520', 'NR', 'NRU', '674')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Nepal', '524', 'NP', 'NPL', '977')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Netherlands', '528', 'NL', 'NLD', '31')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Netherlands Antilles', '530', 'AN', 'ANT', '599')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'New Caledonia', '540', 'NC', 'NCL', '687')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (8, 'New Zealand', '554', 'NZ', 'NZL', '64')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Nicaragua', '558', 'NI', 'NIC', '505')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Niger', '562', 'NE', 'NER', '227')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Nigeria', '566', 'NG', 'NGA', '234')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Niue', '570', 'NU', 'NIU', '683')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Norfolk Island', '574', 'NF', 'NFK', '672')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Northern Mariana Islands', '580', 'MP', 'MNP', '1 670')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Norway', '578', 'NO', 'NOR', '47')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (2, 'Oman', '512', 'OM', 'OMN', '968')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Pakistan', '586', 'PK', 'PAK', '92')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Palau', '585', 'PW', 'PLW', '680')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Panama', '591', 'PA', 'PAN', '507')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Papua New Guinea', '598', 'PG', 'PNG', '675')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Paraguay', '600', 'PY', 'PRY', '595')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Peru', '604', 'PE', 'PER', '51')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Philippines', '608', 'PH', 'PHL', '63')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (2, 'Poland', '616', 'PL', 'POL', '48')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (3, 'Portugal', '620', 'PT', 'PRT', '351')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Puerto Rico', '630', 'PR', 'PRI', '1 787')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Qatar', '634', 'QA', 'QAT', '974')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (11, 'Republic of Korea', '410', 'KR', 'KOR', '82')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Republic of Moldova', '498', 'MD', 'MDA', '373')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Réunion', '638', 'RE', 'REU', '262')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Romania', '642', 'RO', 'ROM', '40')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (12, 'Russian Federation', '643', 'RU', 'RUS', '7')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Rwanda', '646', 'RW', 'RWA', '250')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Saint Helena', '654', 'SH', 'SHN', '290')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Saint Kitts and Nevis', '659', 'KN', 'KNA', '1 869')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Saint Lucia', '662', 'LC', 'LCA', '1 758')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Saint Pierre and Miquelon', '666', 'PM', 'SPM', '508')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Saint Vincent and the Grenadines', '670', 'VC', 'VCT', '1 784')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Samoa', '882', 'WS', 'WSM', '685')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'San Marino', '674', 'SM', 'SMR', '378')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'São Tomé and Principe', '678', 'ST', 'STP', '239')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Saudi Arabia', '682', 'SA', 'SAU', '966')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Senegal', '686', 'SN', 'SEN', '221')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Seychelles', '690', 'SC', 'SYC', '248')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Sierra Leone', '694', 'SL', 'SLE', '232')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Singapore', '702', 'SG', 'SGP', '65')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Slovakia', '703', 'SK', 'SVK', '421')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Slovenia', '705', 'SI', 'SVN', '386')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Solomon Islands', '90', 'SB', 'SLB', '677')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Somalia', '706', 'SO', 'SOM', '252')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (13, 'South Africa', '710', 'ZA', 'ZAF', '27')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (3, 'Spain', '724', 'ES', 'ESP', '34')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Sri Lanka', '144', 'LK', 'LKA', '94')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Sudan', '736', 'SD', 'SDN', '249')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Suriname', '740', 'SR', 'SUR', '597')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Swaziland', '748', 'SZ', 'SWZ', '268')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Sweden', '752', 'SE', 'SWE', '46')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Switzerland', '756', 'CH', 'CHE', '41')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Syrian Arab Republic', '760', 'SY', 'SYR', '963')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (4, 'Taiwan', '158', 'TW', 'TWN', '886')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Tajikistan', '762', 'TJ', 'TJK', '7')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Thailand', '764', 'TH', 'THA', '66')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (14, 'The former Yugoslav Republic of Macedonia', '807', 'MK', 'MKD', '389')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Togo', '768', 'TG', 'TGO', '228')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Tonga', '776', 'TO', 'TON', '676')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Trinidad and Tobago', '780', 'TT', 'TTO', '1 868')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Tunisia', '788', 'TN', 'TUN', '216')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Turkey', '792', 'TR', 'TUR', '90')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Turkmenistan', '795', 'TM', 'TKM', '993')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Turks and Caicos Islands', '796', 'TC', 'TCA', '1 649')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Tuvalu', '798', 'TV', 'TUV', '688')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Uganda', '800', 'UG', 'UGA', '256')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (11, 'Ukraine', '804', 'UA', 'UKR', '380')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'United Arab Emirates', '784', 'AE', 'ARE', '971')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (13, 'United Kingdom', '826', 'GB', 'GBR', '44')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'United Republic of Tanzania', '834', 'TZ', 'TZA', '255')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (15, 'United States', '840', 'US', 'USA', '1')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (15, 'United States Virgin Islands', '850', 'VI', 'VIR', '1 340')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Uruguay', '858', 'UY', 'URY', '598')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Uzbekistan', '860', 'UZ', 'UZB', '998')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Vanuatu', '548', 'VU', 'VUT', '678')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Venezuela', '862', 'VE', 'VEN', '58')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Viet Nam', '704', 'VN', 'VNM', '84')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Yemen', '887', 'YE', 'YEM', '967')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Yugoslavia', '891', 'YU', 'YUG', '381')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Zambia', '894', 'ZM', 'ZMB', '260')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Zimbabwe', '716', 'ZW', 'ZWE', '263')";
        $rst = $con->execute($sql);
    }

    // address_format_strings
    if (confirm_no_records($con, 'address_format_strings')) {
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values ( 1, '" . '$lines<br>$city, $province $postal_code<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values ( 2, '" . '$lines<br>$postal_code $city<br>$province<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values ( 3, '" . '$lines<br>$postal_code $city $province<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values ( 4, '" . '$lines<br>$city $province $postal_code<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values ( 5, '" . '$lines<br>$postal_code $province<br>$city<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values ( 6, '" . '$lines<br>$postal_code $city<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values ( 7, '" . '$postal_code $city<br>$lines<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values ( 8, '" . '$lines<br>$province<br>$city $postal_code<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values ( 9, '" . '$lines<br>$city<br>$province $postal_code<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values (10, '" . '$postal_code<br>$province $city<br>$lines<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values (11, '" . '$lines<br>$city $province<br>$postal_code<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values (12, '" . '$country $postal_code<br>$province $city<br>$lines' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values (13, '" . '$lines<br>$city<br>$province<br>$postal_code<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values (14, '" . '$lines<br>$city $postal_code<br>$country' . "')";
        $rst = $con->execute($sql);
        $sql ="insert into address_format_strings (address_format_string_id, address_format_string) values (15, '" . '$lines<br>$city, $province $postal_code<br>$country' . "')";
        $rst = $con->execute($sql);
    }

} // end misc_db_data fn



/**
 * Create the user table data.
 *
 */
function user_db_data($con) {
    // roles
    if (confirm_no_records($con, 'roles')) {
        $sql ="insert into roles (role_short_name, role_pretty_name, role_pretty_plural, role_display_html) values ('User', 'User', 'Users', 'User')";
        $rst = $con->execute($sql);
        $sql ="insert into roles (role_short_name, role_pretty_name, role_pretty_plural, role_display_html) values ('Admin', 'Admin', 'Admin', 'Admin')";
        $rst = $con->execute($sql);
    }

    // users
    if (confirm_no_records($con, 'users')) {
        $sql ="insert into users (role_id, username, password, last_name, first_names, email, language) values (2, 'user1', '24c9e15e52afc47c225b757e7bee1f9d', 'One', 'User', 'user1@somecompany.com', 'english')";
        $rst = $con->execute($sql);
    }

} // end user_db_data fn



/**
 * Create the company tables.
 *
 */
function company_db_data($con) {
    // company_sources
    if (confirm_no_records($con, 'company_sources')) {
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('OTH', 'Other', 'Other', 'Other')";
        $rst = $con->execute($sql);
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('ADV', 'Advertisement', 'Advertisements', 'Advertisement')";
        $rst = $con->execute($sql);
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('DM', 'Direct Mail', 'Direct Mail', 'Direct Mail')";
        $rst = $con->execute($sql);
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('RAD', 'Radio', 'Radio', 'Radio')";
        $rst = $con->execute($sql);
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('SE', 'Search Engine', 'Search Engines', 'Search Engine')";
        $rst = $con->execute($sql);
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('SEM', 'Seminar', 'Seminars', 'Seminar')";
        $rst = $con->execute($sql);
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('TEL', 'Telemarketing', 'Telemarketings', 'Telemarketing')";
        $rst = $con->execute($sql);
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('TRD', 'Trade Show', 'Trade Shows', 'Trade Show')";
        $rst = $con->execute($sql);
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('WEB', 'Web Site', 'Web Sites', 'Web Site')";
        $rst = $con->execute($sql);
        $sql ="insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('WOM', 'Word of Mouth', 'Word of Mouth', 'Word of Mouth')";
        $rst = $con->execute($sql);
    }

    // industries
    if (confirm_no_records($con, 'industries')) {
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('OTH', 'Other', 'Other', 'Other')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('ADV', 'Advertising', 'Advertising', 'Advertising')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('ARCH', 'Architecture', 'Architecture', 'Architecture')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('CHEM', 'Chemicals', 'Chemicals', 'Chemicals')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('COM', 'Communications', 'Communications', 'Communications')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('COMP', 'Computers', 'Computers', 'Computers')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('CONST', 'Construction', 'Construction', 'Construction')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('CONS', 'Consulting', 'Consulting', 'Consulting')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('DIST', 'Distribution', 'Distribution', 'Distribution')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('EDU', 'Education', 'Education', 'Education')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('FIN', 'Finance', 'Finance', 'Finance')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('GOV', 'Government', 'Government', 'Government')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('HEAL', 'Healthcare', 'Healthcare', 'Healthcare')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('INS', 'Insurance', 'Insurance', 'Insurance')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('LEG', 'Legal', 'Legal', 'Legal')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('MAN', 'Manufacturing', 'Manufacturing', 'Manufacturing')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('NP', 'Non-Profit', 'Non-Profit', 'Non-Profit')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('RE', 'Real Estate', 'Real Estate', 'Real Estate')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('REST', 'Restaurant', 'Restaurant', 'Restaurant')";
        $rst = $con->execute($sql);
        $sql ="insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('RET', 'Retail', 'Retail', 'Retail')";
        $rst = $con->execute($sql);
    }

    // ratings
    if (confirm_no_records($con, 'ratings')) {
        $sql ="insert into ratings (rating_short_name, rating_pretty_name, rating_pretty_plural, rating_display_html) values ('N/A', 'N/A', 'N/A', '<font color=#999999><b>N/A</b></font>')";
        $rst = $con->execute($sql);
        $sql ="insert into ratings (rating_short_name, rating_pretty_name, rating_pretty_plural, rating_display_html) values ('Poor', 'Poor', 'Poor', '<font color=#cc0000><b>Poor</b></font>')";
        $rst = $con->execute($sql);
        $sql ="insert into ratings (rating_short_name, rating_pretty_name, rating_pretty_plural, rating_display_html) values ('Fair', 'Fair', 'Fair', '<font color=#ff9933><b>Fair</b></font>')";
        $rst = $con->execute($sql);
        $sql ="insert into ratings (rating_short_name, rating_pretty_name, rating_pretty_plural, rating_display_html) values ('Good', 'Good', 'Good', '<font color=#009900><b>Good</b></font>')";
        $rst = $con->execute($sql);
    }

    // account_statuses
    if (confirm_no_records($con, 'account_statuses')) {
        $sql ="insert into account_statuses (account_status_short_name, account_status_pretty_name, account_status_pretty_plural, account_status_display_html) values ('N/A', 'N/A', 'N/A', '<font color=#999999><b>N/A</b></font>')";
        $rst = $con->execute($sql);
        $sql ="insert into account_statuses (account_status_short_name, account_status_pretty_name, account_status_pretty_plural, account_status_display_html) values ('Closed', 'Closed', 'Closed', '<font color=#cc0000><b>Closed</b></font>')";
        $rst = $con->execute($sql);
        $sql ="insert into account_statuses (account_status_short_name, account_status_pretty_name, account_status_pretty_plural, account_status_display_html) values ('Hold', 'Hold', 'Hold', '<font color=#ff9933><b>Hold</b></font>')";
        $rst = $con->execute($sql);
        $sql ="insert into account_statuses (account_status_short_name, account_status_pretty_name, account_status_pretty_plural, account_status_display_html) values ('Approved', 'Approved', 'Approved', '<font color=#009900><b>Approved</b></font>')";
        $rst = $con->execute($sql);
    }

    // company_types
    if (confirm_no_records($con, 'company_types')) {
        $sql ="insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('CUST', 'Customer', 'Customers', 'Customer')";
        $rst = $con->execute($sql);
        $sql ="insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('VEND', 'Vendor', 'Vendors', 'Vendor')";
        $rst = $con->execute($sql);
        $sql ="insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('PART', 'Partner', 'Partners', 'Partner')";
        $rst = $con->execute($sql);
        $sql ="insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('COMP', 'Competitor', 'Competitors', 'Competitor')";
        $rst = $con->execute($sql);
        $sql ="insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('SPEC', 'Special', 'Special', 'Special')";
        $rst = $con->execute($sql);
    }

    // crm_statuses
    if (confirm_no_records($con, 'crm_statuses')) {
        $sql ="insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Lead', 'Lead', 'Leads', 'Lead')";
        $rst = $con->execute($sql);
        $sql ="insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Prospect', 'Prospect', 'Prospects', 'Prospect')";
        $rst = $con->execute($sql);
        $sql ="insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Qualified', 'Qualified', 'Qualified', 'Qualified')";
        $rst = $con->execute($sql);
        $sql ="insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Developed', 'Developed', 'Developed', 'Developed')";
        $rst = $con->execute($sql);
        $sql ="insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Closed', 'Closed', 'Closed', 'Closed')";
        $rst = $con->execute($sql);
    }

    // email_templates
    if (confirm_no_records($con, 'email_templates')) {
        $sql ="insert into email_templates (email_template_title, email_template_body) values ('Blank Template', '')";
        $rst = $con->execute($sql);
        $sql ="insert into email_templates (email_template_title, email_template_body) values ('Introduction', '')";
        $rst = $con->execute($sql);
        $sql ="insert into email_templates (email_template_title, email_template_body) values ('Sales Pitch', '')";
        $rst = $con->execute($sql);
        $sql ="insert into email_templates (email_template_title, email_template_body) values ('Thanks for Your Business', '')";
        $rst = $con->execute($sql);
        $sql ="insert into email_templates (email_template_title, email_template_body) values ('Customer Service Inquiry', '')";
        $rst = $con->execute($sql);
    }

    if (confirm_no_records($con, 'relationship_types')) {
        $sql = "INSERT INTO relationship_types
                (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
                VALUES
                ('company relationships','companies','companies','Acquired','Acquired by','a',NULL,NULL)";
        $rst = $con->execute($sql);
        $sql = "INSERT INTO relationship_types
                (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
                VALUES
                ('company relationships','companies','companies','Retains Consultant','Consultant for','a',NULL,NULL)";
        $rst = $con->execute($sql);
        $sql = "INSERT INTO relationship_types
                (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
                VALUES
                ('company relationships','companies','companies','Manufactures for','Uses Manufacturer','a',NULL,NULL)";
        $rst = $con->execute($sql);
        $sql = "INSERT INTO relationship_types
                (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
                VALUES
                ('company relationships','companies','companies','Parent Company of','Subsidiary of','a',NULL,NULL)";
        $rst = $con->execute($sql);
        $sql = "INSERT INTO relationship_types
                (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
                VALUES
                ('company relationships','companies','companies','Uses Supplier','Supplier for','a',NULL,NULL)";
        $rst = $con->execute($sql);
        $sql = "INSERT INTO relationship_types
                (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
                VALUES
                ('company link','contacts','companies','Owns','Owned By','a','<b>','</b>')";
        $rst = $con->execute($sql);
        $sql = "INSERT INTO relationship_types
                (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
                VALUES
                ('company link','contacts','companies','Manages','Managed By','a',NULL,NULL)";
        $rst = $con->execute($sql);
        $sql = "INSERT INTO relationship_types
                (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
                VALUES
                ('company link','contacts','companies','Consultant for','Retains Consultant','a',NULL,NULL)";
        $rst = $con->execute($sql);
    }

} // end company_db_data fn

/**
 * Create the opportunity tables.
 *
 */
function opportunity_db_data($con) {
    // opportunity_statuses
     if (confirm_no_records($con, 'opportunity_statuses')) {
        $sql ="insert into opportunity_statuses (sort_order, status_open_indicator, opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values (1, 'o', 'NEW', 'New', 'New', 'New')";
        $rst = $con->execute($sql);
        $sql ="insert into opportunity_statuses (sort_order, status_open_indicator, opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values (2, 'o', 'PRE', 'Preliminaries', 'Preliminaries', 'Preliminaries')";
        $rst = $con->execute($sql);
        $sql ="insert into opportunity_statuses (sort_order, status_open_indicator, opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values (3, 'o', 'DIS', 'Discussion', 'Discussion', 'Discussion')";
        $rst = $con->execute($sql);
        $sql ="insert into opportunity_statuses (sort_order, status_open_indicator, opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values (4, 'o', 'NEG', 'Negotiation', 'Negotiation', 'Negotiation')";
        $rst = $con->execute($sql);
        $sql ="insert into opportunity_statuses (sort_order, status_open_indicator, opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values (5, 'w', 'CLW', 'Closed/Won', 'Closed/Won', 'Closed/Won')";
        $rst = $con->execute($sql);
        $sql ="insert into opportunity_statuses (sort_order, status_open_indicator, opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values (6, 'l', 'CLL', 'Closed/Lost', 'Closed/Lost', 'Closed/Lost')";        $rst = $con->execute($sql);
        $rst = $con->execute($sql);
    }

} // end opportunity_db_data fn


/**
 * Create the case tables.
 *
 */
function case_db_data($con) {
    // case_types
     if (confirm_no_records($con, 'case_types')) {
        $sql ="insert into case_types (case_type_short_name, case_type_pretty_name, case_type_pretty_plural, case_type_display_html) values ('HELP', 'Help Item', 'Help Items', 'Help Item')";
        $rst = $con->execute($sql);
        $sql ="insert into case_types (case_type_short_name, case_type_pretty_name, case_type_pretty_plural, case_type_display_html) values ('BUG', 'Bug', 'Bugs', 'Bug')";
        $rst = $con->execute($sql);
        $sql ="insert into case_types (case_type_short_name, case_type_pretty_name, case_type_pretty_plural, case_type_display_html) values ('RFE', 'Feature Request', 'Feature Requests', 'Feature Request')";
        $rst = $con->execute($sql);
    }

    // case_statuses
     if (confirm_no_records($con, 'case_statuses')) {
        $sql ="insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (1, 'o', 'NEW', 'New', 'New', 'New')";
        $rst = $con->execute($sql);
        $sql ="insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (2, 'o', 'OPEN', 'Open', 'Open', 'Open')";
        $rst = $con->execute($sql);
        $sql ="insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (3, 'o', 'PRO', 'In Progress', 'In Progress', 'In Progress')";
        $rst = $con->execute($sql);
        $sql ="insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (4, 'c', 'FIN', 'Finished', 'Finished', 'Finished')";
        $rst = $con->execute($sql);
        $sql ="insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (5, 'c', 'CLO', 'Closed', 'Closed', 'Closed')";
        $rst = $con->execute($sql);
    }

    // case_priorities
     if (confirm_no_records($con, 'case_priorities')) {
        $sql ="insert into case_priorities (case_priority_short_name, case_priority_pretty_name, case_priority_pretty_plural, case_priority_display_html) values ('CRIT', 'Critical', 'Critical', 'Critical')";
        $rst = $con->execute($sql);
        $sql ="insert into case_priorities (case_priority_short_name, case_priority_pretty_name, case_priority_pretty_plural, case_priority_display_html) values ('HIGH', 'High', 'High', 'High')";
        $rst = $con->execute($sql);
        $sql ="insert into case_priorities (case_priority_short_name, case_priority_pretty_name, case_priority_pretty_plural, case_priority_display_html) values ('MED', 'Medium', 'Medium', 'Medium')";
        $rst = $con->execute($sql);
        $sql ="insert into case_priorities (case_priority_short_name, case_priority_pretty_name, case_priority_pretty_plural, case_priority_display_html) values ('LOW', 'Low', 'Low', 'Low')";
        $rst = $con->execute($sql);
    }

} // end case_db_data fn


/**
 * Create the campaign tables.
 *
 */
function campaign_db_data($con) {
    // campaign_types
     if (confirm_no_records($con, 'campaign_types')) {
        $sql ="insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('OTH', 'Other', 'Other', 'Other')";
        $rst = $con->execute($sql);
        $sql ="insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('EML', 'E-Mail', 'E-Mail', 'E-Mail')";
        $rst = $con->execute($sql);
        $sql ="insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('TEL', 'Phone', 'Phone', 'Phone')";
        $rst = $con->execute($sql);
        $sql ="insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('MAIL', 'Mail', 'Mail', 'Mail')";
        $rst = $con->execute($sql);
        $sql ="insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('EVT', 'Event', 'Event', 'Event')";
        $rst = $con->execute($sql);
        $sql ="insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('MAG', 'Magazine', 'Magazine', 'Magazine')";
        $rst = $con->execute($sql);
        $sql ="insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('TV', 'Television', 'Television', 'Television')";
        $rst = $con->execute($sql);
    }

    // campaign_statuses
     if (confirm_no_records($con, 'campaign_statuses')) {
        $sql ="insert into campaign_statuses (campaign_status_short_name, campaign_status_pretty_name, campaign_status_pretty_plural, campaign_status_display_html) values ('NEW', 'New', 'New', 'New')";
        $rst = $con->execute($sql);
        $sql ="insert into campaign_statuses (campaign_status_short_name, campaign_status_pretty_name, campaign_status_pretty_plural, campaign_status_display_html) values ('PLAN', 'Planning', 'Planning', 'Planning')";
        $rst = $con->execute($sql);
        $sql ="insert into campaign_statuses (campaign_status_short_name, campaign_status_pretty_name, campaign_status_pretty_plural, campaign_status_display_html) values ('ACT', 'Active', 'Active', 'Active')";
        $rst = $con->execute($sql);
        $sql ="insert into campaign_statuses (campaign_status_short_name, campaign_status_pretty_name, campaign_status_pretty_plural, campaign_status_display_html) values ('CLO', 'Closed', 'Closed', 'Closed')";
        $rst = $con->execute($sql);
    }

} // end campaign_db_data fn


/**
 * Create the activity tables.
 *
 */
function activity_db_data($con) {
    // activity_types
     if (confirm_no_records($con, 'activity_types')) {
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('CTO', 'call to', 'calls to', 'call to')";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('CFR', 'call from', 'calls from', 'call from')";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('ETO', 'e-mail to', 'e-mails to', 'e-mail to')";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('EFR', 'e-mail from', 'e-mails from', 'e-mail from')";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('FTO', 'fax to', 'faxes to', 'fax to')";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('FFR', 'fax from', 'faxes from', 'fax from')";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('FFR', 'letter to', 'letter to', 'letter to')";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('FFR', 'letter from', 'letter from', 'letter from')";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('FFR', 'internal', 'internal', 'internal')";
        $rst = $con->execute($sql);
    }

} // end activity_db_data fn



/**
 * Create the inital dataset.
 *
 */
function create_db_data($con) {
    misc_db_data($con);
    user_db_data($con);
    company_db_data($con);
    opportunity_db_data($con);
    case_db_data($con);
    campaign_db_data($con);
    activity_db_data($con);
} // end create_db_data fn


/**
 * $Log: data.php,v $
 * Revision 1.7  2004/07/05 21:03:04  introspectshun
 * - Removed relationship_type_id field for data-to-column consistency
 *
 * Revision 1.6  2004/07/01 15:23:06  braverock
 * - update default data for relationship_types table
 * - use NAMES -> VALUES SQL construction to be safe
 *
 * Revision 1.5  2004/07/01 12:56:33  braverock
 * - add relationships and relationship_types tables and data to install and update
 *
 * Revision 1.4  2004/05/21 14:34:07  maulani
 * - Add additional address formats in addition to the U.S.
 *
 * Revision 1.3  2004/05/04 23:48:03  maulani
 * - Added a system parameters table to the database.  This table can be used
 *   for items that would otherwise be dumped into the vars.php file. These
 *   include config items that are not required for database connectivity nor
 *   have access speed performance implications.  Accessor and setor functions
 *   added to utils-misc.
 * - Still need to create editing screen in admin section
 *
 * Revision 1.2  2004/03/25 15:08:58  maulani
 * - Repair bug 922629 reported by evergreencp (Addresses not displayed
 *   for companies)  The install script did not properly create the
 *   address format string record.
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