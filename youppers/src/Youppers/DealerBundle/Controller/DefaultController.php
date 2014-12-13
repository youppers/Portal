<?php

namespace Youppers\DealerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/qr/{qr}")
     * @Template()
     */
    public function indexAction($qr)
    {
        return array('name' => $qr);
    }
}
