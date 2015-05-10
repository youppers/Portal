<?php
namespace Youppers\CustomerBundle\Tests\Controller;

use Youppers\CommonBundle\Tests\JsonTestCase;
use Symfony\Component\HttpFoundation\Response;

class ZoneServiceTest extends JsonTestCase
{
	
	private $otherUserId = '1';
	
	static $zones;
				
	public function testZoneList()
	{
		$response = $this->makeHttpMethodRequest('Zone.list1');
		//dump($response); die;		
		$this->assertGreaterThan(
	    	0,
	    	count($response['result'])	
		);
		self::$zones = $response['result'];
	}
	
	
	public function testZoneReadInvalid() {
		$response = $this->makeHttpMethodRequest('Zone.read',array('zoneId' => 'invalid'));
		$this->assertArrayHasKey(
				'error',
				$response
		);
	}
	
	public function testZoneDeleteInvalid() {
		$response = $this->makeHttpMethodRequest('Zone.delete',array('zoneId' => 'invalid'));
		$this->assertArrayHasKey(
				'error',
				$response
		);		
	}

	public function testPublicZoneDeleteFail() {
		foreach (self::$zones as $zone) {
			if (empty($zone['profile'])) {
				$zoneId = $zone['id'];
				break;
			}
		}
		$response = $this->makeHttpMethodRequest('Zone.delete',array('zoneId' => $zoneId));
		$this->assertArrayHasKey(
				'error',
				$response
		);		
	}

	/**
	 * This test should fail because existing zones are used as a reference 
	 */
	public function testOwnZoneDeleteFail() {
		foreach (self::$zones as $zone) {
			if (!empty($zone['profile'])) {
				$zoneId = $zone['id'];
				break;
			}
		}
		$response = $this->makeHttpMethodRequest('Zone.delete',array('zoneId' => $zoneId));
		$this->assertArrayHasKey(
				'error',
				$response,
				'There is a zone that is not used, please redo this test again and again'
		);
	}

	/*
	public function testZoneCRUD()
	{
		$name = 'Zona ' . md5(rand());
		

		$zoneService = static::$kernel->getContainer()->get('youppers.customer.service.zone');
		
		$zone = $zoneService->create(array('name' => $name));
		$zoneService->delete($zone);
	}
	*/
	
	public function testJsonZoneCRUD()
	{
		$name = 'Zona ' . md5(rand());
		
		$response = $this->makeHttpMethodRequest('Zone.create1',array('data' => array('name' => $name)));
		$this->assertArrayHasKey(
				'id',
				$response['result']
		);				
		$id = $response['result']['id'];
		
		// duplicated name
		$response = $this->makeHttpMethodRequest('Zone.create1',array('data' => array('name' => $name)));
		$this->assertArrayHasKey(
				'errors',
				$response['result']['children']['name']
		);
		
		$response = $this->makeHttpMethodRequest('Zone.read',array('zoneId' => $id));
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
		
		$response1 = $this->makeHttpMethodRequest('Zone.update',array('zoneId' => $id, 'data' => $data));		
		$this->assertEquals(
				$response['result'],
				$response1['result']
		);

		// toggle enabled
		$data['enabled'] = !$data['enabled'];
		$response2 = $this->makeHttpMethodRequest('Zone.update',array('zoneId' => $id, 'data' => $data));
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
		$response3 = $this->makeHttpMethodRequest('Zone.update',array('zoneId' => $id, 'data' => $data));
		$this->assertEquals(
				$response2['result']['enabled'],
				!$response3['result']['enabled']
		);
		$this->assertEquals(
				$data['enabled'],
				$response1['result']['enabled']
		);
		
		// update name
		$data['name'] = 'Zona ' . md5(rand());
		$response = $this->makeHttpMethodRequest('Zone.update',array('zoneId' => $id, 'data' => $data));
		$this->assertEquals(
				$response['result']['name'],
				$data['name']
		);

		// try to update profile
		$data['profile'] = md5(rand());
		$response = $this->makeHttpMethodRequest('Zone.update',array('zoneId' => $id, 'data' => $data));
		$this->assertArrayHasKey(
				'error',
				$response
		);
		
		// delete
		$response = $this->makeHttpMethodRequest('Zone.delete',array('zoneId' => $id));
		//dump($response);
		$this->assertArrayNotHasKey(
				'result',
				$response
		);
		$this->assertArrayNotHasKey(
				'error',
				$response
		);
		
		// read must fail after delete
		$response = $this->makeHttpMethodRequest('Zone.read',array('zoneId' => $id));
		//dump($response);
		$this->assertArrayHasKey(
				'error',
				$response
		);		
	}
	
	
}