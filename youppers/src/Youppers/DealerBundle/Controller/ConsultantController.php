<?php

namespace Youppers\DealerBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\Annotation\MaxDepth;

class ConsultantController extends FOSRestController
{
	
	/**
	 * Return a list of consultants
	 * 
	 * @ApiDoc(
	 * 		section="Dealer",
	 * 		description="Return a list of consultants"
	 * )
	 * 
	 * @View(
	 * 		serializerEnableMaxDepthChecks=true
	 * )
	 * 
	 */
	public function allAction()
	{
		$consultants = $this->getDoctrine()
			->getRepository('YouppersDealerBundle:Consultant')
			->findAll();
		
		return $consultants;
	}
}