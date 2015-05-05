<?php
namespace Youppers\ProductBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Youppers\CommonBundle\Tests\JsonTestCase;

class ProductCollectionTest extends JsonTestCase
{
	
	private static $sessionId;
	
	private $collectionId = 'be872eef-eb75-11e4-b4aa-0cc47a127a14';

	public function testCollectionRead()
	{
		$response = $this->makeHttpMethodRequest('Collection.read',array('collectionId' => $this->collectionId));
		dump($response); die;		
		$this->assertGreaterThan(
	    	0,
	    	count($response['result'])	
		);
	}
	
	
}