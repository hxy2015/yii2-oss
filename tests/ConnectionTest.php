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
        $filename = tmpfile();
        file_put_contents($filename, 'hehe');
        $oss->putObjectByFile($this->testObject, 'hehe', $filename);
        $this->assertTrue($oss->isObjectExist($this->testObject));
        $content = $oss->getObjectContent($this->testObject);
        $this->assertSame('hehe', $content);
    }
}