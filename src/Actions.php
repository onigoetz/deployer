<?php
namespace Onigoetz\Deployer;

class Actions
{

    protected static $replacement_dirs;

    /**
     * The ssh tunnel
     *
     * @var Extensions\phpseclib\Net\SFTP
     */
    protected static $ssh;

    /**
     * The output stream
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected static $output;

    public static function setDirectories($directories)
    {
        $dirs = array();
        foreach ($directories as $key => $dir) {
            $dirs['${' . $key . '}'] = $dir;
        }

        self::$replacement_dirs = $dirs;
    }

    public static function prepareDir($dir)
    {
        $dir = strtr($dir, self::$replacement_dirs);

        return str_replace(' ', '\ ', prepare_directory($dir, self::$replacement_dirs['${base_dir}']));
    }

    public static function runActions($actions)
    {
        self::$ssh = Registry::get('ssh');
        self::$output = Registry::get('output');

        foreach ($actions as $action) {
            if (array_key_exists('description', $action)) {
                self::$output->writeln($action['description']);
                unset($action['description']);
            }

            $action_name = current($action);
            $parameters = array_slice($action, 1);

            $parameters = array_map(array(__CLASS__, 'prepareDir'), $parameters);

            $response = call_user_func_array(array(__CLASS__, $action_name), $parameters);
            self::$output->writeln('<fg=green>' . $response . '</fg=green>');
        }
    }

    public static function action($dir)
    {
        $command = 'rm -Rf "' . $dir . '"';
        if (VERBOSE) {
            self::$output->writeln("<bg=blue;options=bold>  -> $command </bg=blue;options=bold>");
        }
        return self::$ssh->exec($command);
    }

    public static function symlink($target, $link_name)
    {
        $command = 'ln -s ' . $target . ' ' . $link_name;
        if (VERBOSE) {
            self::$output->writeln("<bg=blue;options=bold>  -> $command </bg=blue;options=bold>");
        }

        return self::$ssh->exec($command);
    }

    public static function rmfile($file)
    {
        $command = 'rm -f "' . $file . '"';
        if (VERBOSE) {
            self::$output->writeln("<bg=blue;options=bold>  -> $command </bg=blue;options=bold>");
        }

        return self::$ssh->exec($command);
    }

    public static function rmdir($file)
    {
        $command = 'rm -rf "' . $file . '"';
        if (VERBOSE) {
            self::$output->writeln("<bg=blue;options=bold>  -> $command </bg=blue;options=bold>");
        }

        return self::$ssh->exec($command);
    }

    protected static function getComposerCommand($dir)
    {
        //is composer installed on the system ?
        $composer_command = str_replace("\n", "", self::$ssh->exec('which composer'));
        if ($composer_command != '') {
            return $composer_command;
        }

        //is composer installed locally ?
        if (file_exists($dir . '/composer.phar')) {
            return "$dir/composer.phar";
        }

        //if not install it locally
        $command = 'curl -s https://getcomposer.org/installer | php -- --install-dir="' . $dir . '"';
        if (VERBOSE) {
            self::$output->writeln("<bg=blue;options=bold>  -> $command </bg=blue;options=bold>");
        }
        $response = self::$ssh->exec($command);

        self::$output->write('<fg=green>' . $response . '</fg=green>');

        return "$dir/composer.phar";
    }

    public static function composer($dir)
    {
        $composer_command = self::getComposerCommand($dir);

        $command = "$composer_command install --prefer-dist --optimize-autoloader --no-dev --no-interaction -d $dir";

        if (VERBOSE) {
            $command .= ' -v';
            self::$output->writeln("<bg=blue;options=bold>  -> $command </bg=blue;options=bold>");
        }
        return self::$ssh->exec($command);
    }

    //TODO :: finish pruning
    public static function prune()
    {

    }
}
