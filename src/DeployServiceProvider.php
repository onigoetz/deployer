<?php

namespace Onigoetz\Deployer;

use Illuminate\Support\ServiceProvider;
use Onigoetz\Deployer\Command\DeployCommand;
use Onigoetz\Deployer\Command\RollbackCommand;
use Onigoetz\Deployer\Configuration\ConfigurationManager;

class DeployServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('onigoetz/deployer');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['deployer.configuration'] = $this->app->share(
            function ($app) {

                $configuration = array(
                    'directories' => $app['config']['deployer']['directories'],
                    'servers' => $app['config']['deployer']['servers'],
                    'sources' => $app['config']['deployer']['sources'],
                    'tasks' => $app['config']['deployer']['tasks'],
                    'environments' => $app['config']['deployer']['environments'],
                );

                return ConfigurationManager::create($configuration);
            }
        );

        $this->app['command.deployer.deploy'] = $this->app->share(
            function ($app) {
                return new DeployCommand($app['deployer.configuration']);
            }
        );

        $this->app['command.deployer.rollback'] = $this->app->share(
            function ($app) {
                return new RollbackCommand($app['deployer.configuration']);
            }
        );

        $this->commands('command.deployer.deploy', 'command.deployer.rollback');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return array('command.deployer.deploy', 'command.deployer.rollback');
    }
}
