<?php
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Source;

/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 22:12
 */
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
        Source::make(array(), $this->getManager());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testUnkownStrategy()
    {
        Source::make(array('strategy' => 'carrier_pigeon'), $this->getManager());
    }

    public function testCloneStrategy()
    {
        $source = Source::make(array('strategy' => 'clone'), $this->getManager());

        $this->assertInstanceOf('Onigoetz\Deployer\Configuration\Sources\Cloned', $source);
    }

    public function testUploadStrategy()
    {
        $source = Source::make(array('strategy' => 'upload'), $this->getManager());

        $this->assertInstanceOf('Onigoetz\Deployer\Configuration\Sources\Upload', $source);
    }

    public function testGetPath()
    {
        $data = array('strategy' => 'upload', 'path' => '/the/path');

        $source = Source::make($data, $this->getManager());

        $this->assertEquals($data['path'], $source->getPath());
    }

    public function testGetPathInherited()
    {
        $mgr = $this->getManager();

        $master_data = array('strategy' => 'upload', 'path' => '/main/path');
        $mgr->set('master', Source::make($master_data, $mgr));

        $data = array('strategy' => 'upload', 'extends' => 'master');
        $mgr->set('apprentice', $source = Source::make($data, $mgr));

        $this->assertEquals($master_data['path'], $source->getPath());
    }

    /**
     * @expectedException \LogicException
     */
    public function testNoPath()
    {
        $data = array('strategy' => 'upload');

        $source = Source::make($data, $this->getManager());

        $source->getPath();
    }

    public function testIsInvalid()
    {
        $data = array('strategy' => 'upload');

        $source = Source::make($data, $this->getManager());

        $this->assertFalse($source->isValid());
    }

    public function testIsValid()
    {
        $data = array('strategy' => 'upload', 'path' => '/a/path');

        $source = Source::make($data, $this->getManager());

        $this->assertTrue($source->isValid());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNonExistingParent()
    {
        $data = array('strategy' => 'upload', 'extends' => 'master', 'path' => '/main/path');
        $source = Source::make($data, $this->getManager());

        $source->getPath();
    }
}
