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
 * $Id: data.php,v 1.42 2006/04/26 02:15:23 vanmer Exp $
 */

/**
 * Create the miscellaneous table data.
 *
 */
function misc_db_data($con) {
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
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (4, 'Hong Kong', '344', 'HK', 'HKG', '852')";
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
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Macao', '446', 'MO', 'MAC', '853')";
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
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Romania', '642', 'RO', 'ROU', '40')";
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
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code, phone_format) values (15, 'United States', '840', 'US', 'USA', '1', '(###) ###-####')";
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
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Serbia and Montenegro', '891', 'CS', 'SCG', '381')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Zambia', '894', 'ZM', 'ZMB', '260')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (1, 'Zimbabwe', '716', 'ZW', 'ZWE', '263')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'Occupied Palestinian Territory', '275', 'PS', 'PSE', '970')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (9, 'Timor-Leste', '626', 'TL', 'TLS', '670')";
        $rst = $con->execute($sql);
        $sql ="insert into countries (address_format_string_id, country_name, un_code, iso_code2, iso_code3, telephone_code) values (6, 'land Islands', '248', 'AX', 'ALA', '670')";
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

    // time_daylight_savings
    if (confirm_no_records($con, 'time_daylight_savings')) {
        $sql = "INSERT INTO time_daylight_savings VALUES (1,'','',0,'','',0,0,'2004-08-02',0)";
        $con->execute($sql);
        $sql = "INSERT INTO time_daylight_savings VALUES (2,'','',0,'','',0,1,'2004-08-02',1)";
        $con->execute($sql);
        $sql = "INSERT INTO time_daylight_savings VALUES (3,'first','Sunday',4,'last','Sunday',0,1,'2004-08-02',1)";
        $con->execute($sql);
    }

    // time_zones
    if (confirm_no_records($con, 'time_zones')) {
        $sql = "INSERT INTO time_zones VALUES (1,218,'AL',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (2,218,'AK',NULL,NULL,3,-9,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (3,218,'AK','Anchorage',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (4,218,'AK','Bethel',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (5,218,'AK','College',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (6,218,'AK','Eielson AFB',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (7,218,'AK','Fairbanks',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (8,218,'AK','Juneau',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (9,218,'AK','Kalifornsky',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (10,218,'AK','Kenai',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (11,218,'AK','Ketchikan',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (12,218,'AK','Knik-Fairview',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (13,218,'AK','Kodiak',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (14,218,'AK','Lakes',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (15,218,'AK','Meadow Lakes',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (16,218,'AK','Sitka',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (17,218,'AK','Tanaina',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (18,218,'AK','Wasilla',NULL,3,-9,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (19,218,'AZ',NULL,NULL,1,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (20,218,'AR',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (21,218,'CA',NULL,NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (22,218,'CO',NULL,NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (23,218,'CT',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (24,218,'DE',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (25,218,'FL',NULL,NULL,3,-5,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (26,218,'FL','Alamonte Springs',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (27,218,'FL','Boca Raton',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (28,218,'FL','Boynton Beach',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (29,218,'FL','Bradenton',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (30,218,'FL','Cape Coral',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (31,218,'FL','Clearwater',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (32,218,'FL','Coral Gables',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (33,218,'FL','Coral Springs',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (34,218,'FL','Davie',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (35,218,'FL','Daytona Beach',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (36,218,'FL','Deerfield Beach',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (37,218,'FL','Delray Beach',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (38,218,'FL','Deltona',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (39,218,'FL','Fort Lauderdale',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (40,218,'FL','Fort Myers',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (41,218,'FL','Gainesville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (42,218,'FL','Hialeah',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (43,218,'FL','Hollywood',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (44,218,'FL','Jacksonville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (45,218,'FL','Kissimmee',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (46,218,'FL','Lakeland',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (47,218,'FL','Largo',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (48,218,'FL','Lauderhill',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (49,218,'FL','Margate',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (50,218,'FL','Melbourne',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (51,218,'FL','Miami Beach',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (52,218,'FL','Miami',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (53,218,'FL','Miramar',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (54,218,'FL','North Miami Beach',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (55,218,'FL','North Miami',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (56,218,'FL','Ocala',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (57,218,'FL','Orlando',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (58,218,'FL','Palm Bay',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (59,218,'FL','Pembroke Pines',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (60,218,'FL','Pensacola',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (61,218,'FL','Plantation',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (62,218,'FL','Pompano Beach',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (63,218,'FL','Port Orange',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (64,218,'FL','Port St. Lucie',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (65,218,'FL','Sarasota',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (66,218,'FL','St. Petersburg',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (67,218,'FL','Sunrise',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (68,218,'FL','Tallahassee',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (69,218,'FL','Tamarac',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (70,218,'FL','Tampa',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (71,218,'FL','Titusville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (72,218,'FL','West Palm Beach',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (73,218,'FL','Weston',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (74,218,'GA',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (75,218,'HI',NULL,NULL,1,-10,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (76,218,'ID',NULL,NULL,3,-7,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (77,218,'ID','Ammon',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (78,218,'ID','Blackfoot',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (79,218,'ID','Boise',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (80,218,'ID','Burley',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (81,218,'ID','Caldwell',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (82,218,'ID','Chubbuck',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (83,218,'ID','Coeur d\'Alene',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (84,218,'ID','Eagle',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (85,218,'ID','Garden',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (86,218,'ID','Hailey',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (87,218,'ID','Hayden',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (88,218,'ID','Idaho Falls',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (89,218,'ID','Jerome',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (90,218,'ID','Lewiston',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (91,218,'ID','Meridian',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (92,218,'ID','Moscow',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (93,218,'ID','Mountain Home',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (94,218,'ID','Nampa',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (95,218,'ID','Payette',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (96,218,'ID','Pocatello',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (97,218,'ID','Post Falls',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (98,218,'ID','Rexburg',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (99,218,'ID','Sandpoint',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (100,218,'ID','Twin Falls',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (101,218,'IL',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (102,218,'IN',NULL,NULL,1,-5,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (103,218,'IN','Alexandria',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (104,218,'IN','Anderson',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (105,218,'IN','Angola',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (106,218,'IN','Auburn',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (107,218,'IN','Avon',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (108,218,'IN','Batesville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (109,218,'IN','Bedford',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (110,218,'IN','Beech Grove',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (111,218,'IN','Bloomington',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (112,218,'IN','Bluffton',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (113,218,'IN','Boonville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (114,218,'IN','Brazil',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (115,218,'IN','Brownsburg',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (116,218,'IN','Carmel',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (117,218,'IN','Cedar Lake',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (118,218,'IN','Charlestown',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (119,218,'IN','Chesterton',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (120,218,'IN','Clarksville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (121,218,'IN','Columbia City',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (122,218,'IN','Columbus',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (123,218,'IN','Connersville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (124,218,'IN','Crawfordsville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (125,218,'IN','Crown Point',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (126,218,'IN','Danville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (127,218,'IN','Decatur',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (128,218,'IN','Dyer',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (129,218,'IN','East Chicago',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (130,218,'IN','Elkhart',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (131,218,'IN','Elwood',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (132,218,'IN','Evansville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (133,218,'IN','Evansville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (134,218,'IN','Fishers',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (135,218,'IN','Fort Wayne',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (136,218,'IN','Frankfort',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (137,218,'IN','Franklin',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (138,218,'IN','Gary',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (139,218,'IN','Gas City',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (140,218,'IN','Goshen',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (141,218,'IN','Greencastle',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (142,218,'IN','Greenfield',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (143,218,'IN','Greensburg',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (144,218,'IN','Greenwood',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (145,218,'IN','Griffith',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (146,218,'IN','Hammond',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (147,218,'IN','Hartford City',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (148,218,'IN','Highland',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (149,218,'IN','Hobart',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (150,218,'IN','Huntington',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (151,218,'IN','Indianapolis',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (152,218,'IN','Jasper',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (153,218,'IN','Jeffersonville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (154,218,'IN','Kendallville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (155,218,'IN','Kokomo',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (156,218,'IN','La Porte',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (157,218,'IN','Lafayette',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (158,218,'IN','Lake Station',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (159,218,'IN','Lawrence',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (160,218,'IN','Lebanon',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (161,218,'IN','Logansport',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (162,218,'IN','Lowell',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (163,218,'IN','Madison',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (164,218,'IN','Marion',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (165,218,'IN','Martinsville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (166,218,'IN','Merillville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (167,218,'IN','Michigan City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (168,218,'IN','Mishawaka',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (169,218,'IN','Mooresville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (170,218,'IN','Mount Vernon',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (171,218,'IN','Muncie',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (172,218,'IN','Munster',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (173,218,'IN','Nappanee',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (174,218,'IN','New Albany',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (175,218,'IN','New Castle',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (176,218,'IN','New Haven',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (177,218,'IN','Noblesville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (178,218,'IN','North Manchester',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (179,218,'IN','North Vernon',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (180,218,'IN','Peru',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (181,218,'IN','Plainfield',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (182,218,'IN','Plymouth',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (183,218,'IN','Portage',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (184,218,'IN','Portland',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (185,218,'IN','Princeton',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (186,218,'IN','Richmond',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (187,218,'IN','Rochester',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (188,218,'IN','Rushville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (189,218,'IN','Salem',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (190,218,'IN','Schererville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (191,218,'IN','Scottsburg',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (192,218,'IN','Sellersburg',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (193,218,'IN','Seymour',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (194,218,'IN','Shelbyville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (195,218,'IN','South Bend',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (196,218,'IN','Speedway',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (197,218,'IN','St. John',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (198,218,'IN','Tell City',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (199,218,'IN','Terre Haute',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (200,218,'IN','Valparaiso',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (201,218,'IN','Vincennes',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (202,218,'IN','Wabash',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (203,218,'IN','Warsaw',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (204,218,'IN','Washington',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (205,218,'IN','West Lafayette',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (206,218,'IN','Westfield',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (207,218,'IN','Zionsville',NULL,1,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (208,218,'IA',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (209,218,'KS',NULL,NULL,3,-6,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (210,218,'KS','Abilene',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (211,218,'KS','Andover',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (212,218,'KS','Arkansas City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (213,218,'KS','Atchison',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (214,218,'KS','Augusta',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (215,218,'KS','Bonner Springs',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (216,218,'KS','Chanute',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (217,218,'KS','Coffeyville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (218,218,'KS','Derby',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (219,218,'KS','Dodge City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (220,218,'KS','El Dorado',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (221,218,'KS','Emporia',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (222,218,'KS','Fort Scott',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (223,218,'KS','Garden City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (224,218,'KS','Gardner',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (225,218,'KS','Great Bend',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (226,218,'KS','Hays',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (227,218,'KS','Haysville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (228,218,'KS','Hutchinson',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (229,218,'KS','Independence',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (230,218,'KS','Iola',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (231,218,'KS','Junction City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (232,218,'KS','Kansas City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (233,218,'KS','Lansing',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (234,218,'KS','Lawrence',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (235,218,'KS','Leavenworth',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (236,218,'KS','Leawood',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (237,218,'KS','Lenexa',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (238,218,'KS','Liberal',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (239,218,'KS','Manhattan',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (240,218,'KS','McPherson',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (241,218,'KS','Merriam',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (242,218,'KS','Mission',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (243,218,'KS','Newton',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (244,218,'KS','Olathe',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (245,218,'KS','Ottowa',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (246,218,'KS','Overland Park',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (247,218,'KS','Parsons',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (248,218,'KS','Pittsburg',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (249,218,'KS','Prairie Village',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (250,218,'KS','Pratt',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (251,218,'KS','Roeland Park',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (252,218,'KS','Salina',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (253,218,'KS','Shawnee',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (254,218,'KS','Topeka',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (255,218,'KS','Wellington',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (256,218,'KS','Wichita',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (257,218,'KS','Winfield',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (258,218,'KY',NULL,NULL,3,-5,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (259,218,'KY','Alexandria',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (260,218,'KY','Ashland',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (261,218,'KY','Bardstown',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (262,218,'KY','Berea',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (263,218,'KY','Bowling Green',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (264,218,'KY','Campbellsville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (265,218,'KY','Covington',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (266,218,'KY','Danville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (267,218,'KY','Edgewood',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (268,218,'KY','Elizabethtown',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (269,218,'KY','Elsmere',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (270,218,'KY','Erlanger',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (271,218,'KY','Florence',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (272,218,'KY','Fort Knox',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (273,218,'KY','Fort Mitchell',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (274,218,'KY','Fort Thomas',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (275,218,'KY','Frankfort',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (276,218,'KY','Franklin',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (277,218,'KY','Georgetown',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (278,218,'KY','Glasgow',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (279,218,'KY','Harrodsburg',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (280,218,'KY','Henderson',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (281,218,'KY','Hopkinsville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (282,218,'KY','Independence',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (283,218,'KY','Jeffersontown',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (284,218,'KY','Lawrenceburg',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (285,218,'KY','Lexington-Fayette',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (286,218,'KY','Louisville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (287,218,'KY','Lyndon',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (288,218,'KY','Madisonville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (289,218,'KY','Mayfield',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (290,218,'KY','Maysville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (291,218,'KY','Middlesborough',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (292,218,'KY','Mount Washington',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (293,218,'KY','Murray',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (294,218,'KY','Newport',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (295,218,'KY','Nicholasville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (296,218,'KY','Owensboro',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (297,218,'KY','Paducah',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (298,218,'KY','Paris',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (299,218,'KY','Radcliff',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (300,218,'KY','Richmond',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (301,218,'KY','Shelbyville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (302,218,'KY','Shepherdsville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (303,218,'KY','Shively',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (304,218,'KY','Somerset',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (305,218,'KY','St. Matthews',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (306,218,'KY','Winchester',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (307,218,'LA',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (308,218,'ME',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (309,218,'MD',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (310,218,'MA',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (311,218,'MI',NULL,NULL,3,-5,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (312,218,'MI','Adrian',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (313,218,'MI','Allen Park',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (314,218,'MI','Anne Arbor',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (315,218,'MI','Auburn Hills',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (316,218,'MI','Battle Creek',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (317,218,'MI','Bedford',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (318,218,'MI','Birmingham',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (319,218,'MI','Blackman',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (320,218,'MI','Bloomfield',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (321,218,'MI','Brownstown',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (322,218,'MI','Burton',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (323,218,'MI','Canton',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (324,218,'MI','Chesterfield',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (325,218,'MI','Clinton',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (326,218,'MI','Commerce',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (327,218,'MI','Davison',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (328,218,'MI','Dearborn',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (329,218,'MI','Delhi',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (330,218,'MI','Delta',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (331,218,'MI','Detroit',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (332,218,'MI','East Lansing',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (333,218,'MI','Eastpointe',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (334,218,'MI','Farmington Hills',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (335,218,'MI','Ferndale',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (336,218,'MI','Flint',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (337,218,'MI','Forest Hills',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (338,218,'MI','Frenchtown',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (339,218,'MI','Gaines',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (340,218,'MI','Garden City',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (341,218,'MI','Genesee',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (342,218,'MI','Georgetown',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (343,218,'MI','Grand Blanc',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (344,218,'MI','Grand Rapids',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (345,218,'MI','Hamburg',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (346,218,'MI','Hamtramck',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (347,218,'MI','Harrison',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (348,218,'MI','Hazel Park',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (349,218,'MI','Highland',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (350,218,'MI','Holland',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (351,218,'MI','Independence',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (352,218,'MI','Inkster',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (353,218,'MI','Jackson',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (354,218,'MI','Kalamazoo',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (355,218,'MI','Kentwood',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (356,218,'MI','Lansing',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (357,218,'MI','Lincoln Park',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (358,218,'MI','Livonia',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (359,218,'MI','Macomb',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (360,218,'MI','Madison Heights',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (361,218,'MI','Mariquette',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (362,218,'MI','Meridian',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (363,218,'MI','Midland',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (364,218,'MI','Monroe',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (365,218,'MI','Mount Morris',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (366,218,'MI','Mount Pleasant',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (367,218,'MI','Muskegon',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (368,218,'MI','Northville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (369,218,'MI','Norton Shores',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (370,218,'MI','Novi',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (371,218,'MI','Oak Park',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (372,218,'MI','Okemos',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (373,218,'MI','Orion',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (374,218,'MI','Pittsfield',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (375,218,'MI','Plainfield',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (376,218,'MI','Plymouth',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (377,218,'MI','Pontiac',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (378,218,'MI','Port Huron',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (379,218,'MI','Portage',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (380,218,'MI','Redford',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (381,218,'MI','Rochester Hills',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (382,218,'MI','Romulus',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (383,218,'MI','Roseville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (384,218,'MI','Royal Oak',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (385,218,'MI','Saginaw',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (386,218,'MI','Shelby',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (387,218,'MI','Southfield',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (388,218,'MI','Southgate',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (389,218,'MI','St. Clair Shores',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (390,218,'MI','Sterling Heights',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (391,218,'MI','Summit',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (392,218,'MI','Taylor',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (393,218,'MI','Trenton',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (394,218,'MI','Troy',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (395,218,'MI','Van Buren',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (396,218,'MI','Walker',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (397,218,'MI','Warren',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (398,218,'MI','Washington',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (399,218,'MI','Waterford',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (400,218,'MI','Wayne',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (401,218,'MI','West Bloomfield',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (402,218,'MI','Westland',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (403,218,'MI','White Lake',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (404,218,'MI','Wyandotte',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (405,218,'MI','Wyoming',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (406,218,'MI','Ypsilanti',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (407,218,'MN',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (408,218,'MS',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (409,218,'MO',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (410,218,'MT',NULL,NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (411,218,'NE',NULL,NULL,3,-6,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (412,218,'NE','Alliance',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (413,218,'NE','Beatrice',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (414,218,'NE','Bellevue',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (415,218,'NE','Blair',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (416,218,'NE','Chadron',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (417,218,'NE','Chalco',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (418,218,'NE','Columbus',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (419,218,'NE','Crete',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (420,218,'NE','Elkhorn',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (421,218,'NE','Fremont',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (422,218,'NE','Gering',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (423,218,'NE','Grand Island',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (424,218,'NE','Hastings',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (425,218,'NE','Holdrege',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (426,218,'NE','Kearney',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (427,218,'NE','La Vista',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (428,218,'NE','Lexington',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (429,218,'NE','Lincoln',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (430,218,'NE','McCook',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (431,218,'NE','Nebraska City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (432,218,'NE','Norfolk',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (433,218,'NE','North Platte',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (434,218,'NE','Offutt AFB',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (435,218,'NE','Omaha',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (436,218,'NE','Papillion',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (437,218,'NE','Plattsmouth',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (438,218,'NE','Ralston',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (439,218,'NE','Scottsbluff',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (440,218,'NE','Seward',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (441,218,'NE','Sidney',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (442,218,'NE','South Sioux City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (443,218,'NE','York',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (444,218,'NV',NULL,NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (445,218,'NH',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (446,218,'NJ',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (447,218,'NM',NULL,NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (448,218,'NY',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (449,218,'NC',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (450,218,'ND',NULL,NULL,3,-6,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (451,218,'ND','Bismarck',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (452,218,'ND','Devils Lake',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (453,218,'ND','Dickinson',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (454,218,'ND','Fargo',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (455,218,'ND','Grand Forks',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (456,218,'ND','Jamestown',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (457,218,'ND','Mandan',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (458,218,'ND','Minot',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (459,218,'ND','Valley City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (460,218,'ND','Wahpeton',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (461,218,'ND','West Fargo',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (462,218,'ND','Williston',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (463,218,'OH',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (464,218,'OK',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (465,218,'OR',NULL,NULL,3,-8,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (466,218,'OR','Albany',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (467,218,'OR','Aloha',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (468,218,'OR','Altamont',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (469,218,'OR','Ashland',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (470,218,'OR','Beaverton',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (471,218,'OR','Bend',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (472,218,'OR','Canby',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (473,218,'OR','Cedar Mill',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (474,218,'OR','Central Point',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (475,218,'OR','City of The Dalles',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (476,218,'OR','Coos Bay',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (477,218,'OR','Corvalis',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (478,218,'OR','Dallas',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (479,218,'OR','Eugene',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (480,218,'OR','Forest Grove',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (481,218,'OR','Four Corners',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (482,218,'OR','Gladstone',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (483,218,'OR','Grants Pass',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (484,218,'OR','Gresham',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (485,218,'OR','Hayesville',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (486,218,'OR','Hermiston',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (487,218,'OR','Hillsboro',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (488,218,'OR','Keizer',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (489,218,'OR','Klamath Falls',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (490,218,'OR','La Grande',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (491,218,'OR','Lake Oswego',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (492,218,'OR','Lebanon',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (493,218,'OR','McMinnville',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (494,218,'OR','Medford',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (495,218,'OR','Milwaukie',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (496,218,'OR','Newberg',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (497,218,'OR','Oak Grove',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (498,218,'OR','Oatfield',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (499,218,'OR','Ontario',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (500,218,'OR','Oregon City',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (501,218,'OR','Pendleton',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (502,218,'OR','Portland',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (503,218,'OR','Redmond',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (504,218,'OR','Roseburg',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (505,218,'OR','Salem',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (506,218,'OR','Sherwood',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (507,218,'OR','Springfield',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (508,218,'OR','Tigard',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (509,218,'OR','Troutdale',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (510,218,'OR','Tualatin',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (511,218,'OR','West Linn',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (512,218,'OR','Wilsonville',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (513,218,'OR','Woddburn',NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (514,218,'PA',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (515,218,'PR',NULL,NULL,1,-4,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (516,218,'RI',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (517,218,'SC',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (518,218,'SD',NULL,NULL,3,-6,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (519,218,'SD','Aberdeen',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (520,218,'SD','Brookings',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (521,218,'SD','Huron',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (522,218,'SD','Mitchell',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (523,218,'SD','Pierre',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (524,218,'SD','Rapid City',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (525,218,'SD','Rapid Valley',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (526,218,'SD','Sioux Falls',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (527,218,'SD','Spearfish',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (528,218,'SD','Vermillion',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (529,218,'SD','Watertown',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (530,218,'SD','Yankton',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (531,218,'TN',NULL,NULL,3,-6,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (532,218,'TN','Alcoa',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (533,218,'TN','Athens',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (534,218,'TN','Bartlett',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (535,218,'TN','Bloomingdale',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (536,218,'TN','Brentwood',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (537,218,'TN','Bristol',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (538,218,'TN','Brownsville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (539,218,'TN','Chattanooga',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (540,218,'TN','Clarksville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (541,218,'TN','Cleveland',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (542,218,'TN','Clinton',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (543,218,'TN','Collegedale',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (544,218,'TN','Collierville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (545,218,'TN','Colonial Heights',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (546,218,'TN','Columbia',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (547,218,'TN','Cookeville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (548,218,'TN','Covington',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (549,218,'TN','Crossville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (550,218,'TN','Dickson',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (551,218,'TN','Dyersburg',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (552,218,'TN','East Brainerd',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (553,218,'TN','East Ridge',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (554,218,'TN','Elizabethton',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (555,218,'TN','Farragut',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (556,218,'TN','Fayetteville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (557,218,'TN','Franklin',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (558,218,'TN','Gallatin',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (559,218,'TN','Germantown',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (560,218,'TN','Goodlettsville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (561,218,'TN','Green Hill',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (562,218,'TN','Greeneville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (563,218,'TN','Harriman',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (564,218,'TN','Harrison',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (565,218,'TN','Hendersonville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (566,218,'TN','Humboldt',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (567,218,'TN','Jackson',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (568,218,'TN','Jefferson City',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (569,218,'TN','Johnson City',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (570,218,'TN','Kingsport',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (571,218,'TN','Knoxville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (572,218,'TN','La Follette',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (573,218,'TN','La Vergne',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (574,218,'TN','Lakeland',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (575,218,'TN','Lawrenceburg',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (576,218,'TN','Lebanon',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (577,218,'TN','Lenoir City',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (578,218,'TN','Lewisburg',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (579,218,'TN','Lexington',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (580,218,'TN','Manchester',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (581,218,'TN','Martin',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (582,218,'TN','Maryville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (583,218,'TN','McMinnville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (584,218,'TN','Memphis',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (585,218,'TN','Middle Valley',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (586,218,'TN','Milan',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (587,218,'TN','Millington',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (588,218,'TN','Morristown',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (589,218,'TN','Mount Juliet',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (590,218,'TN','Murfreesboro',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (591,218,'TN','Nashville-Davidson',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (592,218,'TN','Newport',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (593,218,'TN','Oak Ridge',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (594,218,'TN','Paris',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (595,218,'TN','Portland',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (596,218,'TN','Pulaski',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (597,218,'TN','Red Bank',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (598,218,'TN','Ripley',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (599,218,'TN','Savannah',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (600,218,'TN','Sevierville',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (601,218,'TN','Seymour',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (602,218,'TN','Shelbyville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (603,218,'TN','Signal Mountain',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (604,218,'TN','Smyrna',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (605,218,'TN','Soddy-Daisy',NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (606,218,'TN','Spring Hill',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (607,218,'TN','Springfield',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (608,218,'TN','Tullahoma',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (609,218,'TN','Union',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (610,218,'TN','White House',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (611,218,'TN','Winchester',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (612,218,'TX',NULL,NULL,3,-6,'n')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (613,218,'TX','Abilene',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (614,218,'TX','Allen',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (615,218,'TX','Amarillo',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (616,218,'TX','Arlington',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (617,218,'TX','Atascocita',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (618,218,'TX','Austin',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (619,218,'TX','Baytown',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (620,218,'TX','Beaumont',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (621,218,'TX','Bedford',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (622,218,'TX','Big Spring',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (623,218,'TX','Brownsville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (624,218,'TX','Bryan',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (625,218,'TX','Carrollton',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (626,218,'TX','Cedar Hill',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (627,218,'TX','Cedar Park',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (628,218,'TX','Channelview',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (629,218,'TX','Cleburne',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (630,218,'TX','College Station',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (631,218,'TX','Conroe',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (632,218,'TX','Coppell',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (633,218,'TX','Copperas Cove',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (634,218,'TX','Corpus Christi',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (635,218,'TX','Corsicana',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (636,218,'TX','Dallas',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (637,218,'TX','Deer Park',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (638,218,'TX','Del Rio',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (639,218,'TX','Denton',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (640,218,'TX','DeSoto',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (641,218,'TX','Duncanville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (642,218,'TX','Edinburg',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (643,218,'TX','El Paso',NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (644,218,'TX','Euless',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (645,218,'TX','Farmers Branch',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (646,218,'TX','Flower Mound',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (647,218,'TX','Fort Hood',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (648,218,'TX','Fort Worth',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (649,218,'TX','Friendswood',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (650,218,'TX','Frisco',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (651,218,'TX','Galveston',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (652,218,'TX','Garland',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (653,218,'TX','Georgetown',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (654,218,'TX','Grand Prairie',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (655,218,'TX','Grapevine',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (656,218,'TX','Haltom City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (657,218,'TX','Harlingen',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (658,218,'TX','Houston',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (659,218,'TX','Huntsville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (660,218,'TX','Hurst',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (661,218,'TX','Irving',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (662,218,'TX','Keller',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (663,218,'TX','Killeen',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (664,218,'TX','Kingsville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (665,218,'TX','La Porte',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (666,218,'TX','Lake Jackson',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (667,218,'TX','Lancaster',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (668,218,'TX','Laredo',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (669,218,'TX','League City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (670,218,'TX','Lewisville',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (671,218,'TX','Longview',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (672,218,'TX','Lubbock',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (673,218,'TX','Lufkin',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (674,218,'TX','Mansfield',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (675,218,'TX','McAllen',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (676,218,'TX','McKinney',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (677,218,'TX','Mesquite',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (678,218,'TX','Midland',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (679,218,'TX','Mission Bend',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (680,218,'TX','Mission',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (681,218,'TX','Missouri City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (682,218,'TX','Nacogdoches',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (683,218,'TX','New Braunfels',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (684,218,'TX','North Richland Hills',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (685,218,'TX','Odessa',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (686,218,'TX','Paris',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (687,218,'TX','Pasadena',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (688,218,'TX','Pearland',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (689,218,'TX','Pharr',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (690,218,'TX','Plano',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (691,218,'TX','Port Arthur',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (692,218,'TX','Richardson',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (693,218,'TX','Round Rock',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (694,218,'TX','Rowlett',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (695,218,'TX','San Angelo',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (696,218,'TX','San Antonio',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (697,218,'TX','San Juan',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (698,218,'TX','San Marcos',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (699,218,'TX','Sherman',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (700,218,'TX','Socorro',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (701,218,'TX','Spring',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (702,218,'TX','Sugar Land',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (703,218,'TX','Temple',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (704,218,'TX','Texarkana',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (705,218,'TX','Texas City',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (706,218,'TX','The Colony',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (707,218,'TX','The Woodlands',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (708,218,'TX','Tyler',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (709,218,'TX','Victoria',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (710,218,'TX','Waco',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (711,218,'TX','Weslaco',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (712,218,'TX','Wichita Falls',NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (713,218,'UT',NULL,NULL,3,-7,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (714,218,'VT',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (715,218,'VA',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (716,218,'WA',NULL,NULL,3,-8,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (717,218,'DC',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (718,218,'WV',NULL,NULL,3,-5,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (719,218,'WI',NULL,NULL,3,-6,'y')";
        $con->execute($sql);
        $sql = "INSERT INTO time_zones VALUES (720,218,'WY',NULL,NULL,3,-7,'y')";
        $con->execute($sql);
    }

    // address_types
    if (confirm_no_records($con, 'address_types')) {
        $sql = "INSERT INTO address_types VALUES (1,'unknown','100')";
        $con->execute($sql);
        $sql = "INSERT INTO address_types VALUES (2,'commercial','200')";
        $con->execute($sql);
        $sql = "INSERT INTO address_types VALUES (3,'residential','300')";
        $con->execute($sql);
    }

    // salutations
    if (confirm_no_records($con, 'salutations')) {
        $sql = "INSERT INTO salutations VALUES (1,'Mr.','100')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (2,'Mrs.','103')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (3,'Ms.','106')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (4,'Miss','109')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (5,'Dr.','112')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (6,'-','113')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (100,'A V M','200')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (101,'Admiraal','203')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (102,'Admiral','206')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (103,'Air Cdre','209')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (104,'Air Commodore','212')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (105,'Air Marshal','215')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (106,'Air Vice Marshal','218')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (107,'Alderman','221')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (108,'Alhaji','224')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (109,'Ambassador','227')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (110,'Baron','230')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (111,'Barones','233')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (112,'Brig','236')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (113,'Brig Gen','239')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (114,'Brig General','242')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (115,'Brigadier','245')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (116,'Brigadier General','248')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (117,'Brother','251')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (118,'Canon','254')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (119,'Capt','257')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (120,'Captain','260')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (121,'Cardinal','263')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (122,'Cdr','266')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (123,'Chief','269')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (124,'Cik','272')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (125,'Cmdr','275')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (126,'Col','278')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (127,'Col Dr','281')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (128,'Colonel','284')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (129,'Commandant','287')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (130,'Commander','290')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (131,'Commissioner','293')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (132,'Commodore','296')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (133,'Comte','299')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (134,'Comtessa','302')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (135,'Congressman','305')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (136,'Conseiller','308')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (137,'Consul','311')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (138,'Conte','314')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (139,'Contessa','317')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (140,'Corporal','320')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (141,'Councillor','323')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (142,'Count','326')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (143,'Countess','329')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (144,'Crown Prince','332')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (145,'Crown Princess','335')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (146,'Dame','338')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (147,'Datin','341')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (148,'Dato','344')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (149,'Datuk','347')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (150,'Datuk Seri','350')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (151,'Deacon','353')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (152,'Deaconess','356')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (153,'Dean','359')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (154,'Dhr','362')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (155,'Dipl Ing','365')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (156,'Doctor','368')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (157,'Dott','371')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (158,'Dott sa','374')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (159,'Dr Ing','377')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (160,'Dra','380')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (161,'Drs','383')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (162,'Embajador','386')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (163,'Embajadora','389')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (164,'En','392')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (165,'Encik','395')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (166,'Eng','398')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (167,'Eur Ing','401')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (168,'Exma Sra','404')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (169,'Exmo Sr','407')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (170,'F O','410')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (171,'Father','413')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (172,'First Lieutient','416')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (173,'First Officer','419')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (174,'Flt Lieut','422')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (175,'Flying Officer','425')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (176,'Fr','428')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (177,'Frau','431')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (178,'Fraulein','434')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (179,'Fru','437')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (180,'Gen','440')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (181,'Generaal','443')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (182,'General','446')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (183,'Governor','449')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (184,'Graaf','452')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (185,'Gravin','455')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (186,'Group Captain','458')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (187,'Grp Capt','461')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (188,'H E Dr','464')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (189,'H H','467')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (190,'H M','470')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (191,'H R H','473')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (192,'Hajah','476')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (193,'Haji','479')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (194,'Hajim','482')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (195,'Her Highness','485')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (196,'Her Majesty','488')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (197,'Herr','491')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (198,'High Chief','494')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (199,'His Highness','497')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (200,'His Holiness','500')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (201,'His Majesty','503')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (202,'Hon','506')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (203,'Hr','509')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (204,'Hra','512')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (205,'Ing','515')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (206,'Ir','518')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (207,'Jonkheer','521')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (208,'Judge','524')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (209,'Justice','527')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (210,'Khun Ying','530')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (211,'Kolonel','533')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (212,'Lady','536')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (213,'Lcda','539')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (214,'Lic','542')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (215,'Lieut','545')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (216,'Lieut Cdr','548')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (217,'Lieut Col','551')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (218,'Lieut Gen','554')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (219,'Lord','557')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (220,'M','560')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (221,'M L','563')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (222,'M R','566')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (223,'Madame','569')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (224,'Mademoiselle','572')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (225,'Maj Gen','575')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (226,'Major','578')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (227,'Master','581')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (228,'Mevrouw','584')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (229,'Mlle','587')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (230,'Mme','590')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (231,'Monsieur','593')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (232,'Monsignor','596')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (233,'Mstr','599')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (234,'Nti','602')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (235,'Pastor','605')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (236,'President','608')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (237,'Prince','611')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (238,'Princess','614')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (239,'Princesse','617')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (240,'Prinses','620')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (241,'Prof','623')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (242,'Prof Dr','626')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (243,'Prof Sir','629')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (244,'Professor','632')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (245,'Puan','635')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (246,'Puan Sri','638')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (247,'Rabbi','641')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (248,'Rear Admiral','644')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (249,'Rev.','647')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (250,'Rev Canon','650')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (251,'Rev Dr','653')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (252,'Rev Mother','656')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (253,'Reverend','659')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (254,'Rva','662')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (255,'Senator','665')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (256,'Sergeant','668')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (257,'Sheikh','671')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (258,'Sheikha','674')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (259,'Sig','677')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (260,'Sig na','680')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (261,'Sig ra','683')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (262,'Sir','686')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (263,'Sister','689')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (264,'Sqn Ldr','692')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (265,'Sr','695')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (266,'Sr D','698')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (267,'Sra','701')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (268,'Srta','704')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (269,'Sultan','707')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (270,'Tan Sri','710')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (271,'Tan Sri Dato','713')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (272,'Tengku','716')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (273,'Teuku','719')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (274,'Than Puying','722')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (275,'The Hon Dr','725')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (276,'The Hon Justice','728')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (277,'The Hon Miss','731')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (278,'The Hon Mr','734')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (279,'The Hon Mrs','737')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (280,'The Hon Ms','740')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (281,'The Hon Sir','743')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (282,'The Very Rev','746')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (283,'Toh Puan','749')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (284,'Tun','752')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (285,'Vice Admiral','755')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (286,'Viscount','758')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (287,'Viscountess','761')";
        $con->execute($sql);
        $sql = "INSERT INTO salutations VALUES (288,'Wg Cdr','764')";
    }
} // end misc_db_data fn



/**
 * Create the user table data.
 *
 */
function user_db_data($con) {
    // users
    if (confirm_no_records($con, 'users')) {
        $sql ="insert into users (user_contact_id, username, password, last_name, first_names, email, language) values (0, 'user1', '24c9e15e52afc47c225b757e7bee1f9d', 'One', 'User', 'user1@somecompany.com', 'english')";
        $rst = $con->execute($sql);
        $user_id=$con->Insert_ID();
        $group_id=get_group_id(false, 'Users');
        $role_id=get_role_id(false, 'Administrator');
        $ret=add_user_group(false, $group_id, $user_id, $role_id);
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
        $sql ="insert into crm_statuses (sort_order, crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values (1, 'Lead', 'Lead', 'Leads', 'Lead')";
        $rst = $con->execute($sql);
        $sql ="insert into crm_statuses (sort_order, crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values (2, 'Prospect', 'Prospect', 'Prospects', 'Prospect')";
        $rst = $con->execute($sql);
        $sql ="insert into crm_statuses (sort_order, crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values (3, 'Qualified', 'Qualified', 'Qualified', 'Qualified')";
        $rst = $con->execute($sql);
        $sql ="insert into crm_statuses (sort_order, crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values (4, 'Developed', 'Developed', 'Developed', 'Developed')";
        $rst = $con->execute($sql);
        $sql ="insert into crm_statuses (sort_order, crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values (5, 'Closed', 'Closed', 'Closed', 'Closed')";
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
    if (confirm_no_records($con, 'email_template_type')) {
        $sql ="insert into email_template_type (email_template_type_id , email_template_type_name) values (1, 'Email Merge Letter')";
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
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('CTO', 'call to', 'calls to', 'call to',1,0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('CFR', 'call from', 'calls from', 'call from',2,0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('ETO', 'e-mail to', 'e-mails to', 'e-mail to',3, 0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order, user_editable_flag) values ('EFR', 'e-mail from', 'e-mails from', 'e-mail from',4,0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order, user_editable_flag) values ('FTO', 'fax to', 'faxes to', 'fax to',5, 0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('FFR', 'fax from', 'faxes from', 'fax from',6, 0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('LTT', 'letter to', 'letter to', 'letter to',7, 0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('LTF', 'letter from', 'letter from', 'letter from',8, 0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('INT', 'internal', 'internal', 'internal',9,0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('PRO', 'process', 'process', 'process',10,0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('SYS', 'system', 'system', 'system',11,0)";
        $rst = $con->execute($sql);
        $sql ="insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, sort_order,user_editable_flag) values ('MTG', 'meeting, 'meetings', 'meeting',12,0)";
        $rst = $con->execute($sql);
    }

     if (confirm_no_records($con, 'activity_participant_positions')) {
       $sql = " insert into activity_participant_positions (activity_type_id , participant_position_name , global_flag) values ( NULL , 'Participant', 1)";
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }
     if (confirm_no_records($con, 'activity_resolution_types')) {
       $sql = " insert into activity_resolution_types (resolution_short_name, resolution_pretty_name, sort_order) values ( 'Resolved' , 'Closed/Resolved', 1)";
        $rst = $con->execute($sql);
       $sql = " insert into activity_resolution_types (resolution_short_name, resolution_pretty_name, sort_order) values ( 'Unresolved' , 'Closed/Unresolved', 2)";
        $rst = $con->execute($sql);
       $sql = " insert into activity_resolution_types (resolution_short_name, resolution_pretty_name, sort_order) values ( 'Obsolete' , 'Obsolete/Out of Date', 3)";
        $rst = $con->execute($sql);
       $sql = " insert into activity_resolution_types (resolution_short_name, resolution_pretty_name, sort_order) values ( 'Cancel' , 'Cancelled', 4)";
        $rst = $con->execute($sql);
       $sql = " insert into activity_resolution_types (resolution_short_name, resolution_pretty_name, sort_order) values ( 'Complete' , 'Completed', 5)";
        $rst = $con->execute($sql);
       $sql = " insert into activity_resolution_types (resolution_short_name, resolution_pretty_name, sort_order) values ( 'Duplicate' , 'Closed/Duplicate', 2)";
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
        }
    }

} // end activity_db_data fn

function user_preferences_db_data($con) {
    //adding strings for user preference types in order to allow them to be translated while still stored as English in the database
    $s=_("Language");
    $s=_("Theme");
    $s=_("Color and Layout Theme for XRMS");


    add_user_preference_type($con, 'user_language', 'Language', false, false, true, 'select');
    add_user_preference_type($con, 'css_theme', 'Theme', 'Color and Layout Theme for XRMS', false, true, 'select');

    $hide_type=add_user_preference_type($con, 'hide_sf_img',  'Hide SourceForge Image', 'Hides the SourceForge Image which appears at the bottom of every page', false, false, 'select');
    $block_type=add_user_preference_type($con, 'block_sf_link', 'Block SourceForge Link', 'Disables the SourceForge image and link which appears at the bottom of every page', false, false, 'select');

    $s=_("Hide Sourceforge Image");
    $s=_("Block Sourceforge Link");
    $s=_("Disables the SourceForge image and link which appears at the bottom of every page");
    $s=_("Hides the SourceForge Image which appears at the bottom of every page");

    add_preference_option($con, $hide_type, 'y', 'Yes', 1);
    add_preference_option($con, $hide_type, 'n', 'No', 2);

    add_preference_option($con, $block_type, 'y', 'Yes', 1);
    add_preference_option($con, $block_type, 'n', 'No', 2);

    $ret=get_admin_preference($con, $hide_type);
    if (!$ret) {
        set_admin_preference($con, $hide_type, 'y');
    }

    $ret=get_admin_preference($con, $block_type);
    if (!$ret) {
        set_admin_preference($con, $block_type, 'n');
    }

    $s=_("Company Search Type");
    $s=_("Search method used when searching company names");
    $s=_("Name Contains");
    $s=_("Name Starts With");
    $s=_("Name Ends With");
    $s=_("Name Exactly Matches");

    $company_search_type=add_user_preference_type($con, 'company_search_type', "Company Search Type", "Search method used when searching company names", false, false, 'select');
    add_preference_option($con, $company_search_type, 'contains', 'Name Contains');
    add_preference_option($con, $company_search_type, 'starts', 'Name Starts With');
    add_preference_option($con, $company_search_type, 'ends', 'Name Ends With');
    add_preference_option($con, $company_search_type, 'matches', 'Name Exactly Matches');
    $ret=get_admin_preference($con, $company_search_type);
    if (!$ret) {
        set_admin_preference($con, $company_search_type, 'contains');
    }

    $s=_("XRMS Version");
    $s=_("XRMS Version (read-only)");
    $xrms_version_type=add_user_preference_type($con, 'xrms_version', "XRMS Version", "XRMS Version (read-only)", false, false, 'text', true);
    $ret=get_admin_preference($con, $xrms_version_type);
    if (!$ret) {
        set_admin_preference($con, $xrms_version_type, '1.0');
    }

    $s=_("Session Data Storage Type");
    $s =_( "Controls where the PHP session data is stored (files or database).  Changing this parameter will cause all currently logged in users to be logged out.");
    $s=_("Standard");
    $s=_("Database (XRMS)");
    $s=_("Database (ADOdb)");
    $session_storage_type=add_user_preference_type($con, 'session_storage_type', "Session Data Storage Type", "Controls where the PHP session data is stored (files or database).  Changing this parameter will cause all currently logged in users to be logged out.", false, false, 'select');
    add_preference_option($con, $session_storage_type, 'standard', 'Standard');
    add_preference_option($con, $session_storage_type, 'db', 'Database (XRMS)');
    add_preference_option($con, $session_storage_type, 'adodb-session', 'Database (ADOdb)');
    $ret=get_admin_preference($con, $session_storage_type);
    if (!$ret) {
        set_admin_preference($con, $session_storage_type, 'standard');
    }

    $pager_columns_data=get_user_preference_type($con, 'pager_columns');
    if ($pager_columns_data AND !$pager_columns_data['skip_system_edit_flag']) {
	$sql = "UPDATE user_preference_types SET skip_system_edit_flag=1 WHERE user_preference_type_id=".$pager_columns_data['user_preference_type_id'];
	$rst=$con->execute($sql);
	if (!$rst) db_error_handler($con, $sql);
    }

    $s=_("Undefined Company Method");
    $s=_("Insert method used when no Company is defined for a new Contact");
    $s=_("Use Unknown Company");
    $s=_("New Company uses Contact Name");
    $s=_("Reject Contact");

    $undefined_company_method=add_user_preference_type($con, 'undefined_company_method', "Undefined Company Method", "Insert method used when no Company is defined for a new Contact", false, false, 'select');
    add_preference_option($con, $undefined_company_method, 'unknown', 'Use Unknown Company');
    add_preference_option($con, $undefined_company_method, 'contact_name', 'New Company uses Contact Name');
    add_preference_option($con, $undefined_company_method, 'reject', 'Reject Contact');
    $ret=get_admin_preference($con, $undefined_company_method);
    if (!$ret) {
        set_admin_preference($con, $undefined_company_method, 'unknown');
    }



}

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
    user_preferences_db_data($con);
} // end create_db_data fn


/**
 * $Log: data.php,v $
 * Revision 1.42  2006/04/26 02:15:23  vanmer
 * - added system preference to control behavior of contacts API when adding a new contact with no company specified
 *
 * Revision 1.41  2006/03/16 23:37:29  vanmer
 * - ensure that user preference for pager columns does not show up in system preferences menu
 *
 * Revision 1.40  2005/12/08 05:23:37  vanmer
 * - added options for db handling of session data through XRMS or adodb to system preferences
 *
 * Revision 1.39  2005/12/06 22:39:22  vanmer
 * - removed system parameters from old install data
 *
 * Revision 1.38  2005/12/03 00:21:39  vanmer
 * - added system preference for storing user sessions in the database
 *
 * Revision 1.37  2005/11/30 00:43:12  vanmer
 * - added XRMS version option
 *
 * Revision 1.36  2005/10/16 19:51:37  maulani
 * - Add additional countries to table
 *
 * Revision 1.35  2005/10/06 04:30:06  vanmer
 * - updated log entries to reflect addition of code by Diego Ongaro at ETSZONE
 *
 * Revision 1.34  2005/10/04 23:21:44  vanmer
 * Patch to allow sort_order on the company CRM status field, thanks to Diego Ongaro at ETSZONE
 *
 * Revision 1.33  2005/09/29 14:37:18  vanmer
 * - requoted system activity properly
 *
 * Revision 1.32  2005/08/05 21:35:50  vanmer
 * - added translations for options text
 * - changed default search to contains instead of matches
 *
 * Revision 1.31  2005/08/05 21:32:07  vanmer
 * - added user preference to control search method used when searching company name (not entirely implemented
 * everywhere)
 *
 * Revision 1.30  2005/08/04 22:56:17  vanmer
 * - added function to install user preferences data upon initial install
 *
 * Revision 1.29  2005/07/28 20:29:10  vanmer
 * - added new activity resolution to list of standard activity resolutions
 *
 * Revision 1.28  2005/06/30 04:50:27  vanmer
 * - added standard types for activity resolution types
 *
 * Revision 1.27  2005/06/16 23:48:55  vanmer
 * - changed default activity types to install as non-user-editable
 *
 * Revision 1.26  2005/05/24 23:01:46  braverock
 * - add email_template_type table in advance of email template type support in core
 *
 * Revision 1.25  2005/05/23 01:55:33  maulani
 * - Add Use Owl system parameter
 *
 * Revision 1.24  2005/05/20 19:26:17  vanmer
 * - fixed error in insert of participant position record
 * - added Show Logo system parameter
 * - added user editable flag on activity types
 *
 * Revision 1.23  2005/05/18 06:19:57  vanmer
 * - removed references to role_id in users table
 * - removed roles table
 *
 * Revision 1.22  2005/04/15 07:46:47  vanmer
 * - ensure that database has proper default position
 *
 * Revision 1.21  2005/04/10 23:46:33  maulani
 * - Add address types
 *
 * Revision 1.20  2005/04/07 13:57:03  maulani
 * - Add salutation table to allow installation configurable list.  Also add
 *   many more default entries.
 *   RFE 913526 by algon.
 *
 * Revision 1.19  2005/03/20 16:56:23  maulani
 * - add new system parameters
 *
 * Revision 1.18  2005/02/05 16:44:18  maulani
 * - Change report options to use system parameters
 *
 * Revision 1.17  2005/01/30 12:52:01  maulani
 * - Add from email address to emailed reports
 *
 * Revision 1.16  2005/01/24 00:17:19  maulani
 * - Add description to system parameters
 *
 * Revision 1.15  2005/01/23 18:49:03  maulani
 * - Add system parameters required for RSS feeds
 *
 * Revision 1.14  2005/01/11 17:08:40  maulani
 * - Added parameter for LDAP Version.  Some LDAP Version 3 installations
 *   require this option to be set.  Initial parameter setting is version 2
 *   since most current installations probably use v2.
 *
 * Revision 1.13  2004/08/03 15:51:00  neildogg
 * - Added daylight savings and time zones tables and data for US
 *
 * Revision 1.12  2004/08/02 08:31:47  maulani
 * - Create Activities Default Behavior system parameter.  Replaces vars.php
 *   variable $activities_default_behavior
 *
 * Revision 1.11  2004/07/15 21:26:20  maulani
 * - Add Audit Level as a system parameter
 *
 * Revision 1.10  2004/07/13 18:15:59  neildogg
 * - Add database entries to allow a contact to be tied to the user
 *
 * Revision 1.9  2004/07/12 12:56:21  braverock
 * - add sort_order to activity_types table on install
 *   - resolves SF bug 987492 reported by kennyg1
 *
 * Revision 1.8  2004/07/07 20:48:16  neildogg
 * - Added database structure changes
 *
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