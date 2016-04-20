<?php

namespace Youppers\ProductBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\ProductType;

class ProductCollectionManager extends BaseEntityManager
{
	
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\ProductBundle\Entity\ProductCollection', $registry);
	}

	public function _findByCode(Brand $brand, $code)
	{
		return $this->findOneBy(array('brand' => $brand, 'code' => $code));		
	}
	
	// cache
	private $brands = array();
	
	public function clear()
	{
		$this->brands = array();
	}

	public function flush()
	{
		$this->getEntityManager()->flush();
		$this->brands = array();
	}

	/**
	 * Search collection using code and aliases
	 * @param Brand $brand Collection Brand
	 * @param string $code Collection code
	 */	
	public function findByCode(Brand $brand, $code)
	{
		$brandCode = trim($brand->getCode());
		$code = trim($code);
		if (!array_key_exists($brandCode,$this->brands)) {
			$collections = $this->findBy(array('brand' => $brand));
			$brandCollections = array();
			foreach ($collections as $collection) {
				$collectionAlias = explode(';',$collection->getAlias());
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

	/**
	 * 
	 * @param Brand $brand
	 * @return ProductCollection[] of Brand
	 */
	public function findByBrand(Brand $brand)
	{
		return $this->findBy(array('brand' => $brand));		
	}
	
	public function createCollection(Brand $brand, $collectionName, $collectionCode, ProductType $productType)
	{
		$collection = $this->create();
		$collection->setBrand($brand);
		$collection->setName($collectionName);
		$collection->setCode($collectionCode);
		$collection->setAlias('');
		$collection->setEnabled(false);
		$collection->setProductType($productType);
		return $collection;		
	}
	
	public function save($collection, $andFlush = true)
	{
		parent::save($collection, $andFlush);
		$this->brands[$collection->getBrand()->getCode()][$collection->getCode()] = $collection;		
	}
	

}
