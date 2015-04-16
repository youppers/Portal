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

class BoxService extends ContainerAware
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
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersDealerBundle:Box 
	 */
	protected function getRepository()
	{
		return $this->managerRegistry->getRepository('YouppersDealerBundle:Box');
	}
	
	protected function getProduct($productId)
	{
		return $this->managerRegistry->getManager()->find('YouppersCompanyBundle:Product',$productId);
	}

	protected function getStore($storeId)
	{
		return $this->managerRegistry->getManager()->find('YouppersDealerBundle:Store',$storeId);
	}

	protected function getGeoid($criteriaId)
	{
		return $this->managerRegistry->getManager()->find('YouppersCommonBundle:Geoid',$criteriaId);
	}
	
	/**
	 * Return list if Box
	 * 
	 * @param string $productId product
	 * @param string $storeId store
	 * @param string $criteriaId geolocalization criteria (used only if storeId=null)
	 * @param number $limit Max results
	 */
	public function listBoxes($productId, $storeId = null, $criteriaId = null, $limit = 10)
	{
		$product = $this->getProduct($productId);
		if (empty($product)) {
			throw new \Exception(sprintf("Product with id '%s' not found",$productId));
		}
		
		$repo = $this->getRepository();
		
		$qb = $repo->createQueryBuilder('b')
			->leftjoin('b.boxProducts', 'p')
			->leftjoin('b.store', 's')
			->where('b.enabled = :enabled')
			->setParameter('enabled',true)
			->andWhere('p.product = :product')
			->setParameter('product',$product)				
			->setMaxResults($limit)		
		;

		if (!empty($storeId)) {
			$store = $this->getStore($storeId);
			if (empty($store)) {
				throw new \Exception(sprintf("Store with id '%s' not found",$storeId));
			}
			$qb
				->andWhere('b.store = :store')
				->setParameter('store',$store);				
		}
		if (!empty($criteriaId) && empty($storeId)) {
			$geoid = $this->getGeoid($criteriaId);
			if (empty($geoid)) {
				throw new \Exception(sprintf("Geoid with criteria id '%s' not found",$criteriaId));
			}
			$qb
				->andWhere('s.geoid= :geoid')
				->setParameter('geoid',$geoid);				
		}
		return $qb->getQuery()->getResult();		
	}
	
	public function showBox($boxId)
	{
		return $this->managerRegistry->getManager()->find('YouppersDealerBundle:Box',$boxId);		
	}

}
