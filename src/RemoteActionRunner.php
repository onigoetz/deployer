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

    public function exec($command, $cwd = null)
    {
        if (VERBOSE) {
            $this->output->writeln("<bg=blue;options=bold>  -> $command </bg=blue;options=bold>");
        }

        if ($cwd) {
            $command = "cd \"$cwd\" && $command";
        }

        $result = $this->ssh->exec($command);

        if ($status = $this->ssh->getExitStatus()) {
            throw new RemoteException("Command '$command' Failed with status '$status': $result");
        }

        return $result;
    }

    public function symlink($target, $linkName)
    {
        return $this->exec('ln -s ' . $target . ' ' . $linkName);
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
        $composerCommand = str_replace("\n", '', $this->ssh->exec('which composer'));
        if ($composerCommand != '') {
            return $composerCommand;
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
        $composerCommand = $this->getComposerCommand($dir);

        $command = "$composerCommand install --prefer-dist --optimize-autoloader --no-dev --no-interaction -d $dir";

        return $this->exec($command . ((VERBOSE) ? ' -v' : ''));
    }

    /**
     * Tests if a directory exists
     *
     * @param string $dir
     * @return bool
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

    public function setupServer($destinationDir)
    {
        if (!$this->isDir($destinationDir)) {
            $this->exec('mkdir -p "' . $destinationDir . '"');

            if (!$this->isDir($destinationDir)) {
                throw new \Exception("Cannot create directory '$destinationDir'");
            }
        }
    }
}
