<?php

namespace Youppers\DealerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class BoxController extends Controller
{
    /**
     * @Route("/box/{id}/show")
     * @Template()
     */
    public function showAction($id)
    {
    	$box = $this->getDoctrine()
    	->getRepository('YouppersDealerBundle:Box')
    	->find($id);

    	return array('box' => $box);    	 
    }

    /**
     * @Route("/box/{id}/product/{childId}/show")
     * @Template()
     */
    public function productAction($id,$childId)
    {
    	$boxProduct = $this->getDoctrine()
    	->getRepository('YouppersDealerBundle:BoxProduct')
    	->find($childId);

    	return array('boxProduct' => $boxProduct);
    }
    
    
}
