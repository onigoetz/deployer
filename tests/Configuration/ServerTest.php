<?php
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Server;

class ServerTest extends PHPUnit_Framework_TestCase
{
    protected function getManager()
    {
        return new ConfigurationManager();
    }

    public function testGetHost()
    {
        $data = array('host' => 'localhost');

        $server = new Server('local', $data, $this->getManager());

        $this->assertEquals($data['host'], $server->getHost());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoHost()
    {
        $server = new Server('local', array(), $this->getManager());

        $server->getHost();
    }

    public function testGetUsername()
    {
        $data = array('username' => 'sgoetz');

        $server = new Server('local', $data, $this->getManager());

        $this->assertEquals($data['username'], $server->getUsername());
    }

    public function testGetPassword()
    {
        $data = array('password' => 'dummyPass');

        $server = new Server('local', $data, $this->getManager());

        $this->assertEquals($data['password'], $server->getPassword());
    }

    public function testNoPassword()
    {
        $server = new Server('local', array(), $this->getManager());

        $this->assertEquals(Server::$defaultPassword, $server->getPassword());
    }

    public function testValidData()
    {
        $data = array('host' => 'localhost', 'username' => 'sgoetz');

        $server = new Server('local', $data, $this->getManager());

        $this->assertTrue($server->isValid());
    }

    public function testInvalidData()
    {
        $data = array('host' => 'localhost', 'password' => 'dummyPass');

        $server = new Server('local', $data, $this->getManager());

        $this->assertFalse($server->isValid());
    }
}
