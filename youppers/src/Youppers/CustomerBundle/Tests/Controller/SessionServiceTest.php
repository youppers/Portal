<?php
namespace Youppers\CustomerBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Youppers\CommonBundle\Tests\JsonTestCase;

class SessionServiceTest extends JsonTestCase
{
	
	private static $sessionId;

    public function testSessionNew()
    {
        $response = $this->makeHttpMethodRequest('Session.new');
        //dump($response);
        $this->assertGreaterThan(
            0,
            count($response['result'])
        );
        self::$sessionId = $response['result']['id'];
    }

    public function testSessionEditName()
    {
        $name = 'Visita ' . md5(rand());
        $response = $this->makeHttpMethodRequest('Session.update', array('sessionId' => self::$sessionId, 'data' => array('name' => $name)));
        $this->assertGreaterThan(
            0,
            count($response['result'])
        );
        $this->assertEquals(
            $name,
            $response['result']['name']
        );
    }

    public function testSessionRead()
	{
		$response = $this->makeHttpMethodRequest('Session.read',array('sessionId' => self::$sessionId));		
		$this->assertGreaterThan(
	    	0,
	    	count($response['result'])	
		);
	}
	
	
}