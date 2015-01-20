<?php

namespace Youppers\CommonBundle\Analytics;

use Happyr\Google\AnalyticsBundle\Http as Happyr;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\Request;
/**
 * Class HttpClient
 *
 * @author Sergio Strampelli
 *
 */
class HttpClient extends Happyr\HttpClient implements Happyr\HttpClientInterface
{
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
	
    protected function getClient()
    {
        if ($this->client === null) {
        	$config = array();
        	if ($this->request) {
        		// Spoofs & Mimics User-Agent strings 
        		$config['defaults']['headers']['User-Agent'] = $this->request->headers->get('user-agent');
        	}
            $this->client = new Client($config);
        }

        return $this->client;
    }
	

}