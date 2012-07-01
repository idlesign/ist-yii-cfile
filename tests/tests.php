<?php

require_once '../CFileHelper.php';


define('TESTS_DIR', dirname(__FILE__));


class CFileTests extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->filename = 'cfile_test_tmp_' . uniqid();
        $this->filepath = sys_get_temp_dir() . '/' . $this->filename;
        $this->cf = CFileHelper::get($this->filepath);
    }

    public function tearDown() {
        $this->cf->delete();
    }

    public function testTestsDir() {
        $cf = $this->cf;
        $cf->createDir();

        $this->assertTrue($cf->getExists());
        $this->assertEquals($this->filename, $cf->getBasename());
        $this->assertEquals($this->filename, $cf->getFilename());
        $this->assertEquals('', $cf->getExtension());
        $this->assertTrue($cf->getIsDir());
        $this->assertTrue($cf->getIsEmpty());
        $this->assertFalse($cf->getIsFile());
        $this->assertFalse($cf->getIsUploaded());
        $this->assertTrue($cf->getReadable());
        $this->assertTrue($cf->getWriteable());
    }

    public function testSetOwner() {
        $cf = $this->cf;
        $cf->create();

        $owner_name = $cf->getOwner();
        $owner_id = $cf->getOwner(False);

        $this->setExpectedException('CFileException');
        $cf->setOwner('nosuchuser123987');

        $this->assertNotEquals($cf->setOwner($owner_name), False);

        $this->setExpectedException('CFileException');
        $cf->setOwner('4567890123');

        $this->setExpectedException('CFileException');
        $cf->setOwner(4567890123);

        $this->assertNotEquals($cf->setOwner($owner_id), False);
    }

    public function testSetGroup() {
        $cf = $this->cf;
        $cf->create();

        $group_name = $cf->getGroup();
        $group_id = $cf->getGroup(False);

        $this->setExpectedException('CFileException');
        $cf->setGroup('nosuchgroup123987');

        $this->assertNotEquals($cf->setGroup($group_name), False);

        $this->setExpectedException('CFileException');
        $cf->setGroup('4567890123');

        $this->setExpectedException('CFileException');
        $cf->setGroup(4567890123);

        $this->assertNotEquals($cf->setGroup($group_id), False);
    }

}
