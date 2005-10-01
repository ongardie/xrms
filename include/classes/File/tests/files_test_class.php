<?php
     
     require_once($include_directory."classes/File/files.php");
     // ***********************************************************************
     // Test Class Properties Interface access
// This class simply assigns a given value to a desired property
// and then pulls the value directly from the Class properties.
Class File_Test
{
    function File_Test()
    {

    }

    function static__file_exists ( $_path = false )
    {
        return File::fileExists($_path);
    }

    function file_exist ( $_path = false )
    {
        return new File( $_path );
    }

    function static__file_readable ( $_path = false )
    {
        return File::fileReadable($_path);
    }

    function file_readable ( $_path = false )
    {
        return new File( $_path );
    }

    function static__file_writeable ( $_path = false )
    {
        return File::fileWriteable($_path);
    }

    function file_writeable ( $_path = false )
    {
        return new File( $_path );
    }

    function set_file_system_path ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->_fileSystemPath;
    }

    function get_file_system_path ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->getFileSystemPath();
    }

    function static__get_file_system_path ( $_path = false )
    {
        return File::getFileSystemPath($_path);
    }

    function get_file_full_path ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->getFileFullPath();
    }

    function set_file_name ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->_fileName;
    }

    function set_file_ext ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->_fileExt;
    }

    function get_file_name ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->getFileName();
    }

    function set_file_size ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->_fileSize;
    }

    function get_file_size ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->getFileSize();
    }

    function static__get_file_size ( $_path = false )
    {
        return File::getFileSize( $_path );
    }

    function set_file_mime ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->_fileMime;
    }

    function get_file_mime ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->getFileMimeType();
    }

    function static__get_file_mime ( $_path = false )
    {
        return File::getFileMimeType( $_path );
    }

    function set_file_permission ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->_filePerm;
    }

    function get_file_permission ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->_filePerm;
    }

    function static__get_file_permission ( $_path = false )
    {
        return File::getFilePerm( $_path );
    }

    function get_file_permission_octal ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->_filePerm;
    }

    function static__get_file_permission_octal ( $_path = false )
    {
        return File::getFilePermOctal( $_path );
    }

    function set_file_overwrite ( $_path = false, $_overwrite = false )
    {
        $objFiles = new File( $_path );

        $objFiles->setFileOverWrite($_overwrite);

        return $objFiles->_fileOverWrite;
    }

    function get_file_overwrite ( $_path = false, $_overwrite = false )
    {
        $objFiles = new File( $_path );

        $objFiles->setFileOverWrite($_overwrite);

        return $objFiles->getFileOverWrite();
    }

    function set_dest_dir ( $_path = false, $_newDir = false )
    {
        $objFiles = new File( $_path );

        $objFiles->setDestDir($_newDir);

        return $objFiles->_dirDestPath;
    }

    function get_dest_dir ( $_path = false, $_newDir = false )
    {
        $objFiles = new File( $_path );

        $objFiles->setDestDir($_newDir);

        return $objFiles->getDestDir();
    }

    function file_delete ( $_path = false )
    {
        $objFiles = new File( $_path );

        return $objFiles->fileDelete();
    }

    function static__file_delete ( $_path = false )
    {
        return File::fileDelete( $_path );
    }

    function file_copy ( $_path = false, $_newFile = false, $_overwrite = false )
    {
        $objFiles = new File( $_path );
        $objFiles->setFileOverWrite ( $_overwrite );

        return $objFiles->fileCopy($_newFile);
    }

    function static__file_copy ( $_path = false, $_newFile = false, $_overwrite = false )
    {
        return File::fileCopy( $_path, $_newFile, $_overwrite );
    }

    function file_rename ( $_path = false, $_newFile = false, $_overwrite = false )
    {
        $objFiles = new File( $_path );
        $objFiles->setFileOverWrite ( $_overwrite );

        return $objFiles->fileRename($_newFile);
    }

    function static__file_rename ( $_path = false, $_newFile = false, $_overwrite = false )
    {
        return File::fileRename( $_path, $_newFile, $_overwrite );
    }

    function file_move ( $_path = false, $_newPath = false, $_overwrite = false )
    {
        $objFiles = new File( $_path );
        $objFiles->setFileOverWrite ( $_overwrite );

        return $objFiles->fileMove($_newPath, $_overwrite);
    }

    function static__file_move ( $_path = false, $_newPath = false, $_overwrite = false )
    {
        return File::fileMove( $_path, $_newPath, $_overwrite );
    }

    function file_create ( $_path = false )
    {
        $fp = fopen($_path, 'wb');
        fputs ($fp, 'auto generated file');
        fclose($fp);
    }
};


// ***********************************************************************
// Test Class Static Methods

Class FilesStaticTest extends PHPUnit_TestCase
{
    function FilesStaticTest( $name = "FilesStaticTest" )
    {
        $this->PHPUnit_TestCase( $name );
    }

    function setUp()
    {
        global $include_directory;
        include ( $include_directory.'classes/File/tests/file_test_config.php' );

        $this->Files = new File();
        $this->FilesTest = new File_Test();
    }

    function teardown()
    {

    }

    function test_static__file_exists ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_write;

        $result = $this->FilesTest->static__file_exists($_path);

        $this->assertTrue($result, "File Exists: " );

        return $result;
    }

    function test_static__get_file_system_path__from_full_path ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->static__get_file_system_path($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->test_path );

        $this->assertTrue($result, "File Exists: " );

        return $result;
    }

    function test_static__get_file_system_path__from_relative_path ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_relative_path;

        $result = $this->FilesTest->static__get_file_system_path($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->test_path );

        $this->assertTrue($result, "File Exists: " );

        return $result;
    }

    function test_static__file_readable ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->file_readable($_path);

        $this->assertTrue($result, "File Readable: " );

        return $result;
    }

    function test_static__file_writeable ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_write;

        $result = $this->FilesTest->file_writeable($_path);

        $this->assertTrue($result, "File Writeable: " );

        return $result;
    }

    function test_static__get_file_size ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->static__get_file_size($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_size );

        $this->assertTrue($result, "File Size: " );

        return $result;
    }

    function test_static__get_file_mime ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->static__get_file_mime($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_mime );

        $this->assertTrue($result, "File Mime Type: " );

        return $result;
    }

    function test_static__get_file_permission ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_write;

        $result = $this->FilesTest->static__get_file_permission($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_perm );

        $this->assertTrue($result, "Get File Permissions" );

        return $result;
    }

    function test_static__get_file_permission_octal ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->static__get_file_permission_octal($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_octal );

        $this->assertTrue($result, "Get File Permissions [Octal]" );

        return $result;
    }

};

// ***********************************************************************
// Test Class Properties

Class FilesPropertiesTest extends PHPUnit_TestCase
{
    function FilesPropertiesTest( $name = "FilesPropertiesTest" )
    {
        $this->PHPUnit_TestCase( $name );
    }

    function setUp()
    {
        global $include_directory;
        include ( $include_directory.'classes/File/tests/file_test_config.php' );

        $this->Files = new File();
        $this->FilesTest = new File_Test();
    }

    function teardown()
    {

    }

    function test_file_exist ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->file_exist($_path);
        $result = $result->_fileSuccess;

        $this->assertTrue($result, "File Exist: '" . $_path . "'");

        return $result;
    }

    function test_file_readable ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->file_readable($_path);
        $result = $result->_fileReadable;

        $this->assertTrue($result, "File Readable" );

        return $result;
    }

    function test_file_writeable ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_write;

        $result = $this->FilesTest->file_writeable($_path);
        $result = $result->_fileWriteable;

        $this->assertTrue($result, "File Writeable" );

        return $result;
    }

    function test_set_file_system_path__from_full ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        // Pull path property
        $result = $this->FilesTest->set_file_system_path($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->test_path );

        $this->assertTrue($result, "Set File Path" );

        return $result;
    }


    function test_set_file_system_path__from_relative ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_relative_path;

        // Pull path property
        $result = $this->FilesTest->set_file_system_path($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->test_path );

        $this->assertTrue($result, "Set File Path" );

        return $result;
    }

    function test_get_file_full_path__from_full ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        // Pull path property
        $result = $this->FilesTest->get_file_full_path($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_full_path );

        $this->assertTrue($result, "Set File Path" );

        return $result;
    }

    function test_get_file_full_path__from_relative ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_relative_path;

        // Pull path property
        $result = $this->FilesTest->get_file_full_path($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_full_path );

        $this->assertTrue($result, "Set File Path" );

        return $result;
    }

    function test_set_file_name ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->set_file_name($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_name );

        $this->assertTrue($result, "Set File Name" );

        return $result;
    }

    function test_set_file_ext ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        // Pull path property
        $result = $this->FilesTest->set_file_ext($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_ext );

        $this->assertTrue($result, "Set File Name" );

        return $result;
    }

    function test_get_file_name ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->get_file_name($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_name );

        $this->assertTrue($result, "Get File Name" );

        return $result;
    }

    function test_set_file_size ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        // Pull path property
        $result = $this->FilesTest->set_file_size($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_size );

        $this->assertTrue($result, "Set File Size");

        return $result;
    }

    function test_get_file_size ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->get_file_size($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_size );

        $this->assertTrue($result, "Set File Size" );

        return $result;
    }

    function test_set_file_mime ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        // Pull path property
        $result = $this->FilesTest->set_file_mime($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_mime );

        $this->assertTrue($result, "Set File Mime" );

        return $result;
    }

    function test_get_file_mime ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        $result = $this->FilesTest->get_file_mime($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_mime );

        $this->assertTrue($result, "Get File Mime") ;

        return $result;
    }

    function test_set_file_permission ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_write;

        $result = $this->FilesTest->set_file_permission($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->file_perm );

        $this->assertTrue($result, "Set File Permissions" );

        return $result;
    }

    function test_set_file_overwrite ( $_path = false, $_overwrite = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        if ( $_overwrite === false )
            $_overwrite = $this->do_overwrite ;

        $result = $this->FilesTest->set_file_overwrite($_path, $_overwrite);

        // Compare it to what we think it should be
        $result = ( $result == $this->do_overwrite );

        $this->assertTrue($result, "Set File Overwrite" );

        return $result;
    }

    function test_get_file_overwrite ( $_path = false, $_overwrite = false )
    {
        if ( $_path === false )
            $_path = $this->file_read_only;

        if ( $_overwrite === false )
            $_overwrite = $this->do_overwrite ;

        $result = $this->FilesTest->get_file_overwrite($_path, $_overwrite);

        // Compare it to what we think it should be
        $result = ( $result == $this->do_overwrite );

        $this->assertTrue($result, "Get File Overwrite" );

        return $result;
    }

    function test_set_dest_directory ( $_path = false, $_newPath = false )
    {
        if ( $_path === false )
            $_path = $this->file_name;

        if ( $_newPath === false )
            $_newPath = $this->test_path;

        $result = $this->FilesTest->set_dest_dir($_path, $_newPath);

        // Compare it to what we think it should be
        $result = ( $result == $this->test_path );

        $this->assertTrue($result, "Set Destination Directory" );

        return $result;
    }

    function test_get_dest_directory ( $_path = false, $_newPath = false )
    {
        if ( $_path === false )
            $_path = $this->file_name;

        if ( $_newPath === false )
            $_newPath = $this->test_path;

        $result = $this->FilesTest->get_dest_dir($_path, $_newPath);

        // Compare it to what we think it should be
        $result = ( $result == $this->test_path );

        $this->assertTrue($result, "Set Destination Directory" );

        return $result;
    }

};


// ***********************************************************************
// Test Class Method Properties

Class FilesFailuresTest extends PHPUnit_TestCase
{
    function FilesFailuresTest( $name = "FilesFailuresTest" )
    {
        $this->PHPUnit_TestCase( $name );
    }

    function setUp()
    {
        global $include_directory;
        include ( $include_directory.'classes/File/tests/file_test_config.php' );

        $this->Files = new File();
        $this->FilesTest = new File_Test();
    }

    function test_file_exists__invalid_parameters ( $_path = false )
    {
        if ( $_path === false )
            $_path = '';

        $result = $this->FilesTest->file_exist($_path);
        $result = ( $result->_errCode == INVALID_PARAMETERS );

        $this->assertTrue($result, "File Exist: '" . $_path . "'");

        return $result;
    }

    function test_file_exists__file_not_found ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->path_bad;

        $result = $this->FilesTest->file_exist($_path);
        $result = ( $result->_errCode == FILE_NOT_FOUND );

        $this->assertTrue($result, "File Exists: '" . $_path . "'");

        return $result;
    }

    function test_static__file_exists__file_not_found ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->path_bad;

        $result = $this->FilesTest->static__file_exists($_path);

        $this->assertFalse($result, "File Exists: '" . $_path . "'");

        return $result;
    }

    function test_static__get_file_system_path__bad_path ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->path_bad;

        $result = $this->FilesTest->get_file_system_path($_path);

        // Compare it to what we think it should be
        $result = ( $result == $this->path_bad_system_path );

        $this->assertTrue($result, "File System Path");

        return $result;
    }

    function test_static__get_file_system_path__no_path ( $_path = false )
    {
        if ( $_path === false )
            $_path = '';

        $result = $this->FilesTest->get_file_system_path($_path);

        // Compare it to what we think it should be
        $result = ( $result == null );

        $this->assertTrue($result, "File System Path");

        return $result;
    }

};



// ***********************************************************************
// Test Class Method Properties

Class FilesObjectDisplay extends PHPUnit_TestCase
{
    function FilesFailuresTest( $name = "FilesObjectDisplay" )
    {
        $this->PHPUnit_TestCase( $name );
    }

    function setUp()
    {
        global $include_directory;
        include ( $include_directory.'classes/File/tests/file_test_config.php' );

        $this->objFiles = new File( $this->file_read_write );
    }

    function test_file_display_object__full_path ()
    {
        $_objFiles = new File( $this->file_read_only );
        $_strErr   = $_objFiles->getErrorMsg();

        // Don't need to see all the error codes
        unset ( $_objFiles->_file_error_codes );

        $_strClass = print_r ( $_objFiles, true );
        $_strClass .= "\r\n";
        $_strClass .= $_strErr;

        $this->fail ( $_strClass );
    }

    function test_file_display_object__relative_path ()
    {
        $_objFiles = new File( $this->file_relative_path );
        $_strErr   = $_objFiles->getErrorMsg();

        // Don't need to see all the error codes
        unset ( $_objFiles->_file_error_codes );

        $_strClass = print_r ( $_objFiles, true );
        $_strClass .= "\r\n";
        $_strClass .= $_strErr;

        $this->fail ( $_strClass );
    }

    function test_file_display_object__bad_path ()
    {
        $_objFiles = new File( $this->path_bad );
        $_strErr   = $_objFiles->getErrorMsg();

        // Don't need to see all the error codes
        unset ( $_objFiles->_file_error_codes );

        $_strClass = print_r ( $_objFiles, true );
        $_strClass .= "\r\n";
        $_strClass .= $_strErr;

        $this->fail ( $_strClass );
    }

    function test_file_display_object__no_path ()
    {
        $_objFiles = new File( '' );
        $_strErr   = $_objFiles->getErrorMsg();

        // Don't need to see all the error codes
        unset ( $_objFiles->_file_error_codes );

        $_strClass = print_r ( $_objFiles, true );
        $_strClass .= "\r\n";
        $_strClass .= $_strErr;

        $this->fail ( $_strClass );
    }

};

// ***********************************************************************
// Test Class File Manipulation Methods

Class FilesManipulationTest extends PHPUnit_TestCase
{
    function FilesPropertiesTest( $name = "FilesManipulationTest" )
    {
        $this->PHPUnit_TestCase( $name );
    }

    function setUp()
    {
        global $include_directory;
        include ( $include_directory.'classes/File/tests/file_test_config.php' );


        $this->Files = new File();
        $this->FilesTest = new File_Test();
    }

    function teardown()
    {
        // Remove the "copied" file
        @unlink ( $this->file_to_copy_to );

        // Remove the "renamed" file
        @unlink ( $this->test_path . $this->file_to_rename_to );

        // Remove the "moved" file
        @unlink ( $this->test_path . $this->file_to_move_to );
    }

    function test__file_delete ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_to_delete;

        // Create a file to delete
        $this->FilesTest->file_create($_path);

        $result = $this->FilesTest->file_delete($_path);

        $result = ( $result === true );

        $this->assertTrue($result, "File Delete" );

        return $result;
    }

    function test_static__file_delete ( $_path = false )
    {
        if ( $_path === false )
            $_path = $this->file_to_delete;

        // Create a file to delete
        $this->FilesTest->file_create($_path);

        $result = $this->FilesTest->static__file_delete($_path);

        $result = ( $result === true );

        $this->assertTrue($result, "File Delete" );

        return $result;
    }

    function test__file_copy ( $_path = false, $_newFile = false )
    {
        if ( $_path === false )
            $_path = $this->file_to_copy;

        if ( $_newFile === false )
            $_newFile = $this->file_to_copy_to;

        // Create a file to copy
        $this->FilesTest->file_create($_path);

        $result = $this->FilesTest->file_copy($_path, $_newFile, true);

        $result = ( $result === true );

        $this->assertTrue($result, "File Copy" );

        return $result;
    }

    function test_static__file_copy ( $_path = false, $_newFile = false )
    {
        if ( $_path === false )
            $_path = $this->file_to_copy;

        if ( $_newFile === false )
            $_newFile = $this->file_to_copy_to;

        // Create a file to copy
        $this->FilesTest->file_create($_path);

        $result = $this->FilesTest->static__file_copy($_path, $_newFile, true );

        $result = ( $result === true );

        $this->assertTrue($result, "File Copy" );

        return $result;
    }

    function test__file_rename ( $_path = false, $_newFile = false )
    {
        if ( $_path === false )
            $_path = $this->file_to_rename;

        if ( $_newFile === false )
            $_newFile = $this->file_to_rename_to;

        // Create a file to rename
        $this->FilesTest->file_create($_path);

        $result = $this->FilesTest->file_rename($_path, $_newFile, true);

        $result = ( $result === true );

        $this->assertTrue($result, "File Rename" );

        return $result;
    }

    function test_static__file_rename ( $_path = false, $_newFile = false )
    {
        if ( $_path === false )
            $_path = $this->file_to_rename;

        if ( $_newFile === false )
            $_newFile = $this->file_to_rename_to;

        // Create a file to rename
        $this->FilesTest->file_create($_path);

        $result = $this->FilesTest->static__file_rename($_path, $_newFile, true);

        $result = ( $result === true );

        $this->assertTrue($result, "File Rename" );

        return $result;
    }

    function test__file_move ( $_path = false, $_newPath = false )
    {
        if ( $_path === false )
            $_path = $this->file_to_move;

        if ( $_newPath === false )
            $_newPath = $this->file_to_move_to;

        // Create a file to move
        $this->FilesTest->file_create($_path);

        $result = $this->FilesTest->file_move($_path, $_newPath, true);

        $result = ( $result === true );

        $this->assertTrue($result, "File Move" );

        return $result;
    }

    function test_static__file_move ( $_path = false, $_newPath = false )
    {
        if ( $_path === false )
            $_path = $this->file_to_move;

        if ( $_newPath === false )
            $_newPath = $this->file_to_move_to;

        // Create a file to move
        $this->FilesTest->file_create($_path);

        $result = $this->FilesTest->static__file_move($_path, $_newPath, true);

        $result = ( $result === true );

        $this->assertTrue($result, "File Move" );

        return $result;
    }

};
?>