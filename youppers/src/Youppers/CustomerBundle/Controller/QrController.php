<?php

namespace Youppers\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Youppers\CompanyBundle\Entity\ProductModel;
use Symfony\Component\HttpFoundation\Response;

class QrController extends Controller
{
    /**
     * @Route("/qr/{qr}")
     */
    public function scanAction($qr)
    {
    	
    	// gestisce le eccezioni per gli utenti che non usano l'app
    	
    	
    	// carca il box
    	$box = $this->getDoctrine()
    		->getRepository('YouppersDealerBundle:Box')
    		->find($qr);
    	
    	if ($box) {
    		// visualizza la pagina del box
    		return $this->redirectToRoute("youppers_dealer_box_show",array("id" => $box->getId()));
    	}

    	// carca il prodotto
    	$product = $this->getDoctrine()
    	->getRepository('YouppersCompanyBundle:Product')
    	->find($qr);
    	 
    	if ($product) {
    		// visualizza la pagina del prodotto
    		return $this->redirectToRoute("youppers_customer_product_show",array("id" => $product->getId()));
    	}

    	// visualizza la pagina di "QR non trovato"
        return new Response('Invalid QR code');
    }
}
