<?php

  /**
   * Configuration file for the File Class Test harness
   *
   * @package Files_Test
   *
   * @author Walter Torres <walter@torres.ws>
   *
   * @version   $Id: file_test_config.php,v 1.2 2005/10/01 08:23:48 vanmer Exp $
   * @date      $Date: 2005/10/01 08:23:48 $
   *
   * @copyright (c) 2004 Walter Torres
   * @license   Licensed under the GNU GPL. For full terms see the file COPYING.
   *            OSI Certified Open Source Software
   *
   * $Id: file_test_config.php,v 1.2 2005/10/01 08:23:48 vanmer Exp $
   *
   **/

    // Modify this path so this Test Suite can locate the test files.
    // And don't forget to place valid data in this Class INI file.
    $this->test_path = dirname(__FILE__) .'/testing/';


    // DO NOT MODIFY BELOW THIS LINE
    // =============================================================

    // Directory information
    $this->dir_read_write     = $this->test_path . 'read_write_dir';
    $this->dir_read_only      = $this->test_path . 'read_only_dir';
    $this->dir_read_denied    = $this->test_path . 'read_denied_dir';

    // File information
    $this->file_read_write     = $this->test_path . 'read_write_file.txt';
    $this->file_read_only      = $this->test_path . 'read_only_file.txt';
    $this->file_read_denied    = $this->test_path . 'read_denied_file.txt';
    $this->file_name           = 'read_only_file.txt';
    $this->file_full_path      = $this->test_path . $this->file_name;

    if (basename($_SERVER['PHP_SELF'])=='files_test.php')
        $this->file_relative_path  = './testing/' . $this->file_name;
    if (basename($_SERVER['PHP_SELF'])=='xrms_test.php')
        $this->file_relative_path  = '../include/classes/File/tests/testing/' . $this->file_name;
    $this->file_size           = 102;
    $this->file_ext            = 'txt';
    $this->file_mime           = 'text/plain';
    $this->file_perm           = '-rw-r--r--';
    $this->file_octal          = '0644';

    $this->do_overwrite        = true;
    $this->dont_overwrite      = false;

    // Failure data
    $this->path_bad             = '/this/is/an/bad/path/file.txt';
    $this->path_bad_system_path = '/';

    $this->path_invalid         = '/th!s/!$/@n/invalid#/p@th/file.txt';

    // File Manipulation data
    $this->file_to_delete       = $this->test_path . 'delete_this_file.txt';

    $this->file_to_copy         = $this->test_path . 'copy_this_file.txt';
    $this->file_to_copy_to      = $this->test_path . 'copy_to_this_file.txt';

    $this->file_to_rename       = $this->test_path . 'rename_this_file.txt';
    $this->file_to_rename_to    = 'rename_to_this_file.txt';

    $this->file_to_move         = $this->test_path . 'move_this_file.txt';
    $this->file_to_move_to      = 'move_to_this_file.txt';

// =============================================================
// =============================================================
// ** CSV Version Control Info

 /**
  * $Log: file_test_config.php,v $
  * Revision 1.2  2005/10/01 08:23:48  vanmer
  * - changed to arrange relative paths based on where the tests are called from
  * - changed permissions to reflect the actual permissions on the test files
  *
  * Revision 1.1  2005/09/08 17:08:28  jswalter
  * - initial commit
  * - defined various test data info
  *
  *
  *
  */

?>