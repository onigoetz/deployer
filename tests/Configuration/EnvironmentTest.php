<?php
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Directories;
use Onigoetz\Deployer\Configuration\Server;
use Onigoetz\Deployer\Configuration\Source;
use Onigoetz\Deployer\Configuration\Environment;

/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 22:12
 */
class EnvironmentTest extends PHPUnit_Framework_TestCase
{
    protected function getManager()
    {
        $manager = new ConfigurationManager();

        $manager->setDefaultDirectories(new Directories('default', array('root' => '/var/www'), $manager));

        return $manager;
    }

    public function testIsValid()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', array('strategy' => 'clone', 'path' => '/projects/app'), $mgr));
        $mgr->set(new Server('localhost', array('host' => 'localhost', 'username' => 'root'), $mgr));

        $env = array(
            'source' => 'the_source',
            'servers' => array('localhost'),
        );

        $environment = new Environment('production', $env, $mgr);
        $this->assertTrue($environment->isValid());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoSource()
    {
        $mgr = $this->getManager();
        $mgr->set(new Server('localhost', array('host' => 'localhost', 'username' => 'root'), $mgr));

        $env = array(
            'servers' => array('localhost'),
        );

        $environment = new Environment('production', $env, $mgr);
        $this->assertFalse($environment->isValid());

        $environment->getSource();
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNonExistingSource()
    {
        $mgr = $this->getManager();

        $mgr->set(new Server('localhost', array('host' => 'localhost', 'username' => 'root'), $mgr));

        $env = array(
            'source' => 'the_source',
            'servers' => array('localhost'),
        );

        $environment = new Environment('production', $env, $mgr);
        $this->assertFalse($environment->isValid());

       $environment->getSource();
    }

    public function testInvalidSource()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', array('strategy' => 'clone'), $mgr));
        $mgr->set(new Server('localhost', array('host' => 'localhost', 'username' => 'root'), $mgr));

        $env = array(
            'source' => 'the_source',
            'servers' => array('localhost'),
        );

        $environment = new Environment('production', $env, $mgr);
        $this->assertFalse($environment->isValid());
    }

    public function testInvalidDirectories()
    {
        $mgr = new ConfigurationManager();
        $mgr->setDefaultDirectories(new Directories('default', array(), $mgr));
        $mgr->set(Source::make('the_source', array('strategy' => 'clone', 'path' => '/projects/app'), $mgr));
        $mgr->set(new Server('localhost', array('host' => 'localhost', 'username' => 'root'), $mgr));

        $env = array(
            'source' => 'the_source',
            'servers' => array('localhost'),
        );

        $environment = new Environment('production', $env, $mgr);
        $this->assertFalse($environment->isValid());
    }

    public function testInvalidServer()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', array('strategy' => 'clone', 'path' => '/projects/app'), $mgr));
        $mgr->set(new Server('localhost', array('host' => 'localhost'), $mgr));

        $env = array(
            'source' => 'the_source',
            'servers' => array('localhost'),
        );

        $environment = new Environment('production', $env, $mgr);
        $this->assertFalse($environment->isValid());
    }

    public function testGetServerUsername()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', array('strategy' => 'clone', 'path' => '/projects/app'), $mgr));

        $data = array('host' => 'localhost', 'username' => 'root');
        $mgr->set(new Server('localhost', $data, $mgr));

        $env = array(
            'source' => 'the_source',
            'servers' => array('localhost'),
        );

        $environment = new Environment('production', $env, $mgr);

        $servers = $environment->getServers();

        $this->assertEquals($data['username'], $servers[0]->getUsername());
    }

    public function testGetOverridenDirectories()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', array('strategy' => 'clone', 'path' => '/projects/app'), $mgr));
        $mgr->set(new Server('localhost', array('host' => 'localhost', 'username' => 'root'), $mgr));

        $data = array(
            'root' => '/root',
            'binary_name' => 'yep'
        );

        $env = array(
            'source' => 'the_source',
            'servers' => array('localhost'),
            'overrides' => array(
                'directories' => $data
            )
        );

        $environment = new Environment('production', $env, $mgr);
        $this->assertEquals($data['root'], $environment->getDirectories()->getRoot());
        $this->assertEquals($data['binary_name'], $environment->getDirectories()->getBinaryName());
    }

    public function testGetOverridenSource()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', array('strategy' => 'clone', 'path' => '/projects/app'), $mgr));
        $mgr->set(new Server('localhost', array('host' => 'localhost', 'username' => 'root'), $mgr));

        $data = array(
            'path' => '/root',
            'branch' => 'develop'
        );

        $env = array(
            'source' => 'the_source',
            'servers' => array('localhost'),
            'overrides' => array(
                'source' => $data
            )
        );

        $environment = new Environment('production', $env, $mgr);
        $this->assertEquals($data['path'], $environment->getSource()->getPath());
        $this->assertEquals($data['branch'], $environment->getSource()->getBranch());
    }
}
