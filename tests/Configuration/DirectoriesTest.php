<?php

use Onigoetz\Deployer\Configuration\Directories;

class DirectoriesTest extends PHPUnit_Framework_TestCase
{

    public function testGetRoot()
    {
        $data = array('root' => '/var/www');

        $directories = new Directories($data);

        $this->assertEquals($data['root'], $directories->getRoot());
    }

    public function testGetInheritedRoot()
    {
        $data = array('root' => '/var/www');

        $directories = new Directories(array(), new Directories($data));

        $this->assertEquals($data['root'], $directories->getRoot());
    }

    public function testGetRootWithInheritance()
    {
        $data = array('root' => '/var/www');
        $data2 = array('root' => '/var/www2');

        $directories = new Directories($data2, new Directories($data));

        $this->assertEquals($data2['root'], $directories->getRoot());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoRoot()
    {
        $directories = new Directories(array(), new Directories(array()));

        $directories->getRoot();
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoRootWithInheritance()
    {
        $directories = new Directories(array(), new Directories(array()));

        $directories->getRoot();
    }

    public function testGetDefaultBinaryName()
    {
        $directories = new Directories(array());

        $this->assertEquals(Directories::$defaultBinaryName, $directories->getBinaryName());
    }

    public function testGetBinaryName()
    {
        $data = array('binary_name' => '%A %B %C');

        $directories = new Directories($data);

        $this->assertEquals($data['binary_name'], $directories->getBinaryName());
    }

    public function testGetDefaultBinaries()
    {
        $directories = new Directories(array());

        $this->assertEquals(Directories::$defaultBinaries, $directories->getBinaries());
    }

    public function testGetBinaries()
    {
        $data = array('binaries' => 'binar');

        $directories = new Directories($data);

        $this->assertEquals($data['binaries'], $directories->getBinaries());
    }

    public function testGetDefaultDeploy()
    {
        $directories = new Directories(array());

        $this->assertEquals(Directories::$defaultDeploy, $directories->getDeploy());
    }

    public function testGetDeploy()
    {
        $data = array('deploy' => 'dep');

        $directories = new Directories($data);

        $this->assertEquals($data['deploy'], $directories->getDeploy());
    }

    public function testGetInheritedDeploy()
    {
        $data = array('deploy' => 'dep');

        $directories = new Directories(array(), new Directories($data));

        $this->assertEquals($data['deploy'], $directories->getDeploy());
    }

    public function testGetDefaultInheritedDeploy()
    {
        $directories = new Directories(array(), new Directories(array()));

        $this->assertEquals(Directories::$defaultDeploy, $directories->getDeploy());
    }

    public function testIsValid()
    {
        $data = array('root' => '/var/www');

        $directories = new Directories($data);

        $this->assertTrue($directories->isValid());
    }

    public function testIsValidWithInheritance()
    {
        $data = array('root' => '/var/www');

        $directories = new Directories(array(), new Directories($data));

        $this->assertTrue($directories->isValid());
    }

    public function testIsInvalid()
    {
        $data = array('deploy' => 'dep');

        $directories = new Directories($data);

        $this->assertFalse($directories->isValid());
    }

    public function testIsInvalidWithInheritance()
    {
        $data = array('deploy' => 'dep');

        $directories = new Directories(array(), new Directories($data));

        $this->assertFalse($directories->isValid());
    }
}
