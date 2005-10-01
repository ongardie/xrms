<?php

  /**
   * Test harness for the Files Class
   *
   * Goals:
   *  - test property assignments
   *  - test "STATIC" method results
   *  - test "failure" senarios
   *
   * @package Files_Test
   *
   * @author Walter Torres <walter@torres.ws>
   *
   * @version   $Id: files_test.php,v 1.3 2005/10/01 08:21:38 vanmer Exp $
   * @date      $Date: 2005/10/01 08:21:38 $
   *
   * @copyright (c) 2004 Walter Torres
   * @license   Licensed under the GNU GPL. For full terms see the file COPYING.
   *            OSI Certified Open Source Software
   *
   * $Id: files_test.php,v 1.3 2005/10/01 08:21:38 vanmer Exp $
   *
   **/

if (!$include_directory) require_once('../../../../include-locations.inc');
require_once("PHPUnit.php");
require_once("PHPUnit/GUI/HTML.php");



require_once('files_test_class.php');

// ***********************************************************************
// ***********************************************************************

// $this->fail ( 'failed' );

// Define Test Classes

$StaticSuite          = new PHPUnit_TestSuite( "FilesStaticTest" );
$propertiesSuite      = new PHPUnit_TestSuite( "FilesPropertiesTest" );
$failuresSuite        = new PHPUnit_TestSuite( "FilesFailuresTest" );
$objectDisplaySuite   = new PHPUnit_TestSuite( "FilesObjectDisplay" );
$manipulationSuite    = new PHPUnit_TestSuite( "FilesManipulationTest" );


// Insert Suites into Test Harness
$display = new PHPUnit_GUI_HTML(array( $propertiesSuite,
                                       $StaticSuite,
                                       $failuresSuite,
                                       $objectDisplaySuite,
                                       $manipulationSuite ) );

// Display Test Harness
$display->show();

// =============================================================
// =============================================================
// ** CSV Version Control Info

 /**
  * $Log: files_test.php,v $
  * Revision 1.3  2005/10/01 08:21:38  vanmer
  * - Moved definition of tests into seperate file (files_test_class.php)
  *
  * Revision 1.2  2005/09/21 22:58:50  vanmer
  * - added include directory required file and global declaration for files.php and files_test.php
  *
  * Revision 1.1  2005/09/08 17:09:58  jswalter
  *  - initial commit
  *  - tests properties, static results and failures
  *  - tests File Manipulation methods
  *
  * Revision 1.3  2005/09/07 15:27:20  walter
  *  - updated Class property names
  *  - added some directory tests
  *  - added 'create_file()' method to make files for the file manipulation suite
  *  - added some file 'removal' code to the "File Manipulation" suite 'teardown()'
  *  - completed the File Manipulation Suite
  *
  * Revision 1.2  2005/09/01 15:42:39  walter
  *  - added new property '_overWriteFile' test
  *  - added new tests for file manipulation methods
  *
  * Revision 1.1  2005/08/31 23:30:34  walter
  *  - initial commit
  *  - tests properties, static results and failures
  *
  *
  */
?>