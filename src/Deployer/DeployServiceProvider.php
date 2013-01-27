<?php namespace Deployer;

use Illuminate\Support\ServiceProvider;
use Deployer\Command\DeployCommand;
use Deployer\Command\RollbackCommand;

class DeployServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
            $this->app['command.deployer.deploy'] = $this->app->share(function($app)
            {
                    Init::bootstrap($app['config']['deploy']);

                    return new DeployCommand();
            });
            
            $this->app['command.deployer.rollback'] = $this->app->share(function($app)
            {
                    Init::bootstrap($app['config']['deploy']);

                    return new RollbackCommand();
            });

            $this->commands('command.deployer.deploy', 'command.deployer.rollback');
        }
        
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
            return array('command.deployer.deploy', 'command.deployer.rollback');
	}
}