<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 20:50
 */

namespace Onigoetz\Deployer\Configuration\Containers;

use Onigoetz\Deployer\Configuration\ConfigurationManager;

abstract class ExtendableConfigurationContainer extends InheritingConfigurationContainer
{
    /**
     * @var ConfigurationManager
     */
    protected $manager;

    public function __construct(array $data, ConfigurationManager $manager)
    {
        parent::__construct($data, null);

        $this->manager = $manager;
    }

    protected function loadParent()
    {
        if (array_key_exists('extends', $this->data)) {
            $this->parent = $this->manager->get(strtolower(get_class($this)), $this->data['extends']);
        }
    }

    protected function getValueOrFail($key, $error_message)
    {
        $this->loadParent();
        return parent::getValueOrFail($key, $error_message);
    }

    protected function getValueOrDefault($key, $default)
    {
        $this->loadParent();

        return parent::getValueOrDefault($key, $default);
    }
}
