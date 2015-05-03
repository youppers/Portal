<?php
namespace Youppers\CustomerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Youppers\CommonBundle\Controller\YouppersJsonRpcController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DomCrawler\Crawler;
use Sonata\UserBundle\Entity\UserManager;

class ProfileServiceTest extends WebTestCase
{
	
	private $usercredentials = array('username' => 'raf', 'password' => 'nomos');
	
	private $otherUserId = '1';
	
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

	private static $auth;
	
	private function getPasswordAccessToken()
	{
		if (!isset(self::$auth)) {
			$client = self::createClient();
			
			$oauthClientManager = $client->getContainer()->get('fos_oauth_server.client_manager');		
			$oauthClient = $oauthClientManager->findClientBy(array('name' => 'test'));

			$url = $client->getContainer()->get('router')->generate('fos_oauth_server_token');		
			$client->request('POST', $url,array(
					'client_id' => $oauthClient->getPublicId(),
					'client_secret' => $oauthClient->getSecret(),
					'grant_type' => 'password',
					'username' => $this->usercredentials['username'],
					'password' => $this->usercredentials['password'],
			));
	
			$this->assertEquals(
	    		200, // o Symfony\Component\HttpFoundation\Response::HTTP_OK
	    		$client->getResponse()->getStatusCode(),
				'User autentication failed'				
			);
	
			self::$auth = json_decode($client->getResponse()->getContent());
			$this->logger->info("Got access_token " . self::$auth->access_token);
		}		
		return self::$auth->access_token;
	}
	
 	private function jsonRpcEndpoint() {
 		return '/jsonrpc/';
	}
 	
	private static $seq = 0;
	
	private function makeMethodRequest($method,$params = array())
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
	
	private function makeHttpMethodRequest($method,$params = array())
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
		$client->request('GET',$this->jsonRpcEndpoint(),
				array( // parameters
						'access_token' => $this->getPasswordAccessToken(),
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
	
		$client->request('GET', $this->jsonRpcEndpoint(), array('access_token' => $this->getPasswordAccessToken()));
	
		$this->assertEquals(
				Response::HTTP_OK,
				$client->getResponse()->getStatusCode()
		);
	
	}

	private static $profiles;
	
	public function testProfileList()
	{
		$response = $this->makeHttpMethodRequest('Profile.list');		
		$this->assertGreaterThan(
	    	0,
	    	count($response['result'])	
		);
		self::$profiles = $response['result'];
	}
	
	
	public function testProfileReadInvalid() {
		$response = $this->makeHttpMethodRequest('Profile.read',array('profileId' => 'invalid'));
		$this->assertArrayHasKey(
				'error',
				$response
		);
	}

	public function testProfileReadMultiple()
	{
		foreach (self::$profiles as $profile) {
			$response = $this->makeHttpMethodRequest('Profile.read',array('profileId' => $profile['id']));
			$this->assertEquals(
					$profile['id'],
					$response['result']['id']
			);
		}		
	}
	
	public function testProfileRead()
	{
		$profile = self::$profiles[0];
		$response = $this->makeHttpMethodRequest('Profile.read',array('profileId' => $profile['id']));
		
		$profile1 = $response['result'];
		//dump($profile1);

		$this->assertArrayHasKey(
				'created_at',
				$profile1
		);
		unset($profile1['created_at']);

		$this->assertArrayHasKey(
				'updated_at',
				$profile1
		);
		unset($profile1['updated_at']);
		
		$this->assertArrayHasKey(
				'user',
				$profile1
		);		
		unset($profile1['user']);

		$this->assertArrayHasKey(
				'zones',
				$profile1
		);
		unset($profile1['zones']);

		$this->assertArrayHasKey(
				'sessions',
				$profile1
		);
		unset($profile1['sessions']);
		
		// after unset of all extra fields
		$this->assertEquals(
				$profile1,
				self::$profiles[0]
			);
	}

	public function testProfileCRUD()
	{
		$name = 'Profilo ' . md5(rand());
		$response = $this->makeHttpMethodRequest('Profile.create',array('data' => array('name' => $name)));
		$this->assertArrayHasKey(
				'id',
				$response['result']
		);				
		$id = $response['result']['id'];
		
		// duplicated name
		$response = $this->makeHttpMethodRequest('Profile.create',array('data' => array('name' => $name)));
		$this->assertArrayHasKey(
				'errors',
				$response['result']['children']['name']
		);
		
		$response = $this->makeHttpMethodRequest('Profile.read',array('profileId' => $id));
		$this->assertEquals(
				$id,
				$response['result']['id']
		);
		
		$data = array();
		foreach ($response['result'] as $name => $value) {
			if ($name == 'id') {
				continue;
			}
			if (!is_array($value)) {
				$data[$name] = $value;
			} elseif (array_key_exists('id',$value)) {
				$data[$name] = $value['id'];
			}
		}
		
		$response1 = $this->makeHttpMethodRequest('Profile.update',array('profileId' => $id, 'data' => $data));
		$this->assertEquals(
				$response['result'],
				$response1['result']
		);

		// toggle enabled
		$data['enabled'] = !$data['enabled'];
		$response2 = $this->makeHttpMethodRequest('Profile.update',array('profileId' => $id, 'data' => $data));
		$this->assertEquals(
				$response1['result']['enabled'],
				!$response2['result']['enabled']
		);
		$this->assertEquals(
				$data['enabled'],
				$response2['result']['enabled']
		);

		// toggle enabled		
		$data['enabled'] = !$data['enabled'];
		$response3 = $this->makeHttpMethodRequest('Profile.update',array('profileId' => $id, 'data' => $data));
		$this->assertEquals(
				$response2['result']['enabled'],
				!$response3['result']['enabled']
		);
		$this->assertEquals(
				$data['enabled'],
				$response1['result']['enabled']
		);
		
		// toggle is_default
		$data['is_default'] = !$data['is_default'];
		$response2 = $this->makeHttpMethodRequest('Profile.update',array('profileId' => $id, 'data' => $data));
		$this->assertEquals(
				$response1['result']['is_default'],
				!$response2['result']['is_default']
		);
		$this->assertEquals(
				$data['is_default'],
				$response2['result']['is_default']
		);

		// update name
		$data['name'] = 'Profilo ' . md5(rand());
		$response = $this->makeHttpMethodRequest('Profile.update',array('profileId' => $id, 'data' => $data));
		$this->assertEquals(
				$response['result']['name'],
				$data['name']
		);

		// set another user (only allowed to super admin)
		$data['user'] = $this->otherUserId;
		$response = $this->makeHttpMethodRequest('Profile.update',array('profileId' => $id, 'data' => $data));
		$this->assertArrayHasKey(
				'error',
				$response
		);
		
		// delete
		$response = $this->makeHttpMethodRequest('Profile.delete',array('profileId' => $id));
		$this->assertArrayNotHasKey(
				'result',
				$response
		);
		
		// read must fail after delete
		$response = $this->makeHttpMethodRequest('Profile.read',array('profileId' => $id));
		$this->assertArrayHasKey(
				'error',
				$response
		);		
	}
	
	
}