<?php

namespace CoreBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\VarDumper\VarDumper;

class VersionService
{

    public static function get()
    {
        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));
        $version = trim(exec('git describe --abbrev=0 --tags'));

        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));

        return sprintf('%s-dev.%s (%s)', $version, $commitHash, $commitDate->format('Y-m-d H:i:s'));
    }

    public static function getProd()
    {
        $version = trim(exec('git describe --abbrev=0 --tags'));

        return sprintf('%s', $version);
    }

    public function getCompiledCommits($count)
    {
        $commits = [];
        while (count($commits) < $count) {
            $commits[] = trim(nl2br(exec('git log  -n ' . (count($commits) + 1) . ' --pretty=format:"%ci|%s" ')));
        }
        $commits = array_unique($commits);
        return $this->formatCommits($commits);

    }

    private function formatCommits($commits)
    {
        $ComObj = new ArrayCollection();

        $lastCom = '';
        foreach ($commits AS $i => $com) {
            $tmp = new Commit($com);
            if ($tmp->message == $lastCom) {
                continue;
            }
            $lastCom = $tmp->message;
            $ComObj->add($tmp);
        }
        return $ComObj;
    }


}

class Commit
{
    /** @var \DateTime */
    public $date;

    /** @var $message */
    public $message;

    public function __construct($brut)

    {
        $com = explode('|', $brut);
        $this->date = new \DateTime($com[0]);
        $this->message = $com[1];

        $this->message = str_replace('- ',"<span class='label label-danger'>[suppression]</span> ", $this->message);
        $this->message = str_replace('* ',"<span class='label label-warning'>[correction]</span> ", $this->message);
        $this->message = str_replace('+ ',"<span class='label label-success'>[ajout]</span> ", $this->message);
        $this->message = preg_replace('#(.+)<span(.*)#isU',"$1<br/><span$2", $this->message);

    }


}