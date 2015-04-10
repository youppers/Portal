<?php

namespace Youppers\CompanyBundle\Loader;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\CompanyBundle\Entity\Product;
use Doctrine\Common\Collections\Criteria;

abstract class AbstractPricelistCollectionLoader extends AbstractPricelistLoader {
	
	private $productCollectionRepository;
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersProductBundle:ProductCollection
	 */
	protected function getProductCollectionRepository()
	{
		if (null === $this->productCollectionRepository) {
			$this->productCollectionRepository = $this->managerRegistry->getRepository('YouppersProductBundle:ProductCollection');
		}
		return $this->productCollectionRepository;
	}

	protected abstract function getNewCollectionProductType(Brand $brand, $code);

	private $brands = array();
	
	private $createCollection = false;
	
	public function setCreateCollection($createCollection)
	{
		$this->createCollection = $createCollection;
	} 

	private $createVariant = false;
	
	public function setCreateVariant($createVariant)
	{
		$this->createVariant = $createVariant;
	}
	
	protected function getCollectionByCode(Brand $brand,$code)
	{
		$brandCode = trim($brand->getCode());
		$code = trim($code);
		if (!array_key_exists($brandCode,$this->brands)) {
			$collections = $this->getProductCollectionRepository()
				->findBy(array('brand' => $brand));
			$brandCollections = array();
			foreach ($collections as $collection) {
				$collectionAlias = explode(',',$collection->getAlias());
				$collectionCode = trim($collection->getCode());
				foreach ($collectionAlias as $alias) {					
					$alias = trim($alias);
					if (!empty($alias)) {
						if (array_key_exists($alias,$brandCollections)) {
							dump($brandCollections);
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
			$this->logger->info(sprintf("Cached collections for brand '%s'",$brand));
			if ($this->debug) {
				dump($brandCollections);
			}
		}
		if (array_key_exists($code,$this->brands[$brandCode])) {
			return $this->brands[$brandCode][$code];
		} else {
			return null;
		}
	}
	
	private $productTypeRepository;
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersProductBundle:ProductType
	 */
	protected function getProductTypeRepository()
	{
		if (null === $this->productTypeRepository) {
			$this->productTypeRepository = $this->managerRegistry->getRepository('YouppersProductBundle:ProductType');
		}
		return $this->productTypeRepository;
	}
	
	
	private $productVariantRepository;
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersProductBundle:ProductVariant
	 */
	protected function getProductVariantRepository()
	{
		if (null === $this->productVariantRepository) {
			$this->productVariantRepository = $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant');
		}
		return $this->productVariantRepository;
	}
	
	
	protected function handleProduct(Brand $brand)
	{
		$product = parent::handleProduct($brand);
		
		$collectionCode = $this->mapper->get('collection');
		if ($collectionCode !== null) {
			$collection = $this->getCollectionByCode($brand, $collectionCode);
			if (empty($collection) && $this->force && $this->createCollection) {
				$collection = new ProductCollection();
				$collection->setBrand($brand);
				$collection->setName($collectionCode);
				$collection->setCode($collectionCode);
				$collection->setAlias('');
				$collection->setEnabled(false);
				$collection->setProductType($this->getNewCollectionProductType($brand,$collectionCode));
				$this->em->persist($collection);
				$this->em->flush();
				$this->logger->info(sprintf("Created collection with code '%s' for Brand '%s'",$collectionCode,$brand));
				$this->brands[$brand->getCode()][$collectionCode] = $collection;
			}
			if ($collection === false) {
				// cached and not created
			} elseif (empty($collection)) {
				if ($this->force) {
					throw new \Exception(sprintf("Collection with code '%s' of Brand '%s' not found",$collectionCode,$brand));
				} else {
					$this->brands[$brand->getCode()][$collectionCode] = false; // warn only once
				}
				$this->logger->warning(sprintf("Collection with code '%s' of Brand '%s' not found",$collectionCode,$brand));
			} else {
				$this->handleVariant($collection, $product);
			}
			//$this->logger->info("Collection: " . $collection);				
		}
		
		return $product;
	}
	
	protected function handleVariant(ProductCollection $collection, Product $product)
	{
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq("product", $product));		
		$variant = $collection->getProductVariants()->matching($criteria)->first();
		if (empty($variant)) {		
			$variant = $this->getProductVariantRepository()
				->findOneBy(array('product' => $product));
			if (!empty($variant)) {
				throw new \Exception(sprintf("Product '%s' in collection '%s' instead of '%s'",$product,$variant->getProductCollection(),$collection));
			}
		}
		if (empty($variant)) {
			if ($this->force && $this->createVariant) {
				$variant = new ProductVariant();
				$variant->setProduct($product);
				$variant->setEnabled(false);
				$variant->setPosition($this->numRows);
				$collection->addProductVariant($variant);
				$this->em->persist($variant);
				$this->logger->info(sprintf("Created variant '%s'",$variant));
			} else {
				$this->logger->info(sprintf("New variant '%s' - '%s'",$collection,$product->getNameCode()));
			}
		}
		return $variant;
	}
}