<?php

namespace Onigoetz\Deployer\Configuration;

use Onigoetz\Deployer\Configuration\Containers\ExtendableConfigurationContainer;
use Onigoetz\Deployer\Configuration\Sources\Cloned;
use Onigoetz\Deployer\Configuration\Sources\Upload;

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
        $strategy = static::findStrategy($name, $data, $manager, $parent);

        switch ($strategy) {
            case 'clone':
                return new Cloned($name, $data, $manager, $parent);
                break;
            case 'upload':
                return new Upload($name, $data, $manager, $parent);
                break;
            default:
        }

        throw new \LogicException("Unrecognized strategy '{$strategy}'");

    }

    protected static function findStrategy($name, $data, ConfigurationManager $manager, Source $parent = null)
    {
        if (isset($data['strategy'])) {
            return $data['strategy'];
        }

        if ($parent) {
            return $parent->getStrategy();
        }

        if (isset($data['extends']) && $manager->has('source', $data['extends'])) {
            return $manager->get('source', $data['extends'])->getStrategy();
        }

        throw new \LogicException("Cannot find a valid strategy for the source '$name'");
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
    public function checkValidity()
    {
        $this->getPath();

        return true;
    }
}
