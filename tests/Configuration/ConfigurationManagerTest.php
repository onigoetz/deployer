<?php

use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Directories;
use Onigoetz\Deployer\Configuration\Server;

class ConfigurationManagerTest extends PHPUnit_Framework_TestCase
{
    public function testGetDirectories()
    {
        $manager = new ConfigurationManager();

        $directories = new Directories('default', [], $manager);
        $manager->setDefaultDirectories($directories);

        $this->assertSame($directories, $manager->getDefaultDirectories());
    }

    public function testCreate()
    {
        $data = [
            'directories' => ['root' => '/var/www'],
            'sources' => ['cloned' => ['strategy' => 'clone', 'path' => 'http://github.com']],
            'servers' => ['localhost' => ['host' => '127.0.0.1', 'username' => 'root']],
            'environments' => ['prod' => ['source' => 'cloned', 'servers' => ['localhost']]],
            'tasks' => ['before' => [['action' => 'prune']]],
        ];

        $manager = ConfigurationManager::create($data);

        $this->assertEquals($data['directories']['root'], $manager->getDefaultDirectories()->getRoot());
        $this->assertEquals($data['sources']['cloned']['path'], $manager->get('source', 'cloned')->getPath());
        $this->assertEquals($data['servers']['localhost']['host'], $manager->get('server', 'localhost')->getHost());

        $servers = $manager->get('environment', 'prod')->getServers();
        $this->assertEquals($data['servers']['localhost']['username'], $servers[0]->getUsername());
        $this->assertTrue($manager->get('environment', 'prod')->isValid());
    }

    public function testGetTasks()
    {
        $data = [
            'environments' => ['prod' => ['tasks' => ['before' => ['do_before']]]],
            'tasks' => ['do_before' => [['action' => 'prune']]],
        ];
        $data += ['directories' => ['root' => '/var/www'],'sources' => [], 'servers' => []];

        $manager = ConfigurationManager::create($data);

        $env = $manager->get('environment', 'prod');

        $this->assertEquals($data['tasks']['do_before'], $env->getTasks('before'));
    }

    public function testGetNamedTasks()
    {
        $data = [
            'environments' => ['prod' => ['tasks' => ['before' => ['do_before']]]],
            'tasks' => ['do_before' => ['Do Pruning' => ['action' => 'prune']]],
        ];
        $data += ['directories' => ['root' => '/var/www'],'sources' => [], 'servers' => []];

        $manager = ConfigurationManager::create($data);

        $env = $manager->get('environment', 'prod');

        $this->assertEquals($data['tasks']['do_before'], $env->getTasks('before'));
    }

    public function testNoEvents()
    {
        $data = [
            'environments' => ['prod' => ['tasks']],
            'tasks' => [],
        ];
        $data += ['directories' => ['root' => '/var/www'],'sources' => [], 'servers' => []];

        $manager = ConfigurationManager::create($data);

        $env = $manager->get('environment', 'prod');

        $this->assertEquals([], $env->getTasks('before'));
    }

    public function testGetMultiEvents()
    {
        $data = [
            'environments' => ['prod' => ['tasks' => ['before' => ['do_before', 'do_before2']]]],
            'tasks' => [
                'do_before' => [['action' => 'prune']],
                'do_before2' => [['action' => 'symlink']],
            ],
        ];
        $data += ['directories' => ['root' => '/var/www'],'sources' => [], 'servers' => []];

        $final = [['action' => 'prune'], ['action' => 'symlink']];

        $manager = ConfigurationManager::create($data);

        $env = $manager->get('environment', 'prod');

        $this->assertEquals($final, $env->getTasks('before'));
    }

    public function testLogs()
    {
        $manager = new ConfigurationManager();

        $server = new Server('localhost', [], $manager);
        $server->isValid();

        $this->assertEquals(["no 'host' specified in server 'localhost'"], $manager->getLogs());
    }
}
