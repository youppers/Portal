<?php

namespace Youppers\CustomerBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Youppers\DealerBundle\Entity\Consultant;

class SessionController extends FOSRestController
{
	
	/**
	 * Return a list of sessions
	 * 
	 * @ApiDoc(
	 * 		section="Customer",
	 * 		description="Return a list of sessions",
	 * 		output={
	 * 			"class"="Youppers\CustomerBundle\Entity\Session",
	 * 			"groups"={"list"}
	 * 		}
	 * )
	 * 
	 * @Rest\View(
	 * 		serializerEnableMaxDepthChecks=true,
	 * 		serializerGroups={"list"}
	 * )
	 * 
	 */
	public function getSessionsAction()
	{
		$sessions = $this->getDoctrine()
			->getRepository('YouppersCustomerBundle:Session')
			->findAll();
		
		return $sessions;
	}

	/**
	 * Return a new Session
	 *
	 * @ApiDoc(
	 * 
	 * 		section="Customer",
	 * 		description="Create a new session",
	 * 		output="Youppers\CustomerBundle\Entity\Session"
	 * )
	 *
	 * @Rest\View(
	 * 		serializerEnableMaxDepthChecks=true,
	 * 		serializerGroups={"details"}
	 * )
	 *
	 */
	public function newSessionAction()
	{
		$repo = $this->getDoctrine()->getRepository('YouppersCustomerBundle:Session');
		$sessionClass = $repo->getClassName();
		$em = $this->getDoctrine()->getEntityManagerForClass($sessionClass);
		$session = new $sessionClass;
		$em->persist($session);
		$em->flush();
		
		return $session;
	}

	/**
	 * Get a Session
	 *
	 * @ApiDoc(
	 * 		section="Customer",
	 * 		description="Get a session",
	 * 		output="Youppers\CustomerBundle\Entity\Session"
	 * )
	 *
	 * @Rest\View(
	 * 		serializerEnableMaxDepthChecks=true,
	 * 		serializerGroups={"details"}
	 * )
	 *
	 */
	public function getSessionAction($id)
	{
		$repo = $this->getDoctrine()->getRepository('YouppersCustomerBundle:Session');
	
		return $repo->find($id);
	}
	
	/**
	 * Update a Session
	 *
	 * @ApiDoc(
	 * 		section="Customer",
	 * 		resource=true,
	 * 		description="Update a session",
	 * 		input="Youppers\CustomerBundle\Entity\Session",
	 * 		output="Youppers\CustomerBundle\Entity\Session"
	 * )
	 * @Rest\View(
	 * 		serializerEnableMaxDepthChecks=true,
	 * 		serializerGroups={"update"}
	 * )
	 *
	 */
	public function putSessionAction($id)
	{
		
	}
	
}