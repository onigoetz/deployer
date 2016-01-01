<?php

namespace Onigoetz\Deployer\Command;

use Net_SFTP;
use Onigoetz\Deployer\Configuration\Environment;
use Onigoetz\Deployer\Configuration\Sources\Cloned;
use Onigoetz\Deployer\Configuration\Sources\Upload;
use Onigoetz\Deployer\RemoteActionRunner;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('server:deploy')
            ->setDescription('Deploy the latest release')
            ->addArgument('to', InputArgument::REQUIRED, 'The environment to deploy to');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!defined('VERBOSE')) {
            define('VERBOSE', $input->getOption('verbose'));
        }

        $environment = $this->getEnvironment($input->getArgument('to'), $output);

        //Prepares the binary name
        $dest = $environment->getDirectories()->getNewBinaryName();

        $output->writeln('This binary\'s name will be : ' . $dest);

        if ($environment->getSource() instanceof Upload) {
            //TODO :: prepare build
        }

        $this->allServers(
            $environment,
            $output,
            function ($ssh) use ($environment, $output, $dest) {
                $this->deploy($output, $ssh, $environment, $dest);
            }
        );
    }

    protected function deploy(OutputInterface $output, Net_SFTP $ssh, Environment $environment, $destination)
    {
        $destination_dir = dirname($destination);

        $runner = new RemoteActionRunner($output, $ssh);

        $this->runAction(
            'Create folders on server',
            $output,
            function () use ($runner, $destination_dir) {
                $runner->setupServer($destination_dir);
            }
        );

        if ($environment->getSource() instanceof Cloned) {
            $class = 'Onigoetz\\Deployer\\SCM\\' . ucfirst($environment->getSource()->getType());
            if (!class_exists($class)) {
                throw new \Exception("Cannot find SCM '$class'");
            }

            /**
             * @var \Onigoetz\Deployer\SCM\SCM
             */
            $scm = new $class($environment);

            $final = $environment->getSource()->getFinalUrl($this->getHelper('dialog'), $output);

            $this->runAction(
                'Clone the latest version',
                $output,
                function () use ($scm, $ssh, $runner, $final, $destination) {
                    $git = $scm->getCommand($ssh);

                    return $runner->exec($scm->cloneCommand($git, $final, $destination));
                }
            );
        }

        $substitutions = $environment->getSubstitutions($destination);

        $output->writeln('<fg=blue;options=bold>Before deployment actions</fg=blue;options=bold>');
        $this->runActions($runner, $environment->getTasks('before'), $output, $substitutions);

        $output->writeln('<fg=blue;options=bold>Deployment</fg=blue;options=bold>');
        $this->runAction(
            'Store the current deployment for eventual rollback',
            $output,
            function () use ($runner, $environment, $ssh) {
                $previous = $runner->getSymlinkDestination($environment->getDirectories()->getDeploy());
                $ssh->put($environment->getDirectories()->getRoot() . '/previous', $previous);

                return "Previous snapshot : $previous";
            }
        );

        $this->runAction(
            'Symlink the new deployment',
            $output,
            function () use ($environment, $runner, $destination) {
                $deploy = $environment->getDirectories()->getDeploy();

                $runner->rmfile($deploy);
                $runner->symlink($destination, $deploy);
            }
        );

        $output->writeln('');
        $output->writeln('<fg=blue;options=bold>After deployment actions</fg=blue;options=bold>');
        $this->runActions($runner, $environment->getTasks('after'), $output, $substitutions);

        $output->writeln('Done');
    }
}
