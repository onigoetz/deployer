<?php

use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Source;
use Onigoetz\Deployer\Configuration\Sources\Cloned;

class SourceCloneTest extends PHPUnit_Framework_TestCase
{
    protected function getManager()
    {
        return new ConfigurationManager();
    }

    public function testIsValid()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertTrue($source->isValid());
    }

    public function testGetBranch()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path', 'branch' => 'develop');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['branch'], $source->getBranch());
    }

    public function testGetBranchInherited()
    {
        $mgr = $this->getManager();

        $master_data = array('strategy' => 'clone', 'path' => '/main/path', 'branch' => 'develop');
        $mgr->set(Source::make('master', $master_data, $mgr));

        $data = array('strategy' => 'clone', 'extends' => 'master');
        $mgr->set($source = Source::make('apprentice', $data, $mgr));;

        $this->assertEquals($master_data['branch'], $source->getBranch());
    }

    public function testGetDefaultBranch()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(Cloned::$defaultBranch, $source->getBranch());
    }

    public function testGetDefaultBranchInherited()
    {
        $mgr = $this->getManager();

        $master_data = array('strategy' => 'clone', 'path' => '/main/path');
        $mgr->set(Source::make('master', $master_data, $mgr));

        $data = array('strategy' => 'clone', 'extends' => 'master');
        $mgr->set($source = Source::make('apprentice', $data, $mgr));;

        $this->assertEquals(Cloned::$defaultBranch, $source->getBranch());
    }


    public function testGetType()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path', 'type' => 'mercurial');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['type'], $source->getType());
    }

    public function testGetDefaultType()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(Cloned::$defaultType, $source->getType());
    }

    public function testGetSubmodules()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path', 'submodules' => true);

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['submodules'], $source->getSubmodules());
    }

    public function testGetDefaultSubmodules()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(Cloned::$defaultSubmodules, $source->getSubmodules());
    }
}
