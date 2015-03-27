<?php
namespace Youppers\ProductBundle\Service;

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
use Youppers\ProductBundle\Entity\ProductVariant;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

class ProductService extends ContainerAware
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
	 * @param string $query
	 * @return multitype:
	 * @deprecated
	 */
	public function listVariants($query,$limit = 100,$sessionId=null)
	{
		if (empty($query)) {
			return;
		}
		$repo = $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant');		
		$query = $repo
			->createQueryBuilder('v')
			->join('YouppersCompanyBundle:Product','p', Expr\Join::WITH,'v.product = p')
			->where('p.name LIKE :query')
			->setParameter('query', '%' . $query . '%')
			->orderBy('p.name', 'ASC')
			->setMaxResults($limit)
			->getQuery();
		
		return $query->getResult();
	}

	/**
	 * Search for the string in code or name field of the product table
	 * @param string $query
	 * @param number $limit 100
	 * @param string $sessionId not used
	 */
	public function searchProducts($query,$limit = 100,$sessionId=null)
	{
		if (empty($query)) {
			return;
		}
		$repo = $this->managerRegistry->getRepository('YouppersCompanyBundle:Product');
		$qb = $repo
			->createQueryBuilder('p');
		$query = $qb
			->where($qb->expr()->like('p.code', ':query'))
			->orWhere($qb->expr()->like('p.name', ':query'))
			->setParameter('query', '%' . $query . '%')
			->orderBy('p.name', 'ASC')
			->setMaxResults($limit)		
			->getQuery();
	
		return $query->getResult();
	}
	
	public function readVariant()
	{
		
	}

	public function readCollection()
	{
	
	}

	public function readAttributes()
	{
	
	}	
}
