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
        $data = ['host' => 'localhost'];

        $server = new Server('local', $data, $this->getManager());

        $this->assertEquals($data['host'], $server->getHost());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoHost()
    {
        $server = new Server('local', [], $this->getManager());

        $server->getHost();
    }

    public function testGetUsername()
    {
        $data = ['username' => 'sgoetz'];

        $server = new Server('local', $data, $this->getManager());

        $this->assertEquals($data['username'], $server->getUsername());
    }

    public function testGetPassword()
    {
        $data = ['password' => 'dummyPass'];

        $server = new Server('local', $data, $this->getManager());

        $this->assertEquals($data['password'], $server->getPassword());
    }

    public function testNoPassword()
    {
        $server = new Server('local', [], $this->getManager());

        $this->assertEquals(Server::$defaultPassword, $server->getPassword());
    }

    public function testValidData()
    {
        $data = ['host' => 'localhost', 'username' => 'sgoetz'];

        $server = new Server('local', $data, $this->getManager());

        $this->assertTrue($server->isValid());
    }

    public function testInvalidData()
    {
        $data = ['host' => 'localhost', 'password' => 'dummyPass'];

        $server = new Server('local', $data, $this->getManager());

        $this->assertFalse($server->isValid());
    }
}
