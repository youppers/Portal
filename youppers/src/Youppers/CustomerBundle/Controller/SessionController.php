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
	 *  	filters={
     *      	{"name"="store[code]", "dataType"="string"},
     *      	{"name"="profile[user][id]", "dataType"="string"}
     *      },
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
	 * 		output={
	 * 			"class"="Youppers\CustomerBundle\Entity\Session",
	 * 			"groups"={"details"}
	 * 		}
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
		return $this->newSession();
	}
	
	/**
	 * Create a new session, optionally associated to a store (that must exists)
	 * @param uuid $storeId
	 * @return Session
	 */
	public function newSession($storeId = null)
	{
		$repo = $this->getDoctrine()->getRepository('YouppersCustomerBundle:Session');
		$sessionClass = $repo->getClassName();
		$em = $this->getDoctrine()->getManagerForClass($sessionClass);
		$session = new $sessionClass;
		if ($storeId) {
			$store = $em->find('YouppersDealerBundle:Store', $storeId);
			if (empty($store)) {
				throw $this->createNotFoundException('Store not found');
			} else {
				$session->setStore($store);
			}
		} else {
			$store = null;
		}
		$em->persist($session);
		$em->flush();

		if ($this->has('youppers_common.analytics.tracker')) {
			$data = array(
					't' => 'event',
					'ds' => 'server',
					'dt' => 'New Session',
					'ec' => 'Session',
					'ea' => 'New Session'
			);
			
			if ($store) {
				$data['el'] = 'Store: ' . $store;
				$geoid = $store->getGeoid();
				if ($geoid) {
					$data['geoid'] = $geoid->getCriteriaId();
				}
			}
			
					
			$this->get('youppers_common.analytics.tracker')->send($data);
		}
		
		return $session;
	}

	/**
	 * Get a Session
	 *
	 * @ApiDoc(
	 * 		section="Customer",
	 * 		description="Get a session",
	 * 		output={
	 * 			"class"="Youppers\CustomerBundle\Entity\Session",
	 * 			"groups"={"details"}
	 * 		}
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
	 * 		input={
	 * 			"class"="Youppers\CustomerBundle\Entity\Session",
	 * 			"groups"={"update"}
	 * 		},
	 * 		output={
	 * 			"class"="Youppers\CustomerBundle\Entity\Session",
	 * 			"groups"={"update"}
	 * 		}
	 * )
	 * 
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