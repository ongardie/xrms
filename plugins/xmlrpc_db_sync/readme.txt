XMLRPC Foreign Database Synchronisation Tool.

This tool is used for one way synchronisation (towards XRMS) with foreign databases or applications.
It requires high level of technical knowledge to implement correctly. 

It is an implementation of Keith Devens XMLRPC tool for php.

This plugin allows other applications to remotely call certain XRMS functions that are useful for synchronising different databases.

For example, lets say you had another application called the "CLIENT" that has your company and contact records in it. You wish to make sure that XRMS (the "SERVER") has the same information, and that when you update your data in CLIENT, you want it also to be updated in the SERVER. Your CLIENT might be an accounting database, for example.

You can use these functions available in the xrms_api.php file to do this by calling them directly from your CLIENT application, providing you are able to modify the CLIENT codebase.

To ensure security, there are certain prohibited sql strings that cannot be passed. This is to protect remote access by hackers who attempt to corrupt or modify your database.

The benefits of using XMLRPC mean that if your CLIENT application is in:

JAVA
VB
VB.NET
PHP
PYTHON
or many other languages supported by XMLRPC

the application can still talk to XRMS using the XMLRPC protocol.

See www.xmlrpc.com for more information on this protocol.

The functions you can call using this plugin are as follows (they are self explanatory):

xrms_export -> can export a list of data from any table
xrms_sql -> allows you to flexibly pass many sqls direct to the XRMS database
xrms_find_contact
xrms_find_company
xrms_add_update_company
xrms_add_update_contact
xrms_find_address
xrms_add_update_address

See client_example.php in this directory for a very simple example.

Nic Lowe
www.cham-e-leon.com
