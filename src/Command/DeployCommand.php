<?php

namespace Onigoetz\Deployer\Command;

use Net_SFTP;
use Onigoetz\Deployer\Actions;
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

        $env = $input->getArgument('to');

        try {
            /**
             * @var Environment
             */
            $environment = $this->manager->get('environment', $env);
        } catch (\LogicException $e) {
            $output->writeln('<error>Environnement not available</error>');
            exit(self::EXIT_CODE_ENVIRONMENT_NOT_AVAILABLE);
        }

        if (!$environment->isValid()) {
            foreach ($this->manager->getLogs() as $line) {
                $output->writeln("<error>$line</error>");
            }
            exit(self::EXIT_CODE_INVALID_CONFIGURATION);
        }

        //Prepares the snapshot name
        $dirs = $environment->getDirectories();

        $dest = $dirs->getNewBinaryName();

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

        $runner->setupServer($destination_dir);

        if ($environment->getSource() instanceof Cloned) {
            $class = 'Onigoetz\\Deployer\\SCM\\' . ucfirst($environment->getSource()->getType());
            if (!class_exists($class)) {
                $output->writeln('<error> Cannot find SCM: ' . $class . ' </error>');
                exit(2);
            }

            /**
             * @var /Onigoetz/Deployer/SCM/SCM
             */
            $scm = new $class($environment);

            $output->writeln('<info>Cloning ...</info>');
            $final = $environment->getSource()->getFinalUrl($this->getHelper('dialog'), $output);

            $git = $scm->getCommand($ssh);

            $result = $runner->exec($scm->cloneCommand($git, $final, $destination));
            $output->writeln('<info>' . $result . '</info>');
        }

        $dirs = $environment->getDirectories();

        /*
         * Before Deploy Section
         */
        $output->writeln('Before deploy actions');
        $this->runActions($runner, $environment->getTasks('before'), $output, $dirs->getSubstitutions($destination));

        $previous = $runner->getSymlinkDestination($environment->getDirectories()->getDeploy());

        if ($previous != '') {
            $output->writeln("Previous snapshot : $previous");
            $ssh->put($dirs->getRoot() . '/previous', $previous);
        }

        $deploy = $dirs->getDeploy();

        //Symlink the folder
        $output->writeln('Deploy');
        $output->writeln('<info>' . $runner->rmfile($deploy) . '</info>');
        $output->writeln('<info>' . $runner->symlink($destination, $deploy) . '</info>');

        //After deploy
        $output->writeln('After deploy actions');
        $this->runActions($runner, $environment->getTasks('after'), $output, $dirs->getSubstitutions($destination));

        $output->writeln('Done');
    }
}
