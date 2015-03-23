<?php
namespace Youppers\CustomerBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Doctrine\ORM\QueryBuilder;

class ZoneService extends ContainerAware
{
	private $managerRegistry;
	private $logger;
	
	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;		
		$this->logger = $logger;
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
	
}
