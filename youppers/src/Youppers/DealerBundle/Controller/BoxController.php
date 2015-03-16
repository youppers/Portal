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

    	$tracker = $this->get('happyr.google.analytics.tracker');
    	$data=array(
    			't' => 'event',
    			'dp' => $this->getRequest()->getPathInfo(),
    			'ds' => 'server',
    			'dt'=>'Show Box: ' . $box,
    			'ec' => 'Server',
    			'ea' => 'Box Show',
    			'el' => '' . $box
    	);
    	
    	// use dealer geoid
    	$data['geoid']=1008736; // Rome,"Rome,Rome,Lazio,Italy",9053431,IT,City
    	
    	// Product Impression List Name
    	$data['il1nm']= 'Box ' . $box;
    	
    	$boxProducts = $box->getBoxProducts();
    	
    	if ($boxProducts) {
    		$productIndex = 0;
    		foreach ($boxProducts as $boxProduct) {
    			$productIndex++;
    			$data['il1pi' . $productIndex . 'ps'] = $boxProduct->getPosition();    			 
   		    	if ($product = $boxProduct->getProduct()) {
		    		$data['il1pi' . $productIndex . 'id'] = $product->getId();
		    		$data['il1pi' . $productIndex . 'nm'] = $product->getName();
   		    	    if ($brand = $product->getBrand()) {
    					$data['il1pi' . $productIndex . 'br'] = $brand->getName();
    				}
    				// add category
    				// add variant    				
   		    	} else {
		    		$data['il1pi' . $productIndex . 'nm'] = $boxProduct->getName();
		    	}
    		}
    	}
    	
    	$data['z'] = rand();
    	     	
    	$logger = $this->get('logger');
    	$stopwatch = new Stopwatch();
    	$stopwatch->start('GoogleAnalytics');
    	$res = $tracker->send($data);
    	$event = $stopwatch->stop('GoogleAnalytics');
    	if ($res) {
    		$logger->info("Sent event to GoogleAnalytics: " . $event->getDuration() . "mS " . var_export($data, true));
    	} else {
    		$logger->error("Failed sending event to GoogleAnalytics: " . var_export($data, true));
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
    	}

    	$tracker = $this->get('happyr.google.analytics.tracker');
    	$data=array(
    			't' => 'event',
    			'dp' => $this->getRequest()->getPathInfo(),
    			'ds' => 'server',
    			'dt' => 'Show Box Product: ' . $boxProduct,
    			'ec' => 'Server',
    			'ea' => 'Box Product',
    			'el' => '' . $boxProduct,
    	);

    	// use dealer geoid
    	$data['geoid']=1008736; // Rome,"Rome,Rome,Lazio,Italy",9053431,IT,City

    	// Product Action
    	$data['pa']= 'detail';
    	
    	// Product Position
    	$data['pr1ps'] = $boxProduct->getPosition();
    	
    	//$data['pal'] = 'Box Show';
    	//$data['il1pi1ps'] = $boxProduct->getPosition();
    	if ($product = $boxProduct->getProduct()) {
			$data['pr1id'] = $product->getId();
    		$data['pr1nm'] = $product->getName();
    		// add category
    		// add variant
    	    //$data['il1pi1id'] = $product->getId();
    		//$data['il1pi1nm'] = $product->getName();
    		if ($brand = $product->getBrand()) {
    			$data['pr1br'] = $brand->getName();    			     			
    			//$data['il1pi1br'] = $brand->getName();
    		}
    		// add category
    		// add variant
    	} else {
    		$data['pr1nm'] = $boxProduct->getName();
    		//$data['il1pi1nm'] = $boxProduct->getName();
    	}
    	    	
    	$data['z'] = rand();
    	
        $logger = $this->get('logger');    	
    	$stopwatch = new Stopwatch();
    	$stopwatch->start('GoogleAnalytics');
    	$res = $tracker->send($data);
    	$event = $stopwatch->stop('GoogleAnalytics');
        if ($res) {
    		$logger->info("Sent event to GoogleAnalytics: " . $event->getDuration() . "mS " . var_export($data, true));
        } else {
    		$logger->error("Failed sending event to GoogleAnalytics: " . var_export($data, true));
    	}    	    	
    	 
    	return array('boxProduct' => $boxProduct, 'scraping' => $this->get('youppers.scraper')->products($product->getBrand()->getCode(),$product->getCode()));
    }
    
    
}
