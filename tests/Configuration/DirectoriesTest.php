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
        $data = ['root' => '/var/www'];

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['root'], $directories->getRoot());
    }

    public function testGetInheritedRoot()
    {
        $data = ['root' => '/var/www'];
        $parent = new Directories('default', $data, $this->getManager());

        $directories = new Directories('override', [], $this->getManager(), $parent);

        $this->assertEquals($data['root'], $directories->getRoot());
    }

    public function testGetRootWithInheritance()
    {
        $parent = new Directories('default', ['root' => '/var/www'], $this->getManager());
        $data2 = ['root' => '/var/www2'];

        $directories = new Directories('override', $data2, $this->getManager(), $parent);

        $this->assertEquals($data2['root'], $directories->getRoot());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoRoot()
    {
        $parent = new Directories('default', [], $this->getManager());
        $directories = new Directories('override', [], $this->getManager(), $parent);

        $directories->getRoot();
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoRootWithInheritance()
    {
        $parent = new Directories('default', [], $this->getManager());
        $directories = new Directories('override', [], $this->getManager(), $parent);

        $directories->getRoot();
    }

    public function testGetDefaultBinaryName()
    {
        $directories = new Directories('default', [], $this->getManager());

        $this->assertEquals(Directories::$defaultBinaryName, $directories->getBinaryName());
    }

    public function testGetBinaryName()
    {
        $data = ['binary_name' => '%A %B %C'];

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['binary_name'], $directories->getBinaryName());
    }

    public function testGetNewBinaryName()
    {
        $data = ['root' => '/var/www', 'binaries' => 'bin', 'binary_name' => '%Y'];

        $directories = new Directories('default', $data, $this->getManager());

        $expected = $data['root'] . '/' . $data['binaries'] . '/' . strftime('%Y');

        $this->assertEquals($expected, $directories->getNewBinaryName());
    }

    public function testGetDefaultBinaries()
    {
        $data = ['root' => '/var/www'];
        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['root'] . '/' . Directories::$defaultBinaries, $directories->getBinaries());
    }

    public function testGetBinaries()
    {
        $data = ['binaries' => 'binar', 'root' => '/var/www'];

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['root'] . '/' . $data['binaries'], $directories->getBinaries());
    }

    public function testGetDefaultDeploy()
    {
        $data = ['root' => '/var/www'];
        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['root'] . '/' . Directories::$defaultDeploy, $directories->getDeploy());
    }

    public function testGetDeploy()
    {
        $data = ['deploy' => 'dep', 'root' => '/var/www'];

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertEquals($data['root'] . '/' . $data['deploy'], $directories->getDeploy());
    }

    public function testGetInheritedDeploy()
    {
        $data = ['deploy' => 'dep', 'root' => '/var/www'];
        $parent = new Directories('default', $data, $this->getManager());

        $directories = new Directories('override', [], $this->getManager(), $parent);

        $this->assertEquals($data['root'] . '/' . $data['deploy'], $directories->getDeploy());
    }

    public function testGetDefaultInheritedDeploy()
    {
        $data = ['root' => '/var/www'];

        $parent = new Directories('default', $data, $this->getManager());
        $directories = new Directories('override', [], $this->getManager(), $parent);

        $this->assertEquals($data['root'] . '/' . Directories::$defaultDeploy, $directories->getDeploy());
    }

    public function testIsValid()
    {
        $data = ['root' => '/var/www'];

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertTrue($directories->isValid());
    }

    public function testIsValidWithInheritance()
    {
        $parent = new Directories('default', ['root' => '/var/www'], $this->getManager());

        $directories = new Directories('override', [], $this->getManager(), $parent);

        $this->assertTrue($directories->isValid());
    }

    public function testIsInvalid()
    {
        $data = ['deploy' => 'dep'];

        $directories = new Directories('default', $data, $this->getManager());

        $this->assertFalse($directories->isValid());
    }

    public function testIsInvalidWithInheritance()
    {
        $parent = new Directories('default', ['deploy' => 'dep'], $this->getManager());

        $directories = new Directories('override', [], $this->getManager(), $parent);

        $this->assertFalse($directories->isValid());
    }
}
