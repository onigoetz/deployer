<?php

use Mockery as m;
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Source;
use Onigoetz\Deployer\Configuration\Sources\Cloned;

class SourceCloneTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    protected function getManager()
    {
        return new ConfigurationManager();
    }

    protected function baseConfiguration()
    {
        return array('strategy' => 'clone', 'path' => 'https://github.com/onigoetz/deployer');
    }

    public function testIsValid()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertTrue($source->isValid());
    }

    public function testGetBranch()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path', 'branch' => 'develop');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['branch'], $source->getBranch());
    }

    public function testGetBranchInherited()
    {
        $mgr = $this->getManager();

        $master_data = array('strategy' => 'clone', 'path' => '/main/path', 'branch' => 'develop');
        $mgr->set(Source::make('master', $master_data, $mgr));

        $data = array('strategy' => 'clone', 'extends' => 'master');
        $mgr->set($source = Source::make('apprentice', $data, $mgr));

        $this->assertEquals($master_data['branch'], $source->getBranch());
    }

    public function testGetTypeInherited()
    {
        $mgr = $this->getManager();

        $master_data = array('strategy' => 'clone', 'path' => '/main/path', 'branch' => 'master');
        $mgr->set(Source::make('master', $master_data, $mgr));

        $data = array('branch' => 'develop', 'extends' => 'master');
        $mgr->set($source = Source::make('apprentice', $data, $mgr));

        $this->assertInstanceOf('\Onigoetz\Deployer\Configuration\Sources\Cloned', $source);
    }

    public function testGetDefaultBranch()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(Cloned::$defaultBranch, $source->getBranch());
    }

    public function testGetDefaultBranchInherited()
    {
        $mgr = $this->getManager();

        $master_data = array('strategy' => 'clone', 'path' => '/main/path');
        $mgr->set(Source::make('master', $master_data, $mgr));

        $data = array('strategy' => 'clone', 'extends' => 'master');
        $mgr->set($source = Source::make('apprentice', $data, $mgr));

        $this->assertEquals(Cloned::$defaultBranch, $source->getBranch());
    }


    public function testGetType()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path', 'type' => 'mercurial');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['type'], $source->getType());
    }

    public function testGetDefaultType()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(Cloned::$defaultType, $source->getType());
    }

    public function testGetSubmodules()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path', 'submodules' => true);

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['submodules'], $source->getSubmodules());
    }

    public function testGetDefaultSubmodules()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(Cloned::$defaultSubmodules, $source->getSubmodules());
    }

    public function testGetUsername()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path', 'username' => 'root');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['username'], $source->getUsername());
    }

    public function testGetDefaultUsername()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(null, $source->getUsername());
    }

    public function testGetPassword()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path', 'password' => 'pass');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['password'], $source->getPassword());
    }

    public function testGetDefaultPassword()
    {
        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(null, $source->getPassword());
    }

    public function testGetFinalUrl()
    {
        $output = m::mock('Symfony\Component\Console\Output\OutputInterface');
        $dialog = m::mock('Symfony\Component\Console\Helper\DialogHelper');

        $data = $this->baseConfiguration()  + ['password' => 'pass', 'username' => 'user'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(
            "https://{$data['username']}:{$data['password']}@github.com/onigoetz/deployer",
            $source->getFinalUrl($dialog, $output)
        );
    }

    public function testGetFinalUrlWithoutPassword()
    {
        $password = 'hey';

        $output = m::mock('Symfony\Component\Console\Output\OutputInterface');
        $dialog = m::mock('Symfony\Component\Console\Helper\DialogHelper');
        $dialog->shouldReceive('askHiddenResponse')->once()->andReturn($password);

        $data = $this->baseConfiguration()  + ['username' => 'user'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(
            "https://{$data['username']}:{$password}@github.com/onigoetz/deployer",
            $source->getFinalUrl($dialog, $output)
        );
    }

    public function testGetFinalUrlWithoutUsername()
    {
        $username = 'hey';

        $output = m::mock('Symfony\Component\Console\Output\OutputInterface');
        $dialog = m::mock('Symfony\Component\Console\Helper\DialogHelper');
        $dialog->shouldReceive('ask')->once()->andReturn($username);

        $data = $this->baseConfiguration()  + ['password' => 'pass'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(
            "https://{$username}:{$data['password']}@github.com/onigoetz/deployer",
            $source->getFinalUrl($dialog, $output)
        );
    }

    public function testGetFinalUrlComplete()
    {
        $final_path = 'https://user:pass@github.com/onigoetz/deployer';

        $output = m::mock('Symfony\Component\Console\Output\OutputInterface');
        $dialog = m::mock('Symfony\Component\Console\Helper\DialogHelper');
        $dialog->shouldReceive('ask')->never();
        $dialog->shouldReceive('askHiddenResponse')->never();

        $data = ['strategy' => 'clone', 'path' => $final_path];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($final_path, $source->getFinalUrl($dialog, $output));
    }

    public function testGetFinalUrlWithoutPasswordOnlyAskOnce()
    {
        $password = 'hey';

        $output = m::mock('Symfony\Component\Console\Output\OutputInterface');
        $dialog = m::mock('Symfony\Component\Console\Helper\DialogHelper');
        $dialog->shouldReceive('askHiddenResponse')->once()->andReturn($password);

        $data = $this->baseConfiguration()  + ['username' => 'user'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(
            "https://{$data['username']}:{$password}@github.com/onigoetz/deployer",
            $source->getFinalUrl($dialog, $output)
        );

        $this->assertEquals(
            "https://{$data['username']}:{$password}@github.com/onigoetz/deployer",
            $source->getFinalUrl($dialog, $output)
        );
    }

    public function testGetFinalUrlWithoutPasswordUserInPath()
    {
        $start_path = 'https://user@github.com/onigoetz/deployer';
        $password = 'hey';
        $final_path = "https://user:{$password}@github.com/onigoetz/deployer";

        $output = m::mock('Symfony\Component\Console\Output\OutputInterface');
        $dialog = m::mock('Symfony\Component\Console\Helper\DialogHelper');
        $dialog->shouldReceive('askHiddenResponse')->once()->andReturn($password);

        $data = ['strategy' => 'clone', 'path' => $start_path];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($final_path, $source->getFinalUrl($dialog, $output));
    }

    public function testGetStrategyFromParent()
    {
        $manager = $this->getManager();

        $data = array('strategy' => 'clone', 'path' => '/the/path');

        $parent = Source::make('parent', $data, $manager);
        $manager->set($parent);

        $child = Source::make('child', ['extends' => 'parent'], $manager);

        $this->assertEquals($data['strategy'], $child->getStrategy());
    }
}
