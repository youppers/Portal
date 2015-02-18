<?php

namespace Youppers\CommonBundle\Analytics;

use Happyr\Google\AnalyticsBundle\Http as Happyr;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

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
	
	private $logger;
	
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
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

	/**
	 * 
	 * @param array $data
	 * @return \GuzzleHttp\Message\mixed
	 */
    public function send(array $data = array())
    {
    	$client = $this->getClient();
    	$options = array(
    			'body' => $data,
    			'timeout' => $this->requestTimeout,
    	);
    	$request = $client->createRequest('POST', $this->endpoint, $options);
    
    	$response = $client->send($request);
    	if ($this->logger) {
    		$this->logger->debug($request);
    		$this->logger->debug($response);
    	}
    
 		return $response->getStatusCode() == '200';
     }

}