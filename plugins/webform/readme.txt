Integrated Contact Us form for your website.
# $Id: readme.txt,v 1.3 2007/06/13 18:09:22 niclowe Exp $

What this plugin does/can do

1. It integrates XRMS into a 'contact us' page on your website
2. It creates activites for the actual email
3. It can create an opportunity based on the contents of the form (todo)

How to modify what it does

1. Modify the contact.php form to be appropriate to your environment
2. Set defaults in vars_webform.inc to match your environment
        crm_status_id
        industry_id
        last_modified_by
        country_id
        etc

3. Examine the variables in new-form.php to get an idea of what you can customise on it.
4. Make sure you create an automated support user and set $session_user_id in new-form.php
5. Modify new-form.php to do things appropriate for your environment.

Ta

Nic Lowe
Www.newtowncarshare.info

# $Log: readme.txt,v $
# Revision 1.3  2007/06/13 18:09:22  niclowe
# First line is file description now for new plugin documentation
#
# Revision 1.2  2006/10/05 11:23:22  braverock
# - move all vars to vars_webform.inc
# - make index a simple redirect to avoid header errors
# - improve documentation and comments
# - set $session_user_id directly to avoid potential security problem
#
# Revision 1.1  2004/06/26 14:39:30  braverock
# - Initial Revision of WebForm Plugin by Nic Lowe
#   - added phpdoc
#   - standardized on long php tags
#