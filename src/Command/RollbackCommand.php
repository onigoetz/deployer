<?php

namespace Onigoetz\Deployer\Command;

use Net_SFTP;
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

        $environment = $this->getEnvironment($input->getArgument('to'), $output);

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
        $runner = new RemoteActionRunner($output, $ssh);

        $previous = trim($ssh->exec('cat ' . $environment->getDirectories()->getRoot() . '/previous'));

        if ($previous == '') {
            $output->writeln('<error>Cannot find previous file !!!</error>');

            return;
        }

        $actions = [
            'Removing the symlink of the release to rollback' => [
                'action' => 'rmfile',
                'file' => $environment->getDirectories()->getDeploy(),
            ],
            'Link it again to the snapshot ' . $previous => [
                'action' => 'symlink',
                'target' => $previous,
                'link_name' => $environment->getDirectories()->getDeploy(),
            ],
        ];

        $output->writeln('Previous snapshot : ' . $previous);
        $output->writeln("Reverting...\n", 'blue');
        $this->runActions($runner, $actions, $output, $environment->getSubstitutions($previous));
        $output->writeln("Done\n");
    }
}
