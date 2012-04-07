<?php

/**
 * CFileTest provides with TestCases for testing CFile provided by idle sign:
 * 
 * Following methods are currently tested, and more are to follow
 * 1) set
 * 2) create
 * 3) createDir
 * 4) purge
 * 5) delete
 * 6) copy
 * 
 * @todo Add test for following methods
 *
 * 7) move/rename
 * 8) download/send
 * 
 * This file must be run using Yii-Framework as it extends CTestCase.
 *
 * @author Danish Satkut <danish_satkut@hotmail.com>
 */
class CFileTest extends CTestCase {
    protected $fileObject;
    protected $folderObject;
    private static $BASE_PATH = 'C:\xampp\htdocs\yii-sandbox\relations-tests';
    
    protected function setUp() {
        $this->fileObject = null;
        $this->folderObject = null;
    }
    
    /**
     * Test for valid CFile instance
     */
    public function testSet_ValidFile_ReturnsCFileInstance() {
        $file = CFile::set(self::$BASE_PATH . "\files\hello.txt");
        $this->assertInstanceOf('CFile', $file);
    }
    
    /**
     * Test for blank path argument to CFile:set()
     */
    public function testSet_BlankPathArgument_ThrowsException() {
        try {
            $this->fileObject = CFile::set('');
        } catch(CFileException $e) {
            $this->assertInstanceOf('CFileException', $e);
        }
    }
    
    /**
     * Test create method for success 
     */
    public function testCreate_NewFileCreated_ReturnsCFileInstance() {
        $this->fileObject = CFile::set(self::$BASE_PATH . '\files\new-test.txt');
        $this->assertFalse($this->fileObject->exists);
        
        $file = $this->fileObject->create();
        $this->assertInstanceOf('CFile', $file);
        $this->assertTrue($this->fileObject->exists);
    }
    
    /**
     * Test create method for failure 
     */
    public function testCreate_FileAlreadyExists_ReturnsFalse() {
        $fileAlreadyExists = CFile::set(self::$BASE_PATH . '\files\hello.txt');
        $this->assertTrue($fileAlreadyExists->exists);
        
        $file = $fileAlreadyExists->create();
        $this->assertFalse($file);
    }
    
    /**
     * Test createDir method for success 
     */
    public function testCreateDir_NewDirCreatedWithPath_ReturnsTrue() {
        $folder = CFile::set(self::$BASE_PATH . '\files');
        $this->assertTrue($folder->exists);
        // Returns true
        $this->folderObject = $folder->createDir(0777, $folder->realpath . '/test-dir');
        $this->assertTrue($this->folderObject);
        
        $this->folderObject = CFile::set(self::$BASE_PATH . '\files\test-dir');
        $this->assertTrue($this->folderObject->isDir);
    }
    
    /**
     * Test createDir method for success  
     */
    public function testCreateDir_NewDirCreated_ReturnsCFileInstance() {
        $folder = CFile::set(self::$BASE_PATH . '\files\new-test-dir');
        $this->assertFalse($folder->exists);
        
        $this->folderObject = $folder->createDir(0777);
        $this->assertInstanceOf('CFile', $this->folderObject);
    }
    
    /**
     * Test purge method for success 
     */
    public function testPurge_PurgingFolder_ReturnsTrue() {
        $this->folderObject = CFile::set(self::$BASE_PATH . '\files\to-be-purged')->createDir();
        $file = CFile::set($this->folderObject->realPath . '\to-be-purged.txt');
        $file->create();
        
        $this->assertTrue($this->folderObject->exists);
        $this->assertTrue($this->folderObject->purge());
        $this->assertFalse($this->folderObject->contents);
    }
    
    /**
     * Test for method purge on files.
     */
    public function testPurge_PurgingFile_ReturnsTrue() {
        $this->fileObject = CFile::set(self::$BASE_PATH . '\files\to-be-purged.txt')->create();
        $this->fileObject->contents = 'This data is going to be purged.';
        
        $this->assertTrue($this->fileObject->exists);
        $this->assertTrue($this->fileObject->purge());
        $this->assertFalse($this->fileObject->contents);
    }
    
    /**
     * Test for method delete on file. 
     */
    public function testDelete_File_ReturnsTrue() {
        $this->fileObject = CFile::set(self::$BASE_PATH . '\files\to-be-deleted.txt')->create();
        
        $this->assertTrue($this->fileObject->exists);
        $this->assertTrue($this->fileObject->delete());
        $this->assertFalse($this->fileObject->exists);
    }
    
    /**
     * Test for method delete on non-empty folder with purge. 
     */
    public function testDelete_NonEmptyFolderWithPurge_ReturnsTrue() {
        $this->folderObject = CFile::set(self::$BASE_PATH . '\files\to-be-deleted')->createDir();
        
        $this->assertTrue($this->folderObject->exists);
        $this->assertTrue($this->folderObject->delete(true));
        $this->assertFalse($this->folderObject->exists);
    }
    
    /**
     * Test for method copy on file 
     */
    public function testCopy_ValidFileDestination_ReturnsCFileInstance() {
        $this->fileObject = CFile::set(self::$BASE_PATH . '\files\valid-to-be-copied.txt')->create();
        $this->assertTrue($this->fileObject->exists, 'Base file creation failed.');
        
        // Destination path is real path
        $this->folderObject = CFile::set(self::$BASE_PATH . '\files\new-dir')->createDir();
        $this->assertTrue($this->folderObject->isDir, 'New directory creation failed.');
        
        $copiedFile = $this->fileObject->copy(self::$BASE_PATH . '\files\new-dir\copied-file.txt');
        $this->assertTrue($copiedFile->exists, 'Copied file does not exists.');
        $this->assertTrue($copiedFile->isFile, 'Copied file is not a file.');
        
        // Destination path is absolute for current directory
        $copiedFile = $this->fileObject->copy(self::$BASE_PATH . 'copied-file.txt');
        $this->assertTrue($copiedFile->exists, 'Copied file does not exists.');
        $this->assertTrue($copiedFile->isFile, 'Copied file is not a file.');
        
        $copiedFile->exists !== null ? $copiedFile->delete() : null;        
    }
    
    /**
     * Test for method copy on file 
     */
    public function testCopy_InvalidFileDestination_ReturnsFalse() {
        $this->fileObject = CFile::set(self::$BASE_PATH . '\files\invalid-to-be-copied.txt')->create();
        $this->assertTrue($this->fileObject->exists, 'Base file creation failed.');
        
        $copiedFile = $this->fileObject->copy(self::$BASE_PATH . '\files');
        $this->assertFalse($copiedFile);
        
        $copiedFile = $this->fileObject->copy(self::$BASE_PATH . '');
        $this->assertFalse($copiedFile);
        
        $copiedFile->exists !== null ? $copiedFile->delete() : null;
    }
    
    /**
     * Test for method copy on folder 
     */
    public function testCopy_ValidFolderDestination_ReturnsCFileInstance() {
        $folder = CFile::set(self::$BASE_PATH . '\files');
        $this->assertTrue($folder->exists, 'Base folder selection failed.');
        $this->folderObject = CFile::set(self::$BASE_PATH . '\files\copied-folder')->createDir();
        $this->assertInstanceOf('CFile', $this->folderObject);
        
        $copiedFolder = $folder->copy(self::$BASE_PATH . '\files\copied-folder');
        $this->assertTrue($copiedFolder->exists, 'Copied folder does not exists.');
        $this->assertTrue($copiedFolder->isDir, 'Copied folder is not a directory.');
        
        $copiedFolder->exists !== null ? $copiedFolder->delete() : null;
    }
    
    /**
     * Test for method copy on file 
     */
    public function testCopy_InvalidFolderDestination_ReturnsFalse() {
        $this->folderObject = CFile::set(self::$BASE_PATH . '\files\invalid-to-be-copied')->create();
        $this->assertTrue($this->folderObject->exists, 'Base folder creation failed.');
        
        $copiedFolder = $this->folderObject->copy(self::$BASE_PATH . '\files\not-created-yet\destination-dir');
        $this->assertFalse($copiedFolder);
        
        $copiedFolder = $this->folderObject->copy(self::$BASE_PATH . '');
        $this->assertFalse($copiedFolder);
        
        $copiedFolder->exists !== null ? $copiedFolder->delete() : null;
    }
    
    
    
    protected function tearDown() {
        ($this->fileObject !== null && $this->fileObject->exists) ? $this->fileObject->delete() : null;
        ($this->folderObject !== null && $this->folderObject->exists) ? $this->folderObject->delete() : null;
        
        unset($this->fileObject);
        unset($this->folderObject);
    }
}

?>
