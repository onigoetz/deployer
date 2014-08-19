<?php

namespace Onigoetz\Deployer\Configuration;

use Onigoetz\Deployer\Configuration\Containers\ExtendableConfigurationContainer;
use Onigoetz\Deployer\Configuration\Sources\Cloned;
use Onigoetz\Deployer\Configuration\Sources\Upload;
use Symfony\Component\Console\Output\OutputInterface;

class Source extends ExtendableConfigurationContainer
{
    /**
     * Create a source with the right class
     *
     * @param string $name The source name
     * @param array $data
     * @param ConfigurationManager $manager
     * @param Source $parent
     * @return Cloned|Upload
     * @throws \LogicException
     */
    public static function make($name, array $data, ConfigurationManager $manager, Source $parent = null)
    {
        if (!array_key_exists('strategy', $data) && !array_key_exists('extends', $data) && !$parent) {
            throw new \LogicException('no strategy specified for this source');
        }

        if ($parent || (array_key_exists('extends', $data) && $parent = $manager->get('source', $data['extends']))) {
            $strategy = $parent->getStrategy();
        } else {
            $strategy = $data['strategy'];
        }

        switch ($strategy) {
            case 'clone':
                return new Cloned($name, $data, $manager, $parent);
                break;
            case 'upload':
                return new Upload($name, $data, $manager, $parent);
                break;
            default:
        }

        throw new \LogicException("Unrecognized strategy '{$data['strategy']}'");
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerType()
    {
        return 'source';
    }

    public function getStrategy()
    {
        return $this->getValueOrFail('strategy', "no 'strategy' specified for source '{$this->name}''");
    }

    /**
     * @return mixed
     * @throws \LogicException
     */
    public function getPath()
    {
        return $this->getValueOrFail('path', "no 'path' specified for source '{$this->name}''");
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        try {
            $this->getPath();
        } catch (\LogicException $e) {
            $this->manager->log($e->getMessage());
            return false;
        }

        return true;
    }
}
