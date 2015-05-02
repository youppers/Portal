<?php
namespace Youppers\CommonBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Youppers\CommonBundle\Controller\YouppersJsonRpcController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DomCrawler\Crawler;

class YouppersJsonRpcControllerTest extends WebTestCase
{
	
	private $variantId = '2c897a91-dec5-11e4-b4aa-0cc47a127a14';
	
	private $controller;  // json controller
	
	public function setUp()
	{
		static::bootKernel();		
		$this->controller = static::$kernel->getContainer()->get('youppers_common.jsonrpccontroller');
		$this->logger = static::$kernel->getContainer()->get('logger');
	}	
	
	private function get($what)
	{
		return static::$kernel->getContainer()->get($what);
	}

	private $auth;
	
	private function getClientAccessToken()
	{
		if (!isset($this->auth)) {
			$client = self::createClient();
			
			$oauthClientManager = $client->getContainer()->get('fos_oauth_server.client_manager');		
			$oauthClient = $oauthClientManager->findClientBy(array('name' => 'test'));
			
			$url = $client->getContainer()->get('router')->generate('fos_oauth_server_token');		
			$client->request('POST', $url,array(
					'client_id' => $oauthClient->getPublicId(),
					'client_secret' => $oauthClient->getSecret(),
					'grant_type' => 'client_credentials',
			));
	
			//dump($client->getResponse());
			
			$this->assertEquals(
	    		200, // o Symfony\Component\HttpFoundation\Response::HTTP_OK
	    		$client->getResponse()->getStatusCode(),
				'Client autentication failed'				
			);
	
			$this->auth = json_decode($client->getResponse()->getContent());
			$this->logger->info("Got access_token " . $this->auth->access_token);
		}		
		return $this->auth->access_token;
	}
	
 	private function jsonRpcEndpoint() {
 		return '/jsonrpc/';
	}
 	
	private $seq = 0;
	
	private function makeMethodRequest($method,$params)
	{
		$requestdata = array(
				'jsonrpc' => '2.0',
				'id' => ++$this->seq,
				'method' => $method,
				'params' => $params
		);
		$response = $this->makeRequest($requestdata);
		return $response;
	}
	
	private function makeRequest($requestdata)
	{
		/** @var \JMS\Serializer\Serializer $serializer */
		$serializer = static::$kernel->getContainer()->get('jms_serializer');
		return json_decode($this->controller->execute(
				new Request(array(), array(), array(), array(), array(), array(), $serializer->serialize($requestdata, 'json'))
		)->getContent(), true);
	}
	
	private function makeHttpMethodRequest($method,$params)
	{
		$requestdata = array(
				'jsonrpc' => '2.0',
				'id' => ++$this->seq,
				'method' => $method,
				'params' => $params
		);
		$response = $this->makeHttpRequest($requestdata);
		return json_decode($response->getContent(), true);
	}
	
	private function makeHttpRequest($requestdata)
	{
		/** @var \JMS\Serializer\Serializer $serializer */
		$serializer = static::$kernel->getContainer()->get('jms_serializer');
		$client = static::createClient();
		//dump($client);
		$client->request('GET',$this->jsonRpcEndpoint(),
				array( // parameters
						'access_token' => $this->getClientAccessToken(),
				),
				array(), // files
				array( // The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
						'HTTP_HEADERS' => array(
								'Accept'     => 'application/json',
								'Content-Type' => 'application/json',
						)
				),
				$serializer->serialize($requestdata, 'json') // The raw body data
		);
		return $client->getResponse();
	}
		
	public function testAuth()
	{
		$client = static::createClient();
	
		$client->request('GET', $this->jsonRpcEndpoint());
	
		$this->assertEquals(
				Response::HTTP_UNAUTHORIZED,
				$client->getResponse()->getStatusCode(),
				'Access without access_token must fail'
		);
	
		$client->request('GET', $this->jsonRpcEndpoint(), array('access_token' => $this->getClientAccessToken()));
	
		$this->assertEquals(
				Response::HTTP_OK,
				$client->getResponse()->getStatusCode()
		);
	
	}
	
	public function testAttributesRead()
	{
		$response1 = $this->makeMethodRequest('Attributes.read',array('variantId' => $this->variantId . 'xx'));
		$this->assertEquals(
	    	"Invalid variant id",
	    	$response1['error']['data']	
		);		
		$response2 = $this->makeHttpMethodRequest('Attributes.read',array('variantId' => $this->variantId . 'xx'));
		$this->assertEquals(
	    	"Invalid variant id",
	    	$response2['error']['data']	
		);

		$response3 = $this->makeMethodRequest('Attributes.read',array('variantId' => $this->variantId));
		$this->assertGreaterThan(
	    	0,
	    	count($response3['result'])	
		);
		$response4 = $this->makeHttpMethodRequest('Attributes.read',array('variantId' => $this->variantId));
		$this->assertEquals(
	    	json_encode($response3['result']),
	    	json_encode($response4['result'])				
		);
	}
		
}