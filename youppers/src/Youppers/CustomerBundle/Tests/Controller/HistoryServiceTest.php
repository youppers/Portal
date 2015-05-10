<?php

namespace Youppers\CustomerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Youppers\DealerBundle\Entity\Box;
use Youppers\CommonBundle\Tests\JsonTestCase;

class HistoryServiceTest extends JsonTestCase
{
	
	private $boxId = '1879dd78-abb0-11e4-b4aa-0cc47a127a14';
	private $sessionId = '08ff525b-ed9a-11e4-b4aa-0cc47a127a14';
	private $variantId = '05149cca-cccf-11e4-b4aa-0cc47a127a14';
	private $zoneId = 'afb535fe-c98c-11e4-b4aa-0cc47a127a14'; //'ace73d9e-c98c-11e4-b4aa-0cc47a127a14';
		
	public function testBoxAdd()
	{
		$boxManager = self::$kernel->getContainer()->get('youppers.dealer.manager.box');		
		$box = $boxManager->find($this->boxId);

		$sessionManager = self::$kernel->getContainer()->get('youppers.customer.manager.session');
		$session = $sessionManager->find($this->sessionId);
		
		$historyService = self::$kernel->getContainer()->get('youppers.customer.service.history');
		$historyService->newHistoryQrBox($box,$session);		
	}

	public function testVariantAdd()
	{
		$variantManager = self::$kernel->getContainer()->get('youppers.product.manager.product_variant');
		$variant = $variantManager->find($this->variantId);
	
		$sessionManager = self::$kernel->getContainer()->get('youppers.customer.manager.session');
		$session = $sessionManager->find($this->sessionId);
	
		$historyService = self::$kernel->getContainer()->get('youppers.customer.service.history');
		$historyService->newHistoryQrVariant($variant,$session);
	}

	public function testRpcVariantRead()
	{
		$response = $this->makeHttpMethodRequest('Variant.read',array('variantId' => $this->variantId, 'sessionId' => $this->sessionId));
		$this->assertEquals(
				$this->variantId,
				$response['result']['id']
				);					
	}
	
	public function testItemAdd()
	{
		$itemService = self::$kernel->getContainer()->get('youppers.customer.service.item');
		
		$result = $itemService->create($this->sessionId,array('variantId'=>$this->variantId,'zoneId'=>$this->zoneId));
	}
	
	public function testList()
	{
		$sessionManager = self::$kernel->getContainer()->get('youppers.customer.manager.session');
		$session = $sessionManager->find($this->sessionId);

		$historyService = self::$kernel->getContainer()->get('youppers.customer.service.history');		
		$result = $historyService->historyList($session);
		//dump($result);		
	}
	
	public function testRpcList()
	{
		$response = $this->makeHttpMethodRequest('History.list',array('sessionId' => $this->sessionId));
		//dump($response);		
	}
	
}
