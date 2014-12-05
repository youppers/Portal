<?php

namespace Youppers\ScraperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Goutte\Client;
use Symfony\Component\DomCrawler\Link;

use Youppers\ScraperBundle\ImgLink;

/**
 * Default controller.
 *
 * @Route("/scraper")
 */
class DefaultController extends Controller
{
	/**
	 * @Route("/")
	 * @Template()
	 */
	public function indexAction()
	{
		return array();
	}
	
	
	/**
     * @Route("/{brand}/{product}")
     * @Template()
     */
    public function productAction($brand,$product)
    {
    	
    	$res = array('brand' => $brand, 'product' => $product);
    	
    	$client = new Client();
    	
    	if ($brand == "IS") {
    		
    		$parameters = array(
    			'tx_indexedsearch' => array(
    				'sword' => substr($product,0,-2)
    			)
    		);
    		
    		$searchcrawler = $client->request('POST', 'http://www.idealstandard.it/search.html?no_cache=1', $parameters);
    		
    		$links = $searchcrawler->filter('div.tx-indexedsearch-res > ul > li > div > h3 > a')->links();
    		
    		$products = array();
    		
    		foreach ($links as $link) {
    			$uri = $link->getUri();
    			$productcrawler = $client->request('GET',$uri);
    			
    			$product = array();

    			$product['uri'] = $uri;
    			
    			foreach ($productcrawler->filterXPath('//*[@id="productImg"]/div/div/img') as $imgNode) {
    				$product['images'][] = (new ImgLink($imgNode,$uri))->getUri();
    			} 
    			$product['title'] = $productcrawler->filterXPath('//*[@id="productText"]/div/div[1]/div/h1')->html();

    			foreach (preg_split("/,/",$productcrawler->filterXPath('//*[@id="productText"]/div/div[1]/div/p[1]/span')->text()) as $productcode) {
    				$productcode = trim($productcode);
    				if (!empty($productcode)) {
    					$product['codes'][] = $productcode;
    				}
    			}
    			
    			$product['description'] = $productcrawler->filterXPath('//*[@id="productText"]/div/div[1]/div/div[1]')->text();
    			    			
    			foreach ($productcrawler->filterXPath('//*[@id="productImg"]/div/div/div/ul/li/a') as $aNode) {
    				
    				$spans = array();
    				foreach($aNode->getElementsByTagName('span') as $node) {
    					$spans[] = $node->textContent;
    				}
    				$product['attachments'][] = array(
    				  'uri' => (new Link($aNode,$uri))->getUri(),
    				  'title' => $aNode->getAttribute('title'),
    				  'description' => $spans[1],
    				  'type' => $spans[2],
    				);
    			}    			
    			     			
    			$results[] = $uri;
    			$products[] = $product;
    		}
    		
    		$res['products'] = $products;
    		    		
    		$res['dump'] = var_export($products,true);
    		
    	} else {
    		//
    	}
    	    	
        return $res;
    }
}
