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
use Symfony\Component\Stopwatch\Stopwatch;

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
	public function readVariant($variantId, $sessionId =  null)
	{
		return $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant')->find($variantId);
	}
	
	/**
	 * list variants matching options
	 *    
	 * @param uuid $collectionId The collection where to search
	 * @param array $options Ids of the selected options
	 * @param string $sessionId
	 * @throws NotFoundHttpException if one option is invalid 
	 * @return array of ProductVariant (eventually empty)
	 */
	public function listVariants($collectionId, $options, $sessionId =  null)
	{
		$collection = $this->managerRegistry->getRepository('YouppersProductBundle:ProductCollection')->find($collectionId);
		if (empty($collection)) {
			throw new NotFoundHttpException("Invalid collection id");
		}

		if (count($options) == 0) {
			return $this->listCollectionVariants($collection);
		}		
		
		$optionsRepo = $this->managerRegistry->getRepository('YouppersProductBundle:AttributeOption');
		
		$optionsEntities = array();
		foreach ($options as $option) {
			$option = trim($option);
			if (empty($option)) {
				continue;
			}
			$optionEntity = $optionsRepo->find($option);
			if ($optionEntity) {
				$optionsEntities[] = $optionEntity;
			} else {
				$this->logger->error("Invalid option id=".$option);
				throw new NotFoundHttpException("Invalid option");
			}
		}
		
		if (count($optionsEntities) == 0) {
			return $this->listCollectionVariants($collection);
		}
		
		return $this->findVariants($collection, $optionsEntities);		
	}
		
	
	/**
	 * List all the variants of the collection
	 * @param ProductCollection $collection
	 */
	protected function listCollectionVariants(ProductCollection $collection)
	{
		return $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant')
			->createQueryBuilder('v')
			->where('v.productCollection = :collection')
			->setParameter('collection', $collection)
			->getQuery()->getResult();
	}
	
	/**
	 * 
	 * @param ProductCollection $collection
	 * @param array of AttributeOption $options
	 * @param boolean $relaxed Perform the search relaxing non variant options if none found with all options 
	 * TODO use mongodb to search entities
	 */
	public function findVariants(ProductCollection $collection, $options, $relaxed = true)
	{
		$stopwatch = new Stopwatch();
		$stopwatch->start('findVariants');
		
		$this->logger->info(sprintf("Collection: %s",$collection));		
		$qb = $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant')
			->createQueryBuilder('v');
		$qb
			->innerJoin('YouppersProductBundle:VariantProperty','p', 'WITH', 'p.productVariant = v')
			->where('v.productCollection = :collection')
			->setParameter('collection', $collection)
			->where('v.productCollection = :collection')
			->andWhere('p.attributeOption = :option');

		$typeVariantsId = array();
		$numVariants = 0;
		foreach ($options as $option) {
			$this->logger->info(sprintf("Option: %s",$option));
							
			$optionVariantsEntities = $qb->setParameter('option',$option)->getQuery()->getResult();

			$numVariants += count($optionVariantsEntities);
			
			if (count($optionVariantsEntities) == 0) {
				$this->logger->warning("No variant found with option: " . $option);
			}
			$optionVariantsId = array();
			foreach ($optionVariantsEntities as $optionVariantEntity) {
				$optionVariantsId[] = $optionVariantEntity->getId();
				if ($this->debug) $this->logger->debug(sprintf("Variant: %s",$optionVariantEntity));				
			}
			$typeVariantsId[$option->getAttributeStandard()->getAttributeType()->getId()] = $optionVariantsId; 
		}

		// primo tentativo: tutte le opzioni
		$variantsId = null;
		foreach ($typeVariantsId as $optionVariantsId) {
			if ($variantsId == null) {
				$variantsId = $optionVariantsId;
			} else {
				$variantsId = array_intersect($variantsId,$optionVariantsId);
				if (count($variantsId) == 0) {
					break;
				}
			}	
		}
		
		// secondo tentativo: solo opzioni che sono variante e abilitate
		if ($relaxed && count($variantsId) == 0) {
			$variantsId = null;
			foreach ($collection->getProductType()->getProductAttributes() as $productTypeAttribute) {
				if ((!$productTypeAttribute->getVariant() || !$productTypeAttribute->getEnabled()) 
						&& array_key_exists($productTypeAttribute->getAttributeType()->getId(),$typeVariantsId)) {
					$this->logger->info(sprintf("Relaxing option '%s'",$productTypeAttribute->getAttributeType()));
					unset($typeVariantsId[$productTypeAttribute->getAttributeType()->getId()]);
				}
			}
			foreach ($typeVariantsId as $optionVariantsId) {
				if ($variantsId == null) {
					$variantsId = $optionVariantsId;
				} else {
					$variantsId = array_intersect($variantsId,$optionVariantsId);
					if (count($variantsId) == 0) {
						break;
					}
				}
			}
		}

		if (count($variantsId) > 0) {
			$qb = $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant')
				->createQueryBuilder('v');
			$qb->where($qb->expr()->in('v.id', $variantsId));
			$variants = $qb->getQuery()->getResult();
		} else {
			$variants = array();
		}
				
		$event = $stopwatch->stop('findVariants');
		$this->logger->info(sprintf("findVariants done, found %d match from %d variants using %d options in %d mS",count($variants),$numVariants, count($options), $event->getDuration()));
		
		return $variants;		
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
	public function readCollectionAttributes($collectionId, $sessionId = null)
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
