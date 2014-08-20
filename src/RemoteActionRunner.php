<?php

namespace Onigoetz\Deployer;

use Net_SFTP;
use Symfony\Component\Console\Output\OutputInterface;

class RemoteActionRunner
{

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var Net_SFTP
     */
    protected $ssh;

    public function __construct(OutputInterface $output, Net_SFTP $ssh)
    {
        $this->output = $output;
        $this->ssh = $ssh;
    }

    public function exec($command)
    {
        if (VERBOSE) {
            $this->output->writeln("<bg=blue;options=bold>  -> $command </bg=blue;options=bold>");
        }
        return $this->ssh->exec($command);
    }

    public function symlink($target, $link_name)
    {
        return $this->exec('ln -s ' . $target . ' ' . $link_name);
    }

    public function rmfile($file)
    {
        return $this->exec('rm -f "' . $file . '"');
    }

    public function rmdir($file)
    {
        return $this->exec('rm -rf "' . $file . '"');
    }

    protected function getComposerCommand($dir)
    {
        //is composer installed on the system ?
        $composer_command = str_replace("\n", "", $this->ssh->exec('which composer'));
        if ($composer_command != '') {
            return $composer_command;
        }

        //is composer installed locally ?
        if (file_exists("$dir/composer.phar")) {
            return "$dir/composer.phar";
        }

        //if not install it locally
        $response = $this->exec('curl -s https://getcomposer.org/installer | php -- --install-dir="' . $dir . '"');

        $this->output->write('<fg=green>' . $response . '</fg=green>');

        return "$dir/composer.phar";
    }

    public function composer($dir)
    {
        $composer_command = $this->getComposerCommand($dir);

        $command = "$composer_command install --prefer-dist --optimize-autoloader --no-dev --no-interaction -d $dir";

        return $this->exec($command . ((VERBOSE) ? ' -v' : ''));
    }

    /**
     * Tests if a directory exists
     *
     * @param string $dir
     * @return boolean
     */
    public function isDir($dir)
    {
        $pwd = $this->ssh->pwd();

        if (!$this->ssh->chdir($dir)) {
            return false;
        }

        $this->ssh->chdir($pwd);

        return true;
    }

    public function getSymlinkDestination($folder)
    {
        //get previous deploy symlink
        $link = str_replace("\n", '', $this->ssh->exec("ls -la $folder"));

        //Store "previous" deploy
        return trim(substr($link, strpos($link, '->') + 3));
    }

    public function setupServer($destination_dir)
    {
        $this->output->writeln('Does the snapshots directory exist ?', 'blue');

        if (!$this->isDir($destination_dir)) {
            $this->output->writeln('<info> CREATING </info>');

            $this->exec('mkdir -p "' . $destination_dir . '"');

            if (!$this->isDir($destination_dir)) {
                throw new \Exception("Cannot create directory '$destination_dir'");
            }
        }
        $this->output->writeln('<info> OK </info>');
    }
}
