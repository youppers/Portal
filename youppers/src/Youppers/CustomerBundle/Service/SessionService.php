<?php
namespace Youppers\CustomerBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Youppers\CustomerBundle\Entity\Session;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Sonata\CoreBundle\Form\FormHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Form\Form;

class SessionService extends ContainerAware
{
	private $managerRegistry;
	private $logger;
	
	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;
		$this->logger = $logger;
	}	
	
	/**
	 * Create a new session, optionally associated to a store (that must exists)
	 * @param uuid $storeId
	 * @return Session
	 */
	public function newSession($storeId = null)
	{
		$repo = $this->managerRegistry->getRepository('YouppersCustomerBundle:Session');
		$sessionClass = $repo->getClassName();
		$em = $this->managerRegistry->getManagerForClass($sessionClass);
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
	
		$this->container->get('youppers_common.analytics.tracker')->sendNewSession($session);
	
		return $session;
	}
		
	/**
	 * set the store of the session if not set
	 * 
	 * @param id|Session $session
	 * @param id|Store $store
	 * @param string $force Update also if already set
	 */
	public function setSessionStore($session, $store, $force = false) {
		if ($session === null || $store === null) {
			return;
		}		
		if (!is_object($session)) {
			$session = $this->managerRegistry->getRepository('YouppersCustomerBundle:Session')->find($session);
		}
		if ($session !== null && ($force === true || $session->getStore() === null) ) {
			if (!is_object($store)) {
				$store = $this->managerRegistry->getRepository('YouppersDealerBundle:Store')->find($store);
			}
			if ($store !== null) {
				$this->logger->info(sprintf("Updating session '%s' store '%s'",$session,$store));
				$session->setStore($store);
				$this->managerRegistry->getManagerForClass('YouppersCustomerBundle:Session')->flush();
				$this->container->get('youppers_common.analytics.tracker')->sendNewSession($session);				
			}
		}		
	}
	
	/**
	 * set the session store using the box's store
	 * 
	 * @param id|Session $session
	 * @param id|Box $box
	 */
	public function setSessionStoreUsingBox($session, $box)
	{
		if ($session === null || $box === null) {
			return;
		}		
		if (!is_object($box)) {
			$box = $this->managerRegistry->getRepository('YouppersDealerBundle:Box')->find($box);
		}
		if ($box !== null) {
			$this->setSessionStore($session, $box->getStore());
		}
	}
	
	/**
	 * Check to identifiy box that are not in the correct store
	 * 
	 * @param id|Session $session
	 * @param id|Box $box
	 * @return boolean true if the box store match session store, false if don't match, null if don't know
	 */
	public function isBoxInStoreOfSession($session,$box) 
	{
		if ($session === null || $box === null) {
			return;
		}
		if (!is_object($box)) {
			$box = $this->managerRegistry->getRepository('YouppersDealerBundle:Box')->find($box);
		}
		if (!is_object($session)) {
			$session = $this->managerRegistry->getRepository('YouppersCustomerBundle:Session')->find($session);
		}
		if ($box !== null && $session !== null 
				&& $session->getStore() !== null
				&& $box->getStore() !== null) {
			if ($session->getStore() === $box->getStore()) {
				return true;
			} else {
				$this->logger->warning(sprintf("Box '%s' is in store '%s' but the session '%s' is in store '%s'",
						$box, $box->getStore(),$session,$session->getStore()
						));
				return false;
			}
		}		
	}

	/**
	 * 
	 * @param guid $sessionId
	 * @param array $data form data eg 
	 */
	public function read($sessionId)
	{
		return $this->getSession($sessionId);	
	}
	
	/**
	 * Update a session
	 * 
	 * @param guid $sessionId
	 * @param array $data
	 * @return Ambigous <\Youppers\CustomerBundle\Service\Entity, \Symfony\Component\Form\Form>
	 */
	public function update($sessionId, $data)
	{
		return $this->handleWrite($sessionId,$data);
	}
	
	/**
	 * Find a session by Id
	 * 
	 * @param guid $sessionId
	 * @return Session
	 */
	public function getSession($sessionId) 
	{
		return $this->managerRegistry->getRepository('YouppersCustomerBundle:Session')->find($sessionId);
	}
	
	/**
	 * 
	 * @param guid $id sessionId
	 * @param array $data
	 * @return Entity|Form
	 */
	protected function handleWrite($sessionId,$data)
	{
		if ($sessionId) {
			$session = $this->getSession($sessionId);
			if ($session === null) {
				$this->logger->error(sprintf("Session '%s' not found",$sessionId));
				throw new NotFoundResourceException("Session not found");
			}
		}
		
		$form = $this->container->get('form.factory')->createNamed(null, 'youppers_customer_session_form', $session, array(
				'csrf_protection' => false
		));
		
		$form->submit($data,false);		

		if ($form->isValid()) {
			$session = $form->getData();
			$om = $this->managerRegistry->getManagerForClass('YouppersCustomerBundle:Session');
			$om->persist($session);
			$om->flush();
			$this->logger->debug("Update session");
			return $session;
		} else {
			$this->logger->warn("Invalid session: " . $form->getErrors());
			return $form;
		}
	}
	
}
