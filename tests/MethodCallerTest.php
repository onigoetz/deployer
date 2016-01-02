<?php

use Mockery as m;
use Onigoetz\Deployer\MethodCaller;

class MethodCallerTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCallReorderArgs()
    {
        $config = ['height' => 100, 'width' => 200];

        $image = m::mock(MethodCallerTestClass::class);
        $image->shouldReceive('test')->with($config['width'], $config['height'])->andReturn(true);

        (new MethodCaller)->call($image, 'test', $config);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCallMethodDoesntExist()
    {
        (new MethodCaller)->call(new MethodCallerTestClass, 'foo', []);
    }

    public function testCallFindsDefaultArgs()
    {
        $config = ['one' => 90];

        $image = m::mock(MethodCallerTestClass::class);
        $image->shouldReceive('testWithDefaults')->with($config['one'], 'default')->andReturn(true);

        (new MethodCaller)->call($image, 'testWithDefaults', $config);
    }
}

class MethodCallerTestClass
{
    public function test($width, $height)
    {
    }

    public function testWithDefaults($one, $two = 'default')
    {
    }
}
