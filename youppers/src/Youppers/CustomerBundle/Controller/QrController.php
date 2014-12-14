<?php

namespace Youppers\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class QrController extends Controller
{
    /**
     * @Route("/qr/{qr}")
     * @Template()
     */
    public function scanAction($qr)
    {
        return array('name' => $qr);
    }
}
