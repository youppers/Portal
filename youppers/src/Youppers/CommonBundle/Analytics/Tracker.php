<?php

namespace Youppers\CommonBundle\Analytics;

use Happyr\Google\AnalyticsBundle\Service\Tracker as GoogleTracker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Youppers\CustomerBundle\Entity\Session;
use Youppers\DealerBundle\Entity\Box;
use Youppers\CompanyBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Youppers\CustomerBundle\Entity\Item;
use Cocur\Slugify\Slugify;

class Tracker
{
	private $tracker;
	private $logger;

	function __construct(GoogleTracker $tracker, LoggerInterface $logger) {
		$this->tracker = $tracker;
		$this->logger = $logger;
	}
	
	/**
	 *
	 * @var Request request
	 */
	private $request;
	
	/**
	 * @param \Symfony\Component\HttpFoundation\Request request
	 */
	public function setRequest($request) {
		$this->request = $request;
	}
	
	/**
	 * Send data to Google Analytics
	 * @param array $data
	 */
	public function send($data,Session $session = null)
	{
		$data['t'] = 'event';  // type
		$data['ds'] = 'server';  // Data Source
		
		if (!array_key_exists('geoid',$data)) {
			$data['geoid'] = 2380; // Italy	IT Country
		}
		
		if (!array_key_exists('z',$data)) {
			$data['z'] = rand();
		}

		if (!array_key_exists('dp',$data)) {
			$slugifier = Slugify::create();
			$data['dp'] = $slugifier->slugify($data['ec']) . '/' . $slugifier->slugify($data['ea']) . '/' . $slugifier->slugify($data['el']); // Document Path
		}

		if ($clientId = $this->request->get('client_id')) {
			$data['cid'] = $clientId;  // Client ID FIXME Must identify the device
		}
		
		if ($session) {
			if (!array_key_exists('cid',$data)) {
				$data['cid'] = $session->getId();  // Client ID FIXME Must identify the device
			}			
			if ($profile = $session->getProfile()) {
				$data['uid'] = $profile->getUser()->getId();  // User ID
			}
		}
		
		if ($this->request) {
			$data['uip'] = $this->request->getClientIp(); // IP Override
		}

		if (!array_key_exists('cid',$data)) {
			$data['cid'] = '41221fc7-d979-11e4-9cb6-0800273000da';  // Client ID is required FIXME Must identify the device
		}
		
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
   			'dt' => 'New Session', // Document Title
   			'ec' => 'Session',  // Specifies the event category. Must not be empty.
   			'ea' => 'New Session',  // Specifies the event action. Must not be empty.
   			'sc' => 'start', // Session Control
    	);
    	
    	if ($store = $session->getStore()) {
   			$data['ea'] = 'New Session in Store';  // Specifies the event action. Must not be empty.
    		$data['el'] = 'Store: ' . $store;  // Event Label
    		$geoid = $store->getGeoid();
    		if ($geoid) {
    			$data['geoid'] = $geoid->getCriteriaId();
    		}
    	} else {
    		$data['el'] = 'Store: unknow';  // Event Label
    	}
    	
    	$this->send($data,$session);    		 
    }

    /**
     * 
     * @param Box $box
     * @param Session $session
     */
    public function sendQrFindBox(Box $box, Session $session = null)
    {
    	$data=array(
    		'dt'=>'Show Box: ' . $box,
    		'ec' => 'QrFind',
    		'ea' => 'Show Box',
    		'el' => 'Box: ' . $box
    	);

    	$store = $box->getStore();
    	$geoid = $store->getGeoid();
    	if ($geoid) {
    		$data['geoid'] = $geoid->getCriteriaId();
    	}
    	$data['dimension3'] = $store->getDealer()->getName();
    	$data['dimension4'] = $store->getName();
    	 
    	 
    	// Product Impression List Name
    	$data['il1nm']= 'Box ' . $box;
    	 
    	$products = $box->getBoxProducts();
    	 
    	if ($products) {
    		$productIndex = 0;
    		foreach ($products as $product) {
    			$productIndex++;
    			$data['il1pi' . $productIndex . 'ps'] = $product->getPosition();
    			$data['il1pi' . $productIndex . 'nm'] = $product->getName();
    			if ($product = $product->getProduct()) {
    				$data['dimension1'] = $product->getBrand()->getCompany()->getName();
    				$data['dimension2'] = $product->getBrand()->getName();
    				$data['il1pi' . $productIndex . 'id'] = $product->getId();
    				$data['il1pi' . $productIndex . 'nm'] = $product->getName();
    				if ($brand = $product->getBrand()) {
    					$data['il1pi' . $productIndex . 'br'] = $brand->getName();
    				}
    				// add category
    				// add variant
    			}
    		}
    	}
    	
    	$this->send($data,$session);
    }

    /**
     * 
     * @param Product $product
     * @param Session $session
     */
    public function sendQrFindProduct(Product $product, Session $session = null)
    {
    	$data=array(
    		'dt' => 'Show Product: ' . $product,
    		'ec' => 'QrFind',
    		'ea' => 'Show Product',
    		'el' => 'Product: ' . $product,
    	);
    	
    	$data['dimension1'] = $product->getBrand()->getCompany()->getName();
    	$data['dimension2'] = $product->getBrand()->getName();
    	 
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
    		$data['geoid']=$store->getGeoid()->getCriteriaId();
    	}

    	$this->send($data,$session);
    }
    
    public function sendItemAdd(Item $item)
    {
    	$product = $item->getVariant()->getProduct();
    	$zone = $item->getZone();
    	$data=array(
    			'dt' => 'Add Product: ' . $product,
    			'ec' => 'ItemAdd',
    			'ea' => 'Add Item in Zome ' . $zone,
    			'el' => 'Product: ' . $product,
    	);
    	 
    	$data['dimension1'] = $product->getBrand()->getCompany()->getName();
    	$data['dimension2'] = $product->getBrand()->getName();
    	
    	// Product Action
    	$data['pa']= 'add';
    	
    	$data['pr1id'] = $product->getId();
    	$data['pr1nm'] = $product->getName();
    	// add category
    	// add variant
    	if ($brand = $product->getBrand()) {
    		$data['pr1br'] = $brand->getName();
    	}
    	
    	$session = $item->getSession();
    	if ($session && $store = $session->getStore()) {
    		$data['geoid']=$store->getGeoid()->getCriteriaId();
    	}
    	
    	$this->send($data,$session);    	 
    }

    public function sendItemRemove(Item $item)
    {
    	$product = $item->getVariant()->getProduct();
    	$zone = $item->getZone();
    	$data=array(
    			'dt' => 'Remove Product: ' . $product,
    			'ec' => 'ItemRemove',
    			'ea' => 'Remove Item from Zome ' . $zone,
    			'el' => 'Product: ' . $product,
    	);
    	
    	$data['dimension1'] = $product->getBrand()->getCompany()->getName();
    	$data['dimension2'] = $product->getBrand()->getName();
    	 
    	// Product Action
    	$data['pa']= 'remove';
    	 
    	$data['pr1id'] = $product->getId();
    	$data['pr1nm'] = $product->getName();
    	// add category
    	// add variant
    	if ($brand = $product->getBrand()) {
    		$data['pr1br'] = $brand->getName();
    	}
    	 
    	$session = $item->getSession();    	 
    	if ($session && $store = $session->getStore()) {
    		$data['geoid']=$store->getGeoid()->getCriteriaId();
    	}
    	 
    	$this->send($data,$session);    	 
    }
    
}
