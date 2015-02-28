<?php

namespace Youppers\CustomerBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\Annotation\MaxDepth;

class SessionController extends FOSRestController
{
	
	/**
	 * Return a list of sessions
	 * 
	 * @ApiDoc(
	 * 		section="Customer",
	 * 		description="Return a list of sessions"
	 * )
	 * 
	 * @View(
	 * 		serializerEnableMaxDepthChecks=true
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
	 * 		section="Customer",
	 * 		description="Create a new session"
	 * )
	 *
	 * @View(
	 * 		serializerEnableMaxDepthChecks=true
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
	 * 		description="Get a session"
	 * )
	 *
	 * @View(
	 * 		serializerEnableMaxDepthChecks=true
	 * )
	 *
	 */
	public function getSessionAction($id)
	{
		$repo = $this->getDoctrine()->getRepository('YouppersCustomerBundle:Session');
	
		return $repo->find($id);
	}
	
}