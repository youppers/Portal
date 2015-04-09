<?php

namespace Youppers\CommonBundle\Controller;

use Wa72\JsonRpcBundle\Controller\JsonRpcController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class YouppersJsonRpcController extends JsonRpcController
{
	public function execute(Request $httprequest)
	{
		$logger = $this->container->get('logger');
		
		$logger->info(sprintf("JSON-RPC Request: %s",$httprequest->getContent()));
		
		$response = parent::execute($httprequest);
		
		$logger->info(sprintf("JSON-RPC Response: %s",$response->getContent()));
		
		return $response;
	}
} 
