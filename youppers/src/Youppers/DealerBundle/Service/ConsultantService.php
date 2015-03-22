<?php
namespace Youppers\DealerBundle\Service;

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
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;
use Youppers\DealerBundle\Entity\Consultant;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;

class ConsultantService extends ContainerAware
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
		return $this->managerRegistry->getRepository('YouppersDealerBundle:Consultant');
	}

	/**
	 * Return list of consultants available for selection in the current Session
	 * 
	 * @param guid|Session $session
	 */
	public function listForSession($sessionId)
	{
		$session = $this->container->get('youppers.customer.session')->getSession($sessionId);
		if ($session === null) {
			$this->logger->critical(sprintf("Session not found: '%s'",$sessionId));		
			throw new NotFoundResourceException(sprintf("Session '%s' not found",$sessionId));
		}
		$store = $session->getStore(); 
		if ($store === null) {
			$this->logger->error(sprintf("Store not selected for session '%s'",$session));		
			throw new NotFoundResourceException(sprintf("Store must be set in the Session '%s'",$session));
		}
		$this->logger->info(sprintf("List consultants for store '%s' of session '%s'",$store,$session));

		$repo = $this->getRepository();
		
		$qb = $repo->createQueryBuilder('c')
			->join('c.stores', 's', 'WITH', 's = :store')
			->orderBy('c.fullname','ASC')
			->where('c.enabled = :enabled')
			->setParameter('store',$store)
			->setParameter('enabled',true)
			;
		return $qb->getQuery()->getResult();		
	}

}
