<?php

namespace Youppers\DealerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Security\Core\Exception\DisabledException;

class BoxController extends Controller
{
	/**
	 * @Route("/box/list")
	 * @Template()
	 */
	public function listAction()
	{
		$criteria = array('enabled' => true);
		if ($storeId=$this->getRequest()->get("store")) {
			$store = $this->getDoctrine()
				->getRepository('YouppersDealerBundle:Store')
				->find($storeId);
			if ($store) {
				$criteria['store'] = $store;
			}
		}
		return $this->getDoctrine()
		->getRepository('YouppersDealerBundle:Box')
		->findBy($criteria,array('name' => 'ASC'));
	}
	
	/**
     * @Route("/box/{id}")
     * @Template()
     */
    public function showAction($id)
    {
    	$box = $this->getDoctrine()
    	->getRepository('YouppersDealerBundle:Box')
    	->find($id);
    	
    	if (!$box) {
    		throw $this->createNotFoundException('Invalid box code (not found)');
    	}
    	
    	if (!$box->getEnabled() && false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
    		throw $this->createAccessDeniedException('Disabled Box is only allowed to admin',new DisabledException('Disabled Box'));
    	}

    	return array('box' => $box);    	 
    }

    /**
     * @Route("/box-product/{id}")
     * @Template()
     */
    public function productAction($id)
    {
    	$boxProduct = $this->getDoctrine()
    	->getRepository('YouppersDealerBundle:BoxProduct')
    	->find($id);
    	
    	if (!$boxProduct) {
    		// visualizza la pagina di "prodotto non trovato"
    		return new Response('Invalid box product code');    		
    	} else {
    		$product = $boxProduct->getProduct();
    	}

    	return array('boxProduct' => $boxProduct);
    }
    
}
