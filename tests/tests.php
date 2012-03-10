<?php

require_once '../CFileHelper.php';


define('TESTS_DIR', dirname(__FILE__));


class CFileTests extends PHPUnit_Framework_TestCase {

    public function testTestsDir() {
        $cf = CFileHelper::get(TESTS_DIR);
        $this->assertTrue($cf->getExists());
        $this->assertEquals('tests', $cf->getBasename());
        $this->assertEquals('tests', $cf->getFilename());
        $this->assertEquals('', $cf->getExtension());
        $this->assertTrue($cf->getIsDir());
        $this->assertFalse($cf->getIsEmpty());
        $this->assertFalse($cf->getIsFile());
        $this->assertFalse($cf->getIsUploaded());
        $this->assertTrue($cf->getReadable());
        $this->assertTrue($cf->getWriteable());
    }

}
