<?php

namespace Youppers\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Youppers\CompanyBundle\Entity\ProductModel;
use Symfony\Component\HttpFoundation\Response;

class QrController extends Controller
{
    /**
     * @Route("/qr/box/{qr}")
     * 
     * cerca il box
     */
    public function boxAction($qr)
    {
    	
    	$a = explode('-',$qr);
    	if (count($a) != 3) {
    		return new Response('Invalid QR code format');
    	}
    	// gestisce le eccezioni per gli utenti che non usano l'app
    	    	
    	
    	$dealer = $this->getDoctrine()
    		->getRepository('YouppersDealerBundle:Dealer')
    		->findOneBy(array('code' => $a[0]));
    	if ($dealer == null) {
    		return new Response('Invalid QR code, dealer not found');    		
    	}

    	$store = $this->getDoctrine()
    		->getRepository('YouppersDealerBundle:Store')
    		->findOneBy(array('dealer' => $dealer, 'code' => $a[1]));
    	if ($store == null) {
    		return new Response('Invalid QR code, store not found');
    	}
    	     	
    	$box = $this->getDoctrine()
    		->getRepository('YouppersDealerBundle:Box')
    		->findOneBy(array('store' => $store, 'code' => $a[2]));
    	
    	if ($box) {
    		// visualizza la pagina del box
    		return $this->redirectToRoute("youppers_dealer_box_show",array("id" => $box->getId()));
    	}

    	// visualizza la pagina di "QR non trovato"
        return new Response('Invalid QR code, box not found');
    }
    
    /**
     * @Route("/qr/prod/{qr}")
     * 
     * cerca il prodotto
     */    
    public function prodAction($qr) {
    	$a = explode('-',$qr);
    	if (count($a) != 3) {
    		return new Response('Invalid QR code format');
    	}
    	// gestisce le eccezioni per gli utenti che non usano l'app
    	    	 
    	$company = $this->getDoctrine()
    	->getRepository('YouppersCompanyBundle:Company')
    	->findOneBy(array('code' => $a[0]));
    	if ($company == null) {
    		return new Response('Invalid QR code, company not found');
    	}
    	
    	$brand = $this->getDoctrine()
    	->getRepository('YouppersCompanyBundle:Brand')
    	->findOneBy(array('company' => $company, 'code' => $a[1]));
    	if ($brand == null) {
    		return new Response('Invalid QR code, brand not found');
    	}
    	 
    	$product = $this->getDoctrine()
    	->getRepository('YouppersCompanyBundle:Product')
    	->findOneBy(array('brand' => $brand, 'code' => $a[2]));

    	if ($product) {
    		// visualizza la pagina del prodotto
    		return $this->redirectToRoute("youppers_company_product_show",array("id" => $product->getId()));
    	}
    	
    	// visualizza la pagina di "QR non trovato"
    	return new Response('Invalid QR code, product not found');    	 
    }
}
