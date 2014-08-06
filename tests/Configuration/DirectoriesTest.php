<?php

use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Directories;

class DirectoriesTest extends PHPUnit_Framework_TestCase
{

    protected function getManager()
    {
        return new ConfigurationManager();
    }

    public function testGetRoot()
    {
        $data = array('root' => '/var/www');

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['root'], $directories->getRoot());
    }

    public function testGetInheritedRoot()
    {
        $data = array('root' => '/var/www');
        $parent = new Directories('default', $data, $this->getManager());

        $directories = new Directories('override', array(), $this->getManager(), $parent);

        $this->assertEquals($data['root'], $directories->getRoot());
    }

    public function testGetRootWithInheritance()
    {
        $parent = new Directories('default', array('root' => '/var/www'), $this->getManager());
        $data2 = array('root' => '/var/www2');

        $directories = new Directories('override', $data2, $this->getManager(), $parent);

        $this->assertEquals($data2['root'], $directories->getRoot());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoRoot()
    {
        $parent = new Directories('default', array(), $this->getManager());
        $directories = new Directories('override', array(), $this->getManager(), $parent);

        $directories->getRoot();
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoRootWithInheritance()
    {
        $parent = new Directories('default', array(), $this->getManager());
        $directories = new Directories('override', array(), $this->getManager(), $parent);

        $directories->getRoot();
    }

    public function testGetDefaultBinaryName()
    {
        $directories = new Directories('default', array(), $this->getManager());

        $this->assertEquals(Directories::$defaultBinaryName, $directories->getBinaryName());
    }

    public function testGetBinaryName()
    {
        $data = array('binary_name' => '%A %B %C');

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['binary_name'], $directories->getBinaryName());
    }

    public function testGetDefaultBinaries()
    {
        $directories = new Directories('default', array(), $this->getManager());

        $this->assertEquals(Directories::$defaultBinaries, $directories->getBinaries());
    }

    public function testGetBinaries()
    {
        $data = array('binaries' => 'binar');

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['binaries'], $directories->getBinaries());
    }

    public function testGetDefaultDeploy()
    {
        $directories = new Directories('default', array(), $this->getManager());

        $this->assertEquals(Directories::$defaultDeploy, $directories->getDeploy());
    }

    public function testGetDeploy()
    {
        $data = array('deploy' => 'dep');

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['deploy'], $directories->getDeploy());
    }

    public function testGetInheritedDeploy()
    {
        $data = array('deploy' => 'dep');
        $parent = new Directories('default', $data, $this->getManager());

        $directories = new Directories('override', array(), $this->getManager(), $parent);

        $this->assertEquals($data['deploy'], $directories->getDeploy());
    }

    public function testGetDefaultInheritedDeploy()
    {
        $parent = new Directories('default', array(), $this->getManager());
        $directories = new Directories('override', array(), $this->getManager(), $parent);

        $this->assertEquals(Directories::$defaultDeploy, $directories->getDeploy());
    }

    public function testIsValid()
    {
        $data = array('root' => '/var/www');

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertTrue($directories->isValid());
    }

    public function testIsValidWithInheritance()
    {
        $parent = new Directories('default', array('root' => '/var/www'), $this->getManager());

        $directories = new Directories('override', array(), $this->getManager(), $parent);

        $this->assertTrue($directories->isValid());
    }

    public function testIsInvalid()
    {
        $data = array('deploy' => 'dep');

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertFalse($directories->isValid());
    }

    public function testIsInvalidWithInheritance()
    {
        $parent = new Directories('default', array('deploy' => 'dep'), $this->getManager());

        $directories = new Directories('override', array(), $this->getManager(), $parent);

        $this->assertFalse($directories->isValid());
    }
}
