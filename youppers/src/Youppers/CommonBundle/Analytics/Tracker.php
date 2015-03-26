<?php

namespace Youppers\CommonBundle\Analytics;

use Happyr\Google\AnalyticsBundle\Service\Tracker as GoogleTracker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Youppers\CustomerBundle\Entity\Session;
use Youppers\DealerBundle\Entity\Box;
use Youppers\CompanyBundle\Entity\Product;

class Tracker
{

	function __construct(GoogleTracker $tracker, LoggerInterface $logger) {
		$this->tracker = $tracker;
		$this->logger = $logger;
	}
	
	/**
	 * Send data to Google Analytics
	 * @param array $data
	 */
	public function send($data)
	{
		if (!array_key_exists('geoid',$data)) {
			$data['geoid'] = 2380; // Italy	IT Country
		}
		
		if (!array_key_exists('z',$data)) {
			$data['z'] = rand();
		}

		// TODO add user id
		
    	$stopwatch = new Stopwatch();
    	$stopwatch->start('GoogleAnalytics');
    	$res = $this->tracker->send($data);
    	$event = $stopwatch->stop('GoogleAnalytics');
    	if ($res) {
    		$this->logger->info("Sent event to GoogleAnalytics: " . $event->getDuration() . "mS " . var_export($data, true));
    	} else {
    		$this->logger->error("Failed sending event to GoogleAnalytics: " . var_export($data, true));
    	}
    }

    /**
     * 
     * @param Session $session
     */
    public function sendNewSession(Session $session)
    {
    	$data = array(
   			't' => 'event',
   			'ds' => 'server',
   			'dt' => 'New Session',
   			'ec' => 'Session',
   			'ea' => 'New Session'
    	);
    	
    	if ($store = $session->getStore()) {
    		$data['el'] = 'Store: ' . $store;
    		$geoid = $store->getGeoid();
    		if ($geoid) {
    			$data['geoid'] = $geoid->getCriteriaId();
    		}
    	}
    	
    	$this->send($data);    		 
    }

    /**
     * 
     * @param Box $box
     * @param Session $session
     */
    public function sendQrFindBox(Box $box, Session $session = null)
    {
    	$data=array(
    		't' => 'event',
    		//'dp' => $this->getRequest()->getPathInfo(),
    		'ds' => 'server',
    		'dt'=>'Show Box: ' . $box,
    		'ec' => 'Server',
    		'ea' => 'Box Show',
    		'el' => '' . $box
    	);

    	$store = $box->getStore();
    	$data['el'] = 'Store: ' . $store;
    	$geoid = $store->getGeoid();
    	if ($geoid) {
    		$data['geoid'] = $geoid->getCriteriaId();
    	}
    	 
    	// Product Impression List Name
    	$data['il1nm']= 'Box ' . $box;
    	 
    	$products = $box->getBoxProducts();
    	 
    	if ($products) {
    		$productIndex = 0;
    		foreach ($products as $product) {
    			$productIndex++;
    			$data['il1pi' . $productIndex . 'ps'] = $product->getPosition();
    			if ($product = $product->getProduct()) {
    				$data['il1pi' . $productIndex . 'id'] = $product->getId();
    				$data['il1pi' . $productIndex . 'nm'] = $product->getName();
    				if ($brand = $product->getBrand()) {
    					$data['il1pi' . $productIndex . 'br'] = $brand->getName();
    				}
    				// add category
    				// add variant
    			} else {
    				$data['il1pi' . $productIndex . 'nm'] = $product->getName();
    			}
    		}
    	}
    	
    	$this->send($data);
    }

    /**
     * 
     * @param Product $product
     * @param Session $session
     */
    public function sendQrFindProduct(Product $product, Session $session = null)
    {
    	$data=array(
    		't' => 'event',
    		//'dp' => $this->getRequest()->getPathInfo(),
    		'ds' => 'server',
    		'dt' => 'Show Product: ' . $product,
    		'ec' => 'Server',
    		'ea' => 'Product',
    		'el' => '' . $product,
    	);
    	
    	// Product Action
    	$data['pa']= 'detail';
    	 
    	// Product Position
    	//$data['pr1ps'] = $product->getPosition();
    	 
    	//$data['pal'] = 'Box Show';
    	//$data['il1pi1ps'] = $product->getPosition();
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

    	if ($session && $store = $session->getStore()) {
    		$data['geoid']=$store->getGeoid();
    	}

    	$this->send($data);
    }
    

}
