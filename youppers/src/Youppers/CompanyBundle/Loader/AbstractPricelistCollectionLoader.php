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
	 
	protected function getCollectionByCode(Brand $brand,$code)
	{
		return $this->getProductCollectionRepository()
			->findOneBy(array('brand' => $brand, 'code' => $code));
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
			if (empty($collection) && $this->force) {
				$collection = new ProductCollection();
				$collection->setBrand($brand);
				$collection->setName($collectionCode);
				$collection->setCode($collectionCode);
				$collection->setEnabled(false);
				$collection->setProductType($this->getNewCollectionProductType($brand,$collectionCode));
				$this->em->persist($collection);
				$this->em->flush();
				$this->logger->info(sprintf("Created collection with code '%s' for Brand '%s'",$collectionCode,$brand));
			}				
			if (empty($collection)) {
				throw new \Exception(sprintf("Collection with code '%s' of Brand '%s' not found",$collectionCode,$brand));
			}	
			//$this->logger->info("Collection: " . $collection);				
		}
		
		$this->handleVariant($collection, $product);
		
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
		if (empty($variant) && $this->force) {
			$variant = new ProductVariant();
			$collection->addProductVariant($variant);
			$variant->setProduct($product);
			$variant->setEnabled(false);
			$variant->setPosition($this->numRows);
			$this->em->persist($variant);
			$this->logger->info(sprintf("Created variant '%s'",$variant));
		}
	}
}