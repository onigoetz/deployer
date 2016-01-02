<?php

namespace Onigoetz\Deployer\Configuration\Sources;

use Onigoetz\Deployer\Configuration\Source;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

//the name of the class is "cloned" as "clone" is a reserved keyword
class Cloned extends Source
{
    public static $defaultType = 'git';
    public static $defaultBranch = 'master';
    public static $defaultSubmodules = false;

    public function getType()
    {
        return $this->getValueOrDefault('type', self::$defaultType);
    }

    public function getBranch()
    {
        return $this->getValueOrDefault('branch', self::$defaultBranch);
    }

    public function getSubmodules()
    {
        return $this->getValueOrDefault('submodules', self::$defaultSubmodules);
    }

    public function getUsername()
    {
        return $this->getValueOrDefault('username', null);
    }

    public function setUsername($username)
    {
        $this->data['username'] = $username;
    }

    public function getPassword()
    {
        return $this->getValueOrDefault('password', null);
    }

    public function setPassword($password)
    {
        $this->data['password'] = $password;
    }

    public function getFinalUrl(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output)
    {
        $regex = '/^((?P<scheme>https?):\\/)?\\/?((?P<username>.*?)(:(?P<password>.*?)|)@)?(?P<uri>.*)/';
        preg_match($regex, $this->getPath(), $matches);

        //username provided ?
        if ($matches['username']) {
            $username = $matches['username'];
        } elseif (!$username = $this->getUsername()) {
            $question = new Question('What is the repository username?');
            $username = $questionHelper->ask($input, $output, $question);
            $this->setUsername($username);
        }

        //password provided ?
        if ($matches['password']) {
            $password = $matches['password'];
        } elseif (!$password = $this->getPassword()) {
            $question = new Question('What is the repository password?', false);
            $question->setHidden(true)->setHiddenFallback(false);
            $password = $questionHelper->ask($input, $output, $question);
            $this->setPassword($password);
        }

        //HTTP(S)? username:password@
        return "{$matches['scheme']}://$username:$password@{$matches['uri']}";
    }
}
