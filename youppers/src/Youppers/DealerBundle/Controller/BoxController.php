<?php

namespace Youppers\DealerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp;
use Symfony\Component\Stopwatch\Stopwatch;

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
    		// visualizza la pagina di "prodotto non trovato"
    		return new Response('Invalid box code');
    	}
    	 
    	$tracker = $this->get('happyr.google.analytics.tracker');
    	$data=array(
    			'dl' => $this->getRequest()->getUri(),
    			'dt'=>'Show Box: ' . $box,
    			'ec' => 'Box',
    			'ea' => 'Show',
    			'el' => '' . $box,    			 
    	);
    	
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
    	$res = $tracker->send($data, 'event');
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

    	/*
    	 * v=1                                  // Version.
&tid=UA-XXXX-Y                       // Tracking ID / Property ID.
&cid=555                             // Anonymous Client ID.
&t=event                             // Event hit type.
&ec=UX                               // Event Category. Required.
&ea=click                            // Event Action. Required.
&el=Results                          // Event label.

&pa=click                            // Product action (click). Required.
&pal=Search%20Results                // Product Action List.
&pr1id=P12345                        // Product 1 ID. Either ID or name must be set.
&pr1nm=Android%20Warhol%20T-Shirt    // Product 1 name. Either ID or name must be set.
&pr1ca=Apparel                       // Product 1 category.
&pr1br=Google                        // Product 1 brand.
&pr1va=Black                         // Product 1 variant.
&pr1ps=1                             // Product 1 position.
    	 */
    	
    	$tracker = $this->get('happyr.google.analytics.tracker');
    	$data=array(
    			'dl' => $this->getRequest()->getUri(),
    			'dt' => 'Show Box Product: ' . $boxProduct,
    			'ec' => 'Box',
    			'ea' => 'Product',
    			'el' => '' . $boxProduct,
    	);

    	$data['pa']= 'detail';
    	$data['pr1ps'] = $boxProduct->getPosition();
    	$data['il1pi1ps'] = $boxProduct->getPosition();
    	if ($product = $boxProduct->getProduct()) {
			$data['pr1id'] = $product->getId();
    		$data['pr1nm'] = $product->getName();
    		// add category
    		// add variant
    	    $data['il1pi1id'] = $product->getId();
    		$data['il1pi1nm'] = $product->getName();
    		if ($brand = $product->getBrand()) {
    			$data['pr1br'] = $brand->getName();    			     			
    			$data['il1pi1br'] = $brand->getName();
    		}
    		// add category
    		// add variant
    	} else {
    		$data['pr1nm'] = $boxProduct->getName();
    		$data['il1pi1nm'] = $boxProduct->getName();
    	}
    	    	
    	$data['z'] = rand();
    	
        $logger = $this->get('logger');    	
    	$stopwatch = new Stopwatch();
    	$stopwatch->start('GoogleAnalytics');
    	$res = $tracker->send($data, 'event');
    	$event = $stopwatch->stop('GoogleAnalytics');
        if ($res) {
    		$logger->info("Sent event to GoogleAnalytics: " . $event->getDuration() . "mS " . var_export($data, true));
        } else {
    		$logger->error("Failed sending event to GoogleAnalytics: " . var_export($data, true));
    	}    	    	
    	 
    	return array('boxProduct' => $boxProduct, 'scraping' => $this->get('youppers.scraper')->products($product->getBrand()->getCode(),$product->getCode()));
    }
    
    
}
