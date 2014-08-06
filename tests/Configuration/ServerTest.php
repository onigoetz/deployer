<?php
use Onigoetz\Deployer\Configuration\Server;

class ServerTest extends PHPUnit_Framework_TestCase
{
    public function testGetHost()
    {
        $data = array('host' => 'localhost');

        $server = new Server($data);

        $this->assertEquals($data['host'], $server->getHost());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoHost()
    {
        $server = new Server(array());

        $server->getHost();
    }

    public function testGetUsername()
    {
        $data = array('username' => 'sgoetz');

        $server = new Server($data);

        $this->assertEquals($data['username'], $server->getUsername());
    }

    public function testGetPassword()
    {
        $data = array('password' => 'dummyPass');

        $server = new Server($data);

        $this->assertEquals($data['password'], $server->getPassword());
    }

    public function testNoPassword()
    {
        $server = new Server(array());

        $this->assertEquals(Server::$defaultPassword, $server->getPassword());
    }

    public function testValidData()
    {
        $data = array('host' => 'localhost', 'username' => 'sgoetz');

        $server = new Server($data);

        $this->assertTrue($server->isValid());
    }

    public function testInvalidData()
    {
        $data = array('host' => 'localhost', 'password' => 'dummyPass');

        $server = new Server($data);

        $this->assertFalse($server->isValid());
    }
}
