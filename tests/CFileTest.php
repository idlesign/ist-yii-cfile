<?php

/**
 * Description of CFileTest
 *
 * @author Danish Satkut
 */
class CFileTest extends CTestCase {
    private $fileObject;
    private static $BASE_PATH = 'C:\xampp\htdocs\yii-sandbox\relations-tests';
    
    public function setUp() {
        $this->fileObject = new CFile();
    }
    
    public function tearDown() {
        unset($this->fileObject);
    }
    
    /**
     * Test for type of object returned 
     */
    public function testFileSet() {
        $file = $this->fileObject->set(self::$BASE_PATH . "\files\hello.txt");
        $this->assertInstanceOf('CFile', $file);
    }
}

?>
