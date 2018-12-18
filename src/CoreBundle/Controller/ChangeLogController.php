<?php

namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\VarDumper\VarDumper;

class ChangeLogController extends Controller
{
    public function indexAction()
    {
        $changes = $this->get('version')->getCompiledCommits(20);
        return $this->render('@Core/ChangeLog/index.html.twig', ['changes'=>$changes]);
    }
}
