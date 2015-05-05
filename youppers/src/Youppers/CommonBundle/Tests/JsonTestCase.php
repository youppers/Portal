<?php
namespace Youppers\CommonBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Youppers\CommonBundle\Controller\YouppersJsonRpcController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DomCrawler\Crawler;
use Sonata\UserBundle\Entity\UserManager;

abstract class JsonTestCase extends WebTestCase
{
	
	protected $credentials = array('name' => 'test', 'username' => 'signoramaria', 'password' => 'signoramaria');
	
	protected $jsonRpcEndpoint = '/jsonrpc/';

	private static $seq = 0;
	
	private static $auth;
		
	public function setUp()
	{
		static::bootKernel();		
		$this->controller = static::$kernel->getContainer()->get('youppers_common.jsonrpccontroller');
		$this->logger = static::$kernel->getContainer()->get('logger');
		self::$seq = rand();
	}	
	
	protected function get($what)
	{
		return static::$kernel->getContainer()->get($what);
	}

	protected function getAccessToken()
	{
		if (!isset(self::$auth)) {
			$client = self::createClient();
			
			$oauthClientManager = $client->getContainer()->get('fos_oauth_server.client_manager');		
			$oauthClient = $oauthClientManager->findClientBy(array('name' => $this->credentials['name']));

			$url = $client->getContainer()->get('router')->generate('fos_oauth_server_token');

			$params = array(
						'client_id' => $oauthClient->getPublicId(),
						'client_secret' => $oauthClient->getSecret(),
						'grant_type' => 'client_credentials',
					);
			
			if (array_key_exists('username',$this->credentials)) {
				$params['grant_type'] = 'password';
				$params['username'] = $this->credentials['username'];
				$params['password'] = $this->credentials['password'];
			}
				
			$client->request('POST', $url, $params);
	
			$this->assertEquals(
	    		200, // o Symfony\Component\HttpFoundation\Response::HTTP_OK
	    		$client->getResponse()->getStatusCode(),
				'Autentication failed'				
			);
	
			self::$auth = json_decode($client->getResponse()->getContent());
			$this->logger->info("Got access_token " . self::$auth->access_token);
		}		
		return self::$auth->access_token;
	}
	
	protected function makeMethodRequest($method,$params = array())
	{
		$requestdata = array(
				'jsonrpc' => '2.0',
				'id' => ++self::$seq,
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
	
	protected function makeHttpMethodRequest($method,$params = array())
	{
		$requestdata = array(
				'jsonrpc' => '2.0',
				'id' => ++self::$seq,
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
		$client->request('GET',$this->jsonRpcEndpoint,
				array( // parameters
						'access_token' => $this->getAccessToken(),
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

}