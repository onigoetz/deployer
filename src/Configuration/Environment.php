<?php

namespace Onigoetz\Deployer\Configuration;

use Onigoetz\Deployer\Configuration\Containers\ConfigurationContainer;

class Environment extends ConfigurationContainer
{
    /**
     * Find if there are some overrides for a configuration
     *
     * @param string $key
     * @return bool
     */
    protected function hasOverrides($key)
    {
        if (array_key_exists('overrides', $this->data) && array_key_exists($key, $this->data['overrides'])) {
            return true;
        }

        return false;
    }

    public function getSource()
    {
        $source = $this->getValueOrFail('source', 'no source specified');

        $resolvedSource = $this->manager->get('source', $source);

        if (!$this->hasOverrides('source')) {
            return $resolvedSource;
        }

        return Source::make('override', $this->data['overrides']['source'], $this->manager, $resolvedSource);
    }

    public function getServers()
    {
        $servers = $this->getValueOrFail('servers', 'no servers specified');

        $resolvedServers = [];
        foreach ($servers as $server) {
            $resolvedServers[] = $this->manager->get('server', $server);
        }

        return $resolvedServers;
    }

    public function getDirectories()
    {
        $directories = $this->manager->getDefaultDirectories();

        if (!$this->hasOverrides('directories')) {
            return $directories;
        }

        return new Directories('overriden', $this->data['overrides']['directories'], $this->manager, $directories);
    }

    public function getTasks($event)
    {
        $tasks = $this->getValueOrDefault('tasks', []);

        if (!array_key_exists($event, $tasks)) {
            return [];
        }

        $groups = $tasks[$event];
        $actions = [];

        foreach ($groups as $group) {
            /**
             * @var Tasks
             */
            $items = $this->manager->get('tasks', $group);
            foreach ($items->getTasks() as $name => $action) {
                if (is_numeric($name)) {
                    $actions[] = $action;
                    continue;
                }

                $actions[$name] = $action;
            }
        }

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function checkValidity()
    {
        if (!$this->getSource()->isValid()) {
            return false;
        }

        if (!$this->getDirectories()->isValid()) {
            return false;
        }

        /**
         * @var Server
         */
        foreach ($this->getServers() as $server) {
            if (!$server->isValid()) {
                return false;
            }
        }

        return true;
    }

    public function getSubstitutions($binary)
    {
        return [
            '{{root}}' => $this->getDirectories()->getRoot(),
            '{{binaries}}' => $this->getDirectories()->getBinaries(),
            '{{binary}}' => $binary,
            '{{deploy}}' => $this->getDirectories()->getDeploy(),
            '{{environment}}' => $this->name,
        ];
    }
}
