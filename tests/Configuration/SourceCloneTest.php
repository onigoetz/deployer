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
        return ['strategy' => 'clone', 'path' => 'https://github.com/onigoetz/deployer'];
    }

    public function testIsValid()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertTrue($source->isValid());
    }

    public function testGetBranch()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path', 'branch' => 'develop'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['branch'], $source->getBranch());
    }

    public function testGetBranchInherited()
    {
        $mgr = $this->getManager();

        $master_data = ['strategy' => 'clone', 'path' => '/main/path', 'branch' => 'develop'];
        $mgr->set(Source::make('master', $master_data, $mgr));

        $data = ['strategy' => 'clone', 'extends' => 'master'];
        $mgr->set($source = Source::make('apprentice', $data, $mgr));

        $this->assertEquals($master_data['branch'], $source->getBranch());
    }

    public function testGetTypeInherited()
    {
        $mgr = $this->getManager();

        $master_data = ['strategy' => 'clone', 'path' => '/main/path', 'branch' => 'master'];
        $mgr->set(Source::make('master', $master_data, $mgr));

        $data = ['branch' => 'develop', 'extends' => 'master'];
        $mgr->set($source = Source::make('apprentice', $data, $mgr));

        $this->assertInstanceOf('\Onigoetz\Deployer\Configuration\Sources\Cloned', $source);
    }

    public function testGetBranchInheritedLazyLoad()
    {
        $mgr = $this->getManager();

        // The point in this test is that the child is
        // declared before the configuration it extends
        $data = ['strategy' => 'clone', 'extends' => 'master'];
        $mgr->set($source = Source::make('apprentice', $data, $mgr));

        $master_data = ['strategy' => 'clone', 'path' => '/main/path', 'branch' => 'develop'];
        $mgr->set(Source::make('master', $master_data, $mgr));

        $this->assertEquals($master_data['branch'], $source->getBranch());
    }

    public function testGetDefaultBranch()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(Cloned::$defaultBranch, $source->getBranch());
    }

    public function testGetDefaultBranchInherited()
    {
        $mgr = $this->getManager();

        $master_data = ['strategy' => 'clone', 'path' => '/main/path'];
        $mgr->set(Source::make('master', $master_data, $mgr));

        $data = ['strategy' => 'clone', 'extends' => 'master'];
        $mgr->set($source = Source::make('apprentice', $data, $mgr));

        $this->assertEquals(Cloned::$defaultBranch, $source->getBranch());
    }

    public function testGetType()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path', 'type' => 'mercurial'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['type'], $source->getType());
    }

    public function testGetDefaultType()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(Cloned::$defaultType, $source->getType());
    }

    public function testGetSubmodules()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path', 'submodules' => true];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['submodules'], $source->getSubmodules());
    }

    public function testGetDefaultSubmodules()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(Cloned::$defaultSubmodules, $source->getSubmodules());
    }

    public function testGetUsername()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path', 'username' => 'root'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['username'], $source->getUsername());
    }

    public function testGetDefaultUsername()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals(null, $source->getUsername());
    }

    public function testGetPassword()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path', 'password' => 'pass'];

        $source = Source::make('noname', $data, $this->getManager());

        $this->assertEquals($data['password'], $source->getPassword());
    }

    public function testGetDefaultPassword()
    {
        $data = ['strategy' => 'clone', 'path' => '/the/path'];

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

        $data = ['strategy' => 'clone', 'path' => '/the/path'];

        $parent = Source::make('parent', $data, $manager);
        $manager->set($parent);

        $child = Source::make('child', ['extends' => 'parent'], $manager);

        $this->assertEquals($data['strategy'], $child->getStrategy());
    }
}
