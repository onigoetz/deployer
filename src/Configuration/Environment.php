<?php

namespace Onigoetz\Deployer\Configuration;

use Onigoetz\Deployer\Configuration\Containers\ConfigurationContainer;

class Environment extends ConfigurationContainer
{
    /**
     * @param string $key
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

        $resolved_source = $this->manager->get('source', $source);

        if (!$this->hasOverrides('source')) {
            return $resolved_source;
        }

        return Source::make('override', $this->data['overrides']['source'], $this->manager, $resolved_source);
    }

    public function getServers()
    {
        $servers = $this->getValueOrFail('servers', 'no servers specified');

        $resolved_servers = array();
        foreach ($servers as $server) {
            $resolved_servers[] = $this->manager->get('server', $server);
        }

        return $resolved_servers;
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
        $tasks = $this->getValueOrDefault('tasks', array());

        if (!array_key_exists($event, $tasks)) {
            return array();
        }

        $groups = $tasks[$event];
        $actions = array();

        foreach ($groups as $group) {
            $items = $this->manager->get('tasks', $group);
            foreach ($items->getTasks() as $name => $action) {
                if (is_numeric($name)) {
                    $actions[] = $action;
                } else {
                    $actions[$name] = $action;
                }
            }
        }

        return $actions;
    }

    public function isValid()
    {
        try {
            if (!$this->getSource()->isValid()) {
                return false;
            }

            if (!$this->getDirectories()->isValid()) {
                return false;
            }

            foreach ($this->getServers() as $server) {
                if (!$server->isValid()) {
                    return false;
                }
            }
        } catch (\LogicException $e) {
            $this->manager->log($e->getMessage());
            return false;
        }

        return true;
    }
}
