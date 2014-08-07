<?php

use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Directories;
use Onigoetz\Deployer\Configuration\Server;

class ConfigurationManagerTest extends PHPUnit_Framework_TestCase
{

    public function testGetDirectories()
    {
        $manager = new ConfigurationManager();

        $directories = new Directories('default', array(), $manager);
        $manager->setDefaultDirectories($directories);

        $this->assertSame($directories, $manager->getDefaultDirectories());
    }

    public function testCreate()
    {
        $data = array(
            'directories' => array('root' => '/var/www'),
            'sources' => array('cloned' => array('strategy' => 'clone', 'path' => 'http://github.com')),
            'servers' => array('localhost' => array('host' => '127.0.0.1', 'username' => 'root')),
            'environments' => array('prod' => array('source' => 'cloned', 'servers' => array('localhost')))
        );

        $manager = ConfigurationManager::create($data);

        $this->assertEquals($data['directories']['root'], $manager->getDefaultDirectories()->getRoot());
        $this->assertEquals($data['sources']['cloned']['path'], $manager->get('source', 'cloned')->getPath());
        $this->assertEquals($data['servers']['localhost']['host'], $manager->get('server', 'localhost')->getHost());

        $servers = $manager->get('environment', 'prod')->getServers();
        $this->assertEquals($data['servers']['localhost']['username'], $servers[0]->getUsername());
        $this->assertTrue($manager->get('environment', 'prod')->isValid());
    }

    public function testLogs()
    {
        $manager = new ConfigurationManager();

        $server = new Server('localhost', array(), $manager);
        $server->isValid();

        $this->assertEquals(array("no 'host' specified in server 'localhost'"), $manager->getLogs());
    }
}
