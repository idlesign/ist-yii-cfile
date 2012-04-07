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
        
    }
    
    /**
     * Test for valid CFile instance
     */
    public function testSet_ValidFile_ReturnsCFileInstance() {
        $this->fileObject = CFile::set(self::$BASE_PATH . "\files\hello.txt");
        $this->assertInstanceOf('CFile', $this->fileObject);
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
        
        $this->fileObject->delete();
    }
    
    /**
     * Test create method for failure 
     */
    public function testCreate_FileAlreadyExists_ReturnsFalse() {
        $this->fileObject = CFile::set(self::$BASE_PATH . '\files\hello.txt');
        $this->assertTrue($this->fileObject->exists);
        
        $file = $this->fileObject->create();
        $this->assertFalse($file);
    }
    
    /**
     * Test createDir method for success 
     */
    public function testCreateDir_NewDirCreatedWithPath_ReturnsTrue() {
        $folder = CFile::set(self::$BASE_PATH . '\files');
        $this->assertTrue($folder->exists);
        
        $this->folderObject = $folder->createDir(0777, $folder->realpath . '/test-dir');
        $this->assertTrue($this->folderObject);
        
        CFile::set(self::$BASE_PATH . '\files\test-dir')->delete();
    }
    
    /**
     * Test createDir method for success  
     */
    public function testCreateDir_NewDirCreated_ReturnsCFileInstance() {
        $folder = CFile::set(self::$BASE_PATH . '\files\new-test-dir');
        $this->assertFalse($folder->exists);
        
        $this->folderObject = $folder->createDir(0777);
        $this->assertInstanceOf('CFile', $this->folderObject);
        
        $this->folderObject->delete();
    }
    
    /**
     * Test purge method for success 
     */
    public function testPurge_PurgingFolder_ReturnsTrue() {
        $this->folderObject = CFile::set(self::$BASE_PATH . '\files\to-be-purged')->createDir();
        $folder = CFile::set($this->folderObject->realpath . '\to-be-purged')->createDir();
        $file = CFile::set($this->folderObject->realPath . '\to-be-purged.txt');
        $file->create();
        
        $this->assertTrue($this->folderObject->exists);
        $this->assertTrue($this->folderObject->purge());
        $this->assertFalse($this->folderObject->contents);
        
        $this->folderObject->delete();
        
    }
    
    public function testPurge_PurgingFile_ReturnsTrue() {
        $this->fileObject = CFile::set(self::$BASE_PATH . '\files\to-be-purged.txt')->create();
        $this->fileObject->contents = 'This data is going to be purged.';
        
        $this->assertTrue($this->fileObject->exists);
        $this->assertTrue($this->fileObject->purge());
        $this->assertFalse($this->fileObject->contents);
        
        $this->fileObject->delete();
    }
    
    public function testDelete_File_ReturnsTrue() {
        $this->fileObject = CFile::set(self::$BASE_PATH . '\files\to-be-deleted.txt')->create();
        
        $this->assertTrue($this->fileObject->exists);
        $this->assertTrue($this->fileObject->delete());
        $this->assertFalse($this->fileObject->exists);
    }
    
    public function testDelete_NonEmptyFolderWithPurge_ReturnsTrue() {
        $this->folderObject = CFile::set(self::$BASE_PATH . '\files\to-be-deleted')->createDir();
        
        $this->assertTrue($this->folderObject->exists);
        $this->assertTrue($this->folderObject->delete(true));
        $this->assertFalse($this->folderObject->exists);
    }
    
    protected function tearDown() {
        unset($this->fileObject);
        unset($this->folderObject);
    }
}

?>
