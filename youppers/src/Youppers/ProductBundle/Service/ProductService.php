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
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeOption;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductService extends ContainerAware
{
	private $managerRegistry;
	private $logger;
	private $debug;
	
	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;
		$this->logger = $logger;
	}	

	public function setContainer(ContainerInterface $container = null)
	{
		parent::setContainer($container);
		$this->debug = $this->container->getParameter('kernel.environment') == 'dev';
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
	
	/**
	 * Return one ProductVariant
	 * 
	 * If only variantId is supplied, return this specific variant
	 * If there is one or more option, search for matching variants of the same collection
	 *   if the options allow to select:
	 *     - exactly one variant: return this
	 *     - more than one variant and one is that specified by id: return the variant specified
	 *     - more than one variant but none is that specified by id: return the first of the list
	 *     - no variant: return null
	 *    
	 * @param uuid $variantId the variant actually shown
	 * @param array<uuid> $options list of id of selected options
	 * @param string $sessionId Used to track activities
	 * @return null|ProductVariant
	 */
	public function readVariant($variantId, $options = array(), $sessionId =  null)
	{
		$variant = $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant')->find($variantId);
		
		if (count($options) == 0) {
			if ($this->debug) $this->logger->info(sprintf("No options, return current: %s",$variant));
			return $variant;
		}
		
		$collection = $variant->getProductCollection();
		
		$optionsRepo = $this->managerRegistry->getRepository('YouppersProductBundle:AttributeOption');

		$optionsEntities = array();
		foreach ($options as $option) {
			$optionEntity = $optionsRepo->find(trim($option));
			if ($optionEntity) {
				$optionsEntities[] = $optionEntity;
			} else {
				$this->logger->error("Invalid option id=".$option);
				throw new NotFoundHttpException("Invalid option");				
			}
		}
		
		$variants =  $this->findVariants($collection, $optionsEntities);
		
		if (count($variants) == 0) {
			$this->logger->info("No variants found.");
			return null;
		} elseif (count($variants) == 1) {
			$newVariant = array_shift($variants);
			$this->logger->info(sprintf("Exactly one variant found: %s",$newVariant));
		} else {
			$this->logger->info(sprintf("Found %d variants",count($variants)));
			foreach ($variants as $newVariant) {
				if ($this->debug) $this->logger->info(sprintf(sprintf("Variant: '%s'",$newVariant)));
				if ($newVariant->getId() == $variantId) {
					$this->logger->info("Variant not changed.");
					return $variant;
				}
			}
			$this->logger->info("New variant is the first of the list.");
			$newVariant= array_shift($variants);
		}

		return $newVariant;
	}
		
	/**
	 * 
	 * @param ProductCollection $collection
	 * @param array of AttributeOption $options
	 */
	public function findVariants(ProductCollection $collection, $options)
	{
		$this->logger->info(sprintf("Collection: %s",$collection));		
		$qb = $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant')
			->createQueryBuilder('v');
		$qb
			->innerJoin('YouppersProductBundle:VariantProperty','p', 'WITH', 'p.productVariant = v')
			->where('v.productCollection = :collection')
			->setParameter('collection', $collection)
			->where('v.productCollection = :collection')
			->andWhere('p.attributeOption = :option');

		$variantsId = null;
		foreach ($options as $option) {
			if ($this->debug) $this->logger->info(sprintf("Option: %s",$option));
							
			$optionVariantsEntities = $qb->setParameter('option',$option)->getQuery()->getResult();
						
			if (count($optionVariantsEntities) == 0) {
				$this->logger->warning("No variant found, invalid option " . $option);
				return null;
			}
			$optionVariantsId = array();
			foreach ($optionVariantsEntities as $optionVariantEntity) {
				$optionVariantsId[] = $optionVariantEntity->getId();
				if ($this->debug) $this->logger->info(sprintf("Variant: %s",$optionVariantEntity));				
			}
			if ($variantsId == null) {
				$variantsId = $optionVariantsId;
			} else {
				$variantsId = array_intersect($variantsId,$optionVariantsId);
				if (count($variantsId) == 0) {
					return array();
				}
			}
		}
		
		$qb = $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant')
			->createQueryBuilder('v');
		$qb->where($qb->expr()->in('v.id', $variantsId));
		return $qb->getQuery()->getResult();				
	}

	/**
	 * @param uuid $collectionId
	 * @param uuid $sessionId
	 * @return ProductCollection
	 */
	public function readCollection($collectionId, $sessionId = null)
	{	
		return $this->managerRegistry->getRepository('YouppersProductBundle:ProductCollection')->find($collectionId);
	}

	/**
	 * @param uuid $collectionId
	 * @return Collection AttributeOption List of options of all the variants of the collection
	 */
	public function readCollectionAttributes($collectionId)
	{
		$collection = $this->readCollection($collectionId);
		
		$repo = $this->managerRegistry->getRepository('YouppersProductBundle:AttributeOption');
		$qb = $repo
			->createQueryBuilder('o');
		$query = $qb
			->join('YouppersProductBundle:VariantProperty','p', 'WITH', 'p.attributeOption = o')
			->join('YouppersProductBundle:ProductVariant','v', 'WITH', 'p.productVariant = v')
			->where('v.productCollection = :collection')
			->setParameter('collection', $collection)
			->orderBy('o.attributeStandard', 'ASC')
			->addOrderBy('o.position', 'ASC')
			->getQuery();
		
		return $query->getResult();
		
	}	
}
