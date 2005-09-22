<?php

  /**
   * Test harness for the Files Upload Class
   *
   * @package Files_Test
   *
   * @author Walter Torres <walter@torres.ws>
   *
   * @version   $Id: upload_test.php,v 1.2 2005/09/22 03:17:05 jswalter Exp $
   * @date      $Date: 2005/09/22 03:17:05 $
   *
   * @copyright (c) 2004 Walter Torres
   * @license   Licensed under the GNU GPL. For full terms see the file COPYING.
   *            OSI Certified Open Source Software
   *
   * $Id: upload_test.php,v 1.2 2005/09/22 03:17:05 jswalter Exp $
   *
   **/


require_once("../file_upload.php");


// =============================================================


function getFileContents( $_filePath )
{
    $fh = fopen ($_filePath, 'r+'); //open the file and set the pointer to the beginning
    $_content = fread($fh, filesize($_filePath));
    fclose($fh);

    return $_content;
}

if ( $_POST )
{

// ************************************************************
    // Create new Class
    $objUpFile = new file_upload( 'testing' );

    if ( $objUpFile->getErrorCode() )
    {
        echo 'Could not create Upload Object: ';
        echo $objUpFile->getErrorMsg();
        exit;
    }

    // If there's one there already, over write it
    $objUpFile->setfileOverWrite ( true );

    // Where do we want this file sent to
    $objUpFile->setDestDir ( '/tmp' );

    if ( $objUpFile->getErrorCode() )
    {
        echo 'Could not set Upload Directory: ';
        echo $objUpFile->getErrorMsg();
        exit;
    }

    // Now process uploaded file
    $objUpFile->processUpload();

    if ( $objUpFile->getErrorCode() )
    {
        echo 'Could not process upload file: ';
        echo $objUpFile->getErrorMsg();
        exit;
    }

echo 'You sent: ' . $objUpFile->getFileFullPath();
echo '<p />';
echo 'And it contained:';
echo '<br />';
echo '<textarea cols="60" rows="10">' . getFileContents( $objUpFile->getFileFullPath() ) . '</textarea>';




// *************************************************************************

}

else
{

?>

Because of basic security browser security, File Upload can not perform standard PHPunit type tests.
<p />

Only manual testing is available.

<hr width="80%" align="center"/>
<p />

<form name="testing_form"
      id="testing_form"
      method="post"
      enctype="multipart/form-data"
      accept="multipart/form-data">

Select a File:
<input type="file" name="testing" id="testing" ? />
<p />

<input type="submit"  name="send" id="send" />


</form>

<?php
}

// =============================================================
// =============================================================
// ** CSV Version Control Info

 /**
  * $Log: upload_test.php,v $
  * Revision 1.2  2005/09/22 03:17:05  jswalter
  *  - removed debug include
  *
  * Revision 1.1  2005/09/22 03:14:34  jswalter
  *  - initial commit
  *  - simple upload file test
  *
  *
  */
?>