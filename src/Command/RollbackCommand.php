<?php

namespace Onigoetz\Deployer\Command;

use Net_SFTP;
use Onigoetz\Deployer\Actions;
use Onigoetz\Deployer\Configuration\Environment;
use Onigoetz\Deployer\RemoteActionRunner;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RollbackCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('server:rollback')
            ->setDescription('Rollback to the release before')
            ->addArgument('to', InputArgument::REQUIRED, 'The environment to rollback');
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

        $this->allServers(
            $environment,
            $output,
            function ($ssh) use ($environment, $output) {
                $this->rollback($output, $ssh, $environment);
            }
        );
    }

    protected function rollback(OutputInterface $output, Net_SFTP $ssh, Environment $environment)
    {
        $dirs = $environment->getDirectories();
        $runner = new RemoteActionRunner($output, $ssh);

        $previous = trim($ssh->exec('cat ' . $dirs->getRoot() . '/previous'));
        if ($previous != '') {
            $actions = array(
                'Removing the symlink of the release to rollback' => array(
                    'action' => 'rmfile',
                    'file' => $dirs->getDeploy()
                ),
                'Link it again to the snapshot ' . $previous => array(
                    'action' => 'symlink',
                    'target' => $previous,
                    'link_name' => $dirs->getDeploy()
                )
            );

            $output->writeln('Previous snapshot : ' . $previous);
            $output->writeln("Reverting...\n", 'blue');
            $this->runActions($runner, $actions, $output, $dirs->getSubstitutions($previous));
            $output->writeln("Done\n");
        } else {
            $output->writeln('<error>Cannot find previous file !!!</error>');
        }
    }
}
