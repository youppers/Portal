<?php

namespace Youppers\ProductBundle\Manager;

use Youppers\CompanyBundle\Entity\Brand;
use Doctrine\ORM\EntityManager;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\ProductType;

class ProductCollectionManager
{
	/**
	 * @var EntityManager
	 */
	protected $em;
	
	/**
	 * @var EntityRepository
	 */
	protected $repository;
	
	/**
	 * @var string
	 */
	protected $class;
	
	/**
	 * Constructor.
	 *
	 * @param EntityManager            $em
	 * @param string                   $class
	 */
	public function __construct(EntityManager $em, $class = 'YouppersProductBundle:ProductCollection')
	{
		$this->em = $em;
		$this->repository = $em->getRepository($class);
	
		$metadata = $em->getClassMetadata($class);
		$this->class = $metadata->name;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function findById($id)
	{
		return $this->repository->find($id);
	}

	// cache
	private $brands = array();
	
	/**
	 *
	 * @param Brand $brand Collection Brand
	 * @param string $code Collection code
	 */	
	public function findByCode(Brand $brand, $code)
	{
		$brandCode = trim($brand->getCode());
		$code = trim($code);
		if (!array_key_exists($brandCode,$this->brands)) {
			$collections = $this->repository->findBy(array('brand' => $brand));
			$brandCollections = array();
			foreach ($collections as $collection) {
				$collectionAlias = explode(',',$collection->getAlias());
				$collectionCode = trim($collection->getCode());
				foreach ($collectionAlias as $alias) {
					$alias = trim($alias);
					if (!empty($alias)) {
						if (array_key_exists($alias,$brandCollections)) {
							throw new \Exception(sprintf("Duplicated alias '%s' in collection '%s",$alias,$collection));
						}
						$brandCollections[$alias] = $collection;
					}
				}
				if (!array_key_exists($collectionCode,$brandCollections)) {
					$brandCollections[$collectionCode] = $collection;
				}
			}
			$this->brands[$brandCode] = $brandCollections;
		}
		if (array_key_exists($code,$this->brands[$brandCode])) {
			if (($collection = $this->brands[$brandCode][$code]) === false) {
				return null;
			} else {
				return $collection;
			}
		} else {
			$this->brands[$brand->getCode()][$code] = false; // cache negative hit
			return null;
		}
	}
	
	public function create(Brand $brand, $collectionName, $collectionCode, ProductType $productType)
	{
		$className = $this->getClass();
		$collection = new $className;
		$collection->setBrand($brand);
		$collection->setName($collectionName);
		$collection->setCode($collectionCode);
		$collection->setAlias('');
		$collection->setEnabled(false);
		$collection->setProductType($productType);
		return $collection;		
	}
	
	/**
	 * Saves a comment to the persistence backend used.
	 * @TODO use interface
	 */	
	public function save(ProductCollection $collection)
	{
		$this->em->persist($collection);
		$this->em->flush();
		$this->brands[$collection->getBrand()->getCode()][$collection->getCode()] = $collection;		
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getClass()
	{
		return $this->class;
	}

}
