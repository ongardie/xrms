# If you are upgrading, use the 'upgrade.sql script instead!

# 1) cd to the directory with the XRMS *.sql files in it
# 2) start mysql shell with 'mysql -u root -p'
# 3) enter: "source xrms-init-all.sql" (without quotation marks)

# PLEASE BE AWARE THAT THIS SCRIPT COMPLETELY
# REMOVES ANY PREVIOUS INSTANCIATED XRMS DATABASE!!!!
# If you are upgrading, use the 'upgrade.sql script instead!

DROP DATABASE xrms;

CREATE DATABASE xrms;
use xrms;
SOURCE 1-misc.sql;
SOURCE 2-users.sql;
SOURCE 3-companies.sql;
SOURCE 4-opportunities.sql;
SOURCE 5-cases.sql;
SOURCE 6-campaigns.sql;
SOURCE 7-activities.sql;