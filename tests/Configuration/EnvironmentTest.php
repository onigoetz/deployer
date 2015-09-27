<?php
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Directories;
use Onigoetz\Deployer\Configuration\Environment;
use Onigoetz\Deployer\Configuration\Server;
use Onigoetz\Deployer\Configuration\Source;

class EnvironmentTest extends PHPUnit_Framework_TestCase
{
    protected function getManager()
    {
        $manager = new ConfigurationManager();

        $manager->setDefaultDirectories(new Directories('default', ['root' => '/var/www'], $manager));

        return $manager;
    }

    public function testIsValid()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', ['strategy' => 'clone', 'path' => '/projects/app'], $mgr));
        $mgr->set(new Server('localhost', ['host' => 'localhost', 'username' => 'root'], $mgr));

        $env = [
            'source' => 'the_source',
            'servers' => ['localhost'],
        ];

        $environment = new Environment('production', $env, $mgr);
        $this->assertTrue($environment->isValid());
    }

    /**
     * @expectedException     \LogicException
     */
    public function testNoSource()
    {
        $mgr = $this->getManager();
        $mgr->set(new Server('localhost', ['host' => 'localhost', 'username' => 'root'], $mgr));

        $env = [
            'servers' => ['localhost'],
        ];

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

        $mgr->set(new Server('localhost', ['host' => 'localhost', 'username' => 'root'], $mgr));

        $env = [
            'source' => 'the_source',
            'servers' => ['localhost'],
        ];

        $environment = new Environment('production', $env, $mgr);
        $this->assertFalse($environment->isValid());

        $environment->getSource();
    }

    public function testInvalidSource()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', ['strategy' => 'clone'], $mgr));
        $mgr->set(new Server('localhost', ['host' => 'localhost', 'username' => 'root'], $mgr));

        $env = [
            'source' => 'the_source',
            'servers' => ['localhost'],
        ];

        $environment = new Environment('production', $env, $mgr);
        $this->assertFalse($environment->isValid());
    }

    public function testInvalidDirectories()
    {
        $mgr = new ConfigurationManager();
        $mgr->setDefaultDirectories(new Directories('default', [], $mgr));
        $mgr->set(Source::make('the_source', ['strategy' => 'clone', 'path' => '/projects/app'], $mgr));
        $mgr->set(new Server('localhost', ['host' => 'localhost', 'username' => 'root'], $mgr));

        $env = [
            'source' => 'the_source',
            'servers' => ['localhost'],
        ];

        $environment = new Environment('production', $env, $mgr);
        $this->assertFalse($environment->isValid());
    }

    public function testInvalidServer()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', ['strategy' => 'clone', 'path' => '/projects/app'], $mgr));
        $mgr->set(new Server('localhost', ['host' => 'localhost'], $mgr));

        $env = [
            'source' => 'the_source',
            'servers' => ['localhost'],
        ];

        $environment = new Environment('production', $env, $mgr);
        $this->assertFalse($environment->isValid());
    }

    public function testGetServerUsername()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', ['strategy' => 'clone', 'path' => '/projects/app'], $mgr));

        $data = ['host' => 'localhost', 'username' => 'root'];
        $mgr->set(new Server('localhost', $data, $mgr));

        $env = [
            'source' => 'the_source',
            'servers' => ['localhost'],
        ];

        $environment = new Environment('production', $env, $mgr);

        $servers = $environment->getServers();

        $this->assertEquals($data['username'], $servers[0]->getUsername());
    }

    public function testGetOverridenDirectories()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', ['strategy' => 'clone', 'path' => '/projects/app'], $mgr));
        $mgr->set(new Server('localhost', ['host' => 'localhost', 'username' => 'root'], $mgr));

        $data = [
            'root' => '/root',
            'binary_name' => 'yep',
        ];

        $env = [
            'source' => 'the_source',
            'servers' => ['localhost'],
            'overrides' => [
                'directories' => $data,
            ],
        ];

        $environment = new Environment('production', $env, $mgr);
        $this->assertEquals($data['root'], $environment->getDirectories()->getRoot());
        $this->assertEquals($data['binary_name'], $environment->getDirectories()->getBinaryName());
    }

    public function testGetSubstitutions()
    {
        $mgr = $this->getManager();

        $env = 'production';
        $environment = new Environment($env, [], $mgr);
        $data = ['root' => '/var/www'];
        $directories = new Directories('default', $data, $mgr);
        $mgr->setDefaultDirectories($directories);

        $binary = $directories->getNewBinaryName();

        $final = [
            '{{environment}}' => $env,
            '{{root}}' => $directories->getRoot(),
            '{{binaries}}' => $directories->getBinaries(),
            '{{binary}}' => $binary,
            '{{deploy}}' => $directories->getDeploy(),
        ];

        $this->assertEquals($final, $environment->getSubstitutions($binary));
    }

    public function testGetOverridenSource()
    {
        $mgr = $this->getManager();
        $mgr->set(Source::make('the_source', ['strategy' => 'clone', 'path' => '/projects/app'], $mgr));
        $mgr->set(new Server('localhost', ['host' => 'localhost', 'username' => 'root'], $mgr));

        $data = [
            'path' => '/root',
            'branch' => 'develop',
        ];

        $env = [
            'source' => 'the_source',
            'servers' => ['localhost'],
            'overrides' => [
                'source' => $data,
            ],
        ];

        $environment = new Environment('production', $env, $mgr);
        $this->assertEquals($data['path'], $environment->getSource()->getPath());
        $this->assertEquals($data['branch'], $environment->getSource()->getBranch());
    }

    public function testExampleConfiguration()
    {
        $config_folder = dirname(dirname(__DIR__)) . '/src/config';

        $configuration = [
            'directories' => include "$config_folder/directories.php",
            'servers' => include "$config_folder/servers.php",
            'sources' => include "$config_folder/sources.php",
            'tasks' => include "$config_folder/tasks.php",
            'environments' => include "$config_folder/environments.php",
        ];

        $manager = ConfigurationManager::create($configuration);

        foreach (array_keys($configuration['environments']) as $env) {
            if (!$result = $manager->get('environment', $env)->isValid()) {
                foreach ($manager->getLogs() as $line) {
                    echo "$line\n";
                }
            }

            $this->assertTrue($result, "The environment '$env' isn't valid!");
        }
    }
}
