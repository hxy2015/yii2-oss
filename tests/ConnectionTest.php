<?php

namespace yiiunit\oss;

/**
 * @author huangxy <huangxy10@qq.com>
 */
class ConnectionTest extends TestCase
{
    private $testObject = 'some_dir/some_uuid';
    
    public function testPutObjectByContent()
    {
        $oss = $this->getConnection();
        $oss->putObjectByContent($this->testObject, 'haha');
        $this->assertTrue($oss->isObjectExist($this->testObject));
        $content = $oss->getObjectContent($this->testObject);
        $this->assertSame('haha', $content);
    }
    
    
    public function testPutObjectByFile()
    {
        $oss = $this->getConnection();
        $filename = __DIR__ . '/data/haha.txt';
        $oss->putObjectByFile($this->testObject, $filename);
        $this->assertTrue($oss->isObjectExist($this->testObject));
        $content = $oss->getObjectContent($this->testObject);
        $this->assertSame('haha', $content);
    }
}