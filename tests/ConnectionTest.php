<?php

namespace yiiunit\oss;

/**
 * @author huangxy <huangxy10@qq.com>
 */
class ConnectionTest extends TestCase
{
    private $testObject = 'oss-sdk-test/oss-object-name.txt';
    
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
        $oss->putObjectByFile($this->testObject, __FILE__);
        $this->assertTrue($oss->isObjectExist($this->testObject));
        $content = $oss->getObjectContent($this->testObject);
        $this->assertSame(file_get_contents(__FILE__), $content);
    }

    public function testGetObjectByFile()
    {
        $oss = $this->getConnection();
        $oss->putObjectByFile($this->testObject, __FILE__);
        $this->assertTrue($oss->isObjectExist($this->testObject));
        
        $file = \Yii::getAlias('@runtime/test.txt');
        $oss->getObjectFile($this->testObject, $file);
        $this->assertSame(file_get_contents(__FILE__), file_get_contents($file));
        unlink($file);
    }
}