<?php

namespace Youppers\CompanyBundle\Loader;

use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Reader\Factory\CsvReaderFactory;
use Symfony\Component\Stopwatch\Stopwatch;
use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Entity\ProductPrice;

abstract class AbstractPricelistLoader extends AbstractLoader
{
	protected $pricelist;
	
	private $disabledBrands = array();
	
	protected $mapper;
	
	protected $numRows;
	
	private $skip;
	
	public function setPricelist($pricelist)
	{
		$this->pricelist = $pricelist;
		
		$this->company = $pricelist->getCompany();
	}
	
	private $createProduct = false;
	
	public function setCreateProduct($createProduct)
	{
		$this->createProduct = $createProduct;
	}
	
	public function load($filename,$skip=0)
	{
		$this->skip = $skip;
		
		if (empty($this->pricelist)) {
			throw new \Exception("Pricelist MUST be set before loading prices.");
		}
	
		$this->logger->info(sprintf("Loading pricelist from '%s'.",$filename));
		if ($this->enable) {
			$this->logger->info("And enable products");
		}
	
		$reader = $this->createReader($filename);
	
		$this->numRows = 0;
	
		$reader->setHeaderRowNumber(0);
	
		$this->serializer = $this->container->get('serializer');
	
		$this->mapper = $this->createMapper();
	
		$this->logger->info("Using mapper: " . $this->mapper);
		
		if ($skip>0) {
			$this->logger->info(sprintf("Skip '%d' rows",$skip));
		} else {
			$query = $this->em->createQuery('DELETE Youppers\CompanyBundle\Entity\ProductPrice p WHERE p.pricelist = :pricelist');
			$query->setParameter('pricelist', $this->pricelist);
			if ($this->force) {
				$numDeleted = $query->execute();
				if ($numDeleted > 0) {
					$this->logger->info(sprintf("Deleted '%d' rows before reloading pricelist.",$numDeleted));
				}
			} else {
				$this->logger->info("SQL: " . $query->getSql());
			}
		}
			
		// speed up
		if (!$this->debug) {
			$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
		}
	
		$stopwatch = new Stopwatch();
		$stopwatch->start('load');
		foreach ($reader as $row) {
	
			$this->numRows++;
			if ($this->numRows <= $skip) {
				continue;
			}
			
			$this->hanldleRow($row);
				
			if ($this->numRows % 500 == 0) {
				$this->logger->info(sprintf("Read %d rows",$this->numRows));
				if ($this->force) {
					$this->em->flush();
				} else {
					//
				}
				$this->em->clear($this->getProductPriceRepository()->getClassName());
			}
		}
		
		$this->em->flush();
		
		$event = $stopwatch->stop('load');
		$this->logger->info(sprintf("Load done, read %d rows in %d mS",$this->numRows,$event->getDuration()));
	}
		
	protected function handleBrand()
	{
		$brandCode = $this->mapper->remove('brand');
		if (null !== $brandCode) {
			$this->setBrandByCode($brandCode);
		}
		
		if (empty($this->brand)) {
			if (empty($brandCode)) {
				throw new \Exception(sprintf("Brand column MUST be in the column '%s' OR must be set manually",$this->mapper->key('brand')));
			}
			$brand = $this->getBrandRepository()->findOneBy(array('company' => $this->company, 'code' => $brandCode));
			if (empty($brand)) {
				throw new \Exception(sprintf("At row '%d': Brand '%s' not found for Company '%s'",$this->numRows,$brandCode,$this->company));
			}
		} else {
			$brand = $this->brand;
		}
			
		if ($this->skip == 0 && $this->force && $this->enable && !array_key_exists($brand->getId(),$this->disabledBrands)) {
			$query = $this->em->createQuery('UPDATE Youppers\CompanyBundle\Entity\ProducT p SET p.enabled = false WHERE p.brand = :brand');
			$query->setParameter('brand', $brand);
			$query->execute();
			$this->disabledBrands[$brand->getId()] = $brand;
			$this->logger->info(sprintf("Disabled all products of brand '%s'",$brand));
		}		
		return $brand;
	}
	
	/**
	 * 
	 * @param Brand $brand
	 * @throws \Exception
	 * @return Product
	 */
	protected function handleProduct(Brand $brand)
	{
		$productCode = $this->mapper->remove('code');
		
		if (empty($productCode)) {
			throw new \Exception(sprintf("Product code not found in the column '%s'",$this->mapper->key('code')));
		}
		
		$product = $this->getProductRepository()
			->findOneBy(array('brand' => $brand, 'code' => $productCode));
		
		if (empty($product)) {
			$className = $this->getProductRepository()->getClassName();
			$product = new $className;
			$product->setBrand($brand);
			$product->setCode($productCode);
		} elseif (!$this->force) {
			$product = clone $product;
		}
		
		if ($this->enable) {
			$product->setEnabled(true);
		}
		$name = $this->mapper->remove('name');
		$product->setName($name);
			
		$productGtin = $this->mapper->remove('gtin');
		if (!empty($productGtin)) {
			$product->setGtin($productGtin);
		}
		
		$info = json_encode($this->mapper->getData());
		$product->setInfo($info);
		
		if (empty($product->getId())) {
			if ($this->force && $this->createProduct) {
				$this->em->persist($product);
			} else {
				$this->logger->info("New: " . $product);
			}
		} elseif (!$this->force) {
			$this->logger->debug("Updated: " . $product);
		}		
		
		return $product;
	}
	
	/**
	 * 
	 * @param Product $product
	 * @throws \Exception
	 * @return ProductPrice
	 */
	protected function handlePrice(Product $product)
	{
		$price = $this->getProductPriceRepository()
		->findOneBy(array('product' => $product, 'pricelist' => $this->pricelist));
		if ($this->force && !empty($price)) {
			throw new \Exception(sprintf("Duplicated price at row %d: %s",$this->numRows,implode(',',$row)));
		}
		
		$className = $this->getProductPriceRepository()->getClassName();
		$price = new $className;
		$price->setPriceList($this->pricelist);
		$price->setProduct($product);
		$price->setPrice(strtr($this->mapper->remove('price'),array(" " => "", "€" => "","." => "","," => ".")));
		$price->setUom($this->mapper->remove('uom'));
		if ($this->force) {
			$this->em->persist($price);
		}
		return $price;
	}

	public function hanldleRow($row) {
		
		//parent::hanldleRow($row);
		
		$this->mapper->setData($row);
		
		$brand = $this->handleBrand();

		if ($brand) {
			$product = $this->handleProduct($brand);
			if ($product) {
				$price = $this->handlePrice($product);
			}
		}
		
	}
	
	
}
