Included with the custom_fields plugin is a script to import data from the
'info' plugin. THIS SCRIPT IS NOT OFFICIALLY SUPPORTED: USE AT YOUR OWN
RISK.

Steps to import data from the 'info' plugin:

1. Carry out a full backup of your XRMS database.
2. Create the custom_fields database tables (see the custom_fields plugin
documentation).
3. Ensure that you are the only person accessing the XRMS database.
4. In a browser, navigate to plugins/custom_fields/admin/info_to_cf.php
under your XRMS root URL.
5. The script will run and output some information messages.
6. Navigate to your root XRMS URL, then disable the 'info' plugin and
enable the 'custom_fields' plugin as described in the custom_fields plugin
documentation.
7. Depending on the version of the 'info' plugin that was used to create
the info fields, there may be some spurious 'Name' fields in non-sidebar
displays. These can be cleaned up from the custom_fields administration
screens.
