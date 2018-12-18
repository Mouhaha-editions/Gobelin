<?php

namespace DashboardBundle\Controller;

use DateTime;
use Doctrine\DBAL\Types\DateTimeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\VarDumper\VarDumper;

class DefaultController extends Controller
{
    public function indexAction()
    {

        return $this->redirectToRoute('bank_account_index');

    }
}
