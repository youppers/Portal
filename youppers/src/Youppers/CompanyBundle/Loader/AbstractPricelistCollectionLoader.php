<?php

namespace Youppers\CompanyBundle\Loader;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Manager\ProductCollectionManager;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\CompanyBundle\Entity\Product;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;

abstract class AbstractPricelistCollectionLoader extends AbstractPricelistLoader {
	
	private $productCollectionRepository;
	
	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
		$this->em = $managerRegistry->getManager();
		$this->collectionManager = new ProductCollectionManager($this->em);
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
			$collection = $this->collectionManager->findByCode($brand, $collectionCode);			
			if (empty($collection) && $this->force && $this->createCollection) {				
				$collection = $this->collectionManager->create($brand, $collectionCode, $collectionCode, $this->getNewCollectionProductType($brand,$collectionCode));
				$this->collectionManager->save($collection);
				$this->logger->info(sprintf("Created collection with code '%s' for Brand '%s'",$collectionCode,$brand));
			}
			if (empty($collection)) {
				if ($this->force && $this->createCollection) {
					throw new \Exception(sprintf("Collection with code '%s' of Brand '%s' not found",$collectionCode,$brand));
				}
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
				$this->logger->error(sprintf("Product '%s' in collection '%s' instead of '%s'",$product,$variant->getProductCollection(),$collection));
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