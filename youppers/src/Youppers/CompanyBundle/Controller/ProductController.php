<?php

namespace Youppers\CompanyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp;

class ProductController extends Controller
{
    /**
     * @Route("/product/{id}")
     * @Template()
     */
    public function showAction($id)
    {
    	$product = $this->getDoctrine()
    	->getRepository('YouppersCompanyBundle:Product')
    	->find($id);
    	
    	if (!$product) {
    		// visualizza la pagina di "prodotto non trovato"
    		return new Response('Invalid product code');
    	}
    	 
    	$data['il1nm']= 'Product ' . $product;
    	
    	$tracker = $this->get('happyr.google.analytics.tracker');
    	$data=array(
    			'dl' => $this->getRequest()->getUri(),
    			'dt' => 'Show Product: ' . $product,
    			'ec' => 'Company',
    			'ea' => 'Product',
    			'el' => '' . $product,
    	);

    	$data['pa']= 'detail';
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
    	   	
    	$data['z'] = rand();
    	
        $logger = $this->get('logger');    	
    	$res = $tracker->send($data, 'event');
    	if ($res) {
    		$logger->info("Sent to GoogleAnalytics: " . var_export($data, true));
    	} else {
    		$logger->error("Failed sending to GoogleAnalytics: " . var_export($data, true));
    	}    	    	
    	    	 
    	return array('product' => $product);
    }
    
    
}
