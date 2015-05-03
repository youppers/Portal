<?php
namespace Youppers\CustomerBundle\Tests\Controller;

use Youppers\CommonBundle\Tests\JsonTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProfileServiceTest extends JsonTestCase
{
	
	private $otherUserId = '1';
				
	public function testAuth()
	{
		$client = static::createClient();
	
		$client->request('GET', $this->jsonRpcEndpoint);
	
		$this->assertEquals(
				Response::HTTP_UNAUTHORIZED,
				$client->getResponse()->getStatusCode(),
				'Access without access_token must fail'
		);
	
		$client->request('GET', $this->jsonRpcEndpoint, array('access_token' => $this->getAccessToken()));
	
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