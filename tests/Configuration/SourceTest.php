<?php
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Source;

class SourceTest extends PHPUnit_Framework_TestCase
{
    protected function getManager()
    {
        return new ConfigurationManager();
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoStrategy()
    {
        Source::make('noname', [], $this->getManager());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testUnkownStrategy()
    {
        Source::make('noname', ['strategy' => 'carrier_pigeon'], $this->getManager());
    }

    public function testCloneStrategy()
    {
        $source = Source::make('noname', ['strategy' => 'clone'], $this->getManager());

        $this->assertInstanceOf('Onigoetz\Deployer\Configuration\Sources\Cloned', $source);
    }

    public function testUploadStrategy()
    {
        $source = Source::make('noname', ['strategy' => 'upload'], $this->getManager());

        $this->assertInstanceOf('Onigoetz\Deployer\Configuration\Sources\Upload', $source);
    }

    public function testGetPath()
    {
        $data = ['strategy' => 'upload', 'path' => '/the/path'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['path'], $source->getPath());
    }

    public function testGetPathInherited()
    {
        $mgr = $this->getManager();

        $master_data = ['strategy' => 'upload', 'path' => '/main/path'];
        $mgr->set(Source::make('master', $master_data, $mgr));

        $data = ['strategy' => 'upload', 'extends' => 'master'];
        $mgr->set($source = Source::make('apprentice', $data, $mgr));

        $this->assertEquals($master_data['path'], $source->getPath());
    }

    /**
     * @expectedException \LogicException
     */
    public function testNoPath()
    {
        $data = ['strategy' => 'upload'];

        $source = Source::make('noname', $data, $this->getManager());

        $source->getPath();
    }

    public function testIsInvalid()
    {
        $data = ['strategy' => 'upload'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertFalse($source->isValid());
    }

    public function testIsValid()
    {
        $data = ['strategy' => 'upload', 'path' => '/a/path'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertTrue($source->isValid());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNonExistingParent()
    {
        $data = ['strategy' => 'upload', 'extends' => 'master', 'path' => '/main/path'];
        $source = Source::make('noname', $data, $this->getManager());

        $source->getPath();
    }
}
