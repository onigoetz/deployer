<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 21:50
 */

namespace Onigoetz\Deployer\Configuration;


use Onigoetz\Deployer\Configuration\Sources\Cloned;
use Onigoetz\Deployer\Configuration\Sources\Upload;

class Source extends ExtendableConfigurationContainer
{
    public static function make($data, ConfigurationManager $manager)
    {
        if (!array_key_exists('strategy', $data)) {
            throw new \LogicException('no strategy specified for this source');
        }

        switch ($data['strategy']) {
            case 'clone':
                return new Cloned($data, $manager);
                break;
            case 'upload':
                return new Upload($data, $manager);
                break;
            default:
        }

        throw new \LogicException("Unrecognized strategy '{$data['strategy']}'");
    }

    public function getPath()
    {
        return $this->getValueOrFail('path', 'path not found');
    }

    public function isValid()
    {
        try {
            $this->getPath();
        } catch (\LogicException $e) {
            return false;
        }

        return true;

    }
}
