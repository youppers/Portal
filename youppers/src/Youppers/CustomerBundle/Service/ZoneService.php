<?php
namespace Youppers\CustomerBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Doctrine\ORM\QueryBuilder;
use Youppers\CustomerBundle\Manager\ZoneManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Sonata\UserBundle\Model\UserInterface;

class ZoneService extends ContainerAware
{	
	private $managerRegistry;
	private $logger;
	private $manager;
	
	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;		
		$this->logger = $logger;
		$this->manager = new ZoneManager($managerRegistry);
	}	
	
	/**
	 * Used to set user as current authenticated user
	 *
	 * @param TokenStorageInterface $tokenStorage
	 */
	public function setTokenStorage(TokenStorageInterface $tokenStorage)
	{
		$this->tokenStorage = $tokenStorage;
	}
	

	private function getUser()
	{
		if ($this->tokenStorage
				&& ($token = $this->tokenStorage->getToken())
				&& ($user = $token->getUser())) {
			if ($user instanceof UserInterface) {
				return $user;
			} else {
				$this->logger->critical("Token user: " . $user);
				return null;
			}
		}
	}
	
	/**
	 *
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersDealerBundle:Consultant
	 */
	protected function getRepository()
	{
		return $this->managerRegistry->getRepository('YouppersCustomerBundle:Zone');
	}
	
	/**
	 * Return list of Zones available for selection with the current session (session MUST have a profile)
	 *
	 * @param guid $sessionId
	 */
	public function listForSession($sessionId)
	{
		$session = $this->container->get('youppers.customer.session')->getSession($sessionId);
		if ($session === null) {
			$this->logger->critical(sprintf("Session not found: '%s'",$sessionId));
			throw new NotFoundResourceException(sprintf("Session with id='%s' not found",$sessionId));
		}

		$repo = $this->getRepository();
		
		$qb = $repo->createQueryBuilder('z')
		->orderBy('z.name','ASC');
		
		$profile = $session->getProfile();
		if (empty($profile)) {
			$this->logger->info(sprintf("Session '%s' dont have a profile selected",$session));
			$qb->andWhere('z.profile IS NULL');				
		} else {
			$this->logger->info(sprintf("List zones for profile '%s'",$profile));
			$qb->setParameter('profile',$profile);
			$qb->andWhere('z.profile = :profile OR z.profile IS NULL');				
		}
		return $qb->getQuery()->getResult();
	}
	
	/**
	 * Create a zone associated to the profile of the current session
	 * @param unknown $sessionId
	 * @param unknown $zoneName
	 * @throws NotFoundResourceException
	 * @throws \Exception
	 * @return unknown
	 */
	public function createForSession($sessionId,$zoneName)
	{
		$session = $this->container->get('youppers.customer.session')->getSession($sessionId);

		if ($session === null) {
			$this->logger->critical(sprintf("Session not found: '%s'",$sessionId));
			throw new NotFoundResourceException(sprintf("Session with id='%s' not found",$sessionId));
		}
		
		if (($profile = $session->getProfile()) === null) {
			$this->logger->critical(sprintf("Session dont have a profile associated: '%s'",$session));
			throw new \Exception("Please login");			
		}
		
		return $this->createForProfile($profile, $zoneName);
	}
		
	/**
	 * Create a zone associated to the profile
	 * @param unknown $profile
	 * @param unknown $zoneName
	 * @throws \Exception
	 * @return unknown
	 */
	private function createForProfile($profile,$zoneName)
	{			
		$repo = $this->getRepository();
		if (count($repo->findBy(array('name' => $zoneName, 'profile' => null))) > 0 ||
			count($repo->findBy(array('name' => $zoneName, 'profile' => $profile))) > 0) {
			throw new \Exception(sprintf("Duplicated zone name '%s'",$zoneName));
		}
		// TODO use handleWrite
		$zoneClass = $repo->getClassName();
		$em = $this->managerRegistry->getManagerForClass($zoneClass);
		$zone = new $zoneClass;
		$zone->setProfile($profile);
		$zone->setName($zoneName);
		
		$em->persist($zone);
		$em->flush();
		
		return $zone;		
	}
	
	public function listForCurrentUser()
	{
		if ($user = $this->getUser()) {
			return $this->manager->findAllForUser($user);
		}
		throw new \Exception("User not logged in");
	}
	
	/**
	 * 
	 * @param unknown $data
	 * @throws \Exception
	 * @return Ambigous <unknown, \Symfony\Component\Form\FormInterface>
	 */
	public function create($data)
	{
		if ($user = $this->getUser()) {
			$profile = $this->container->get('youppers.customer.service.session')->getDefaultProfile($user);
			$data['profile'] = $profile->getId();
			return $this->handleWrite(null, $data);
		}
		throw new \Exception("User not logged in");
	}
	
	/**
	 * 
	 * @param unknown $zoneId
	 * @throws \Exception
	 * @return object
	 */
	public function read($zoneId)
	{
		if ($user = $this->getUser()) {
			$zone = $this->manager->find($zoneId);
			if (empty($zone)) {
				throw new \Exception("Zone not found");
			}
			$profile =  $zone->getProfile();
			if (!empty($profile) && $profile->getUser() != $user) {
				throw new \Exception("User not allowed to read this zone not owned");
			}
			return $zone;
		}
		throw new \Exception("User not logged in");
	}
	
	/**
	 * 
	 * @param unknown $zoneId
	 * @param unknown $data
	 * @throws \Exception
	 */
	public function update($zoneId, $data)
	{
		if ($user = $this->getUser()) {
			$zone = $this->manager->find($zoneId);
			if (empty($zone)) {
				throw new \Exception("Zone not found");
			}
			$profile =  $zone->getProfile();
			if (empty($profile)) {
				throw new \Exception("User not allowed to update a public zone");
			}
			if ($profile->getUser() != $user) {
				throw new \Exception("User not allowed to update this zone not owned");
			}
			if (array_key_exists('profile',$data) && $data['profile'] != $profile->getId()) {				
				throw new \Exception("User not allowed to changed the profile of a zone");
			}
			$normalizer = $this->container->get('fos_rest.normalizer.camel_keys');
			return $this->handleWrite($zone,$normalizer->normalize($data));
		}
		throw new \Exception("User not logged in");
	}
	
	public function delete($zoneId)
	{
		if ($user = $this->getUser()) {
			$zone = $this->manager->find($zoneId);
			if (empty($zone)) {
				throw new \Exception("Zone not found");
			}
			$profile =  $zone->getProfile();
			if (empty($profile)) {
				throw new \Exception("User not allowed to delete a public zone");
			}
			if ($profile->getUser() != $user) {
				throw new \Exception("User not allowed to delete this zone not owned");
			}
			return $this->manager->delete($zone);
		}
		throw new \Exception("User not logged in");
	}
	
	protected function handleWrite($zone,$data)
	{
		$form = $this->container->get('form.factory')->createNamed(null, 'youppers_customer_zone_form', $zone, array(
				'csrf_protection' => false
		));
		
		$form->submit($data,false);
	
		if ($form->isValid()) {
			$zone = $form->getData();
			$this->manager->save($zone);
			return $zone;
		} else {
			$this->logger->warn("Invalid zone: " . $form->getErrors());
			return $form;
		}
	}
	
	
	
}
