<?php

use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Source;
use Onigoetz\Deployer\Configuration\Sources\Cloned;
use Onigoetz\Deployer\Configuration\Sources\Upload;

class SourceUploadTest extends PHPUnit_Framework_TestCase
{
    protected function getManager()
    {
        return new ConfigurationManager();
    }

    public function testGetInclude()
    {
        $data = array('strategy' => 'upload', 'path' => '/the/path', 'include' => array('app'));

        $source = Source::make($data, $this->getManager());

        $this->assertEquals($data['include'], $source->getInclude());
    }

    public function testGetDefaultType()
    {
        $data = array('strategy' => 'upload', 'path' => '/the/path');

        $source = Source::make($data, $this->getManager());

        $this->assertEquals(Upload::$defaultInclude, $source->getInclude());
    }

    public function testGetSubmodules()
    {
        $data = array('strategy' => 'upload', 'path' => '/the/path', 'exclude' => array('app/storage'));

        $source = Source::make($data, $this->getManager());

        $this->assertEquals($data['exclude'], $source->getExclude());
    }

    public function testGetDefaultSubmodules()
    {
        $data = array('strategy' => 'upload', 'path' => '/the/path');

        $source = Source::make($data, $this->getManager());

        $this->assertEquals(Upload::$defaultExclude, $source->getExclude());
    }
}
