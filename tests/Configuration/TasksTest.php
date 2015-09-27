<?php

use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Tasks;

class TasksTest extends PHPUnit_Framework_TestCase
{
    protected function getManager()
    {
        return new ConfigurationManager();
    }

    public function testGetTasks()
    {
        $data = ['prune' => ['action' => 'prune']];

        $task = new Tasks('prune', $data, $this->getManager());

        $this->assertEquals($data, $task->getTasks());
    }

    public function testIsValid()
    {
        $data = ['prune' => ['action' => 'prune']];

        $task = new Tasks('prune', $data, $this->getManager());

        $this->assertTrue($task->isValid());
    }
}
