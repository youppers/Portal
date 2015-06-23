<?php

namespace Youppers\CompanyBundle\Loader;

use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Reader\Factory\CsvReaderFactory;
use Symfony\Component\Stopwatch\Stopwatch;
use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Entity\ProductPrice;
use Youppers\CompanyBundle\Manager\PricelistManager;
use Youppers\CompanyBundle\Manager\ProductPriceManager;

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

    /**
     * @var PricelistManager
     */
    private $pricelistManager;

    /**
     * @return PricelistManager
     */
    protected function getPricelistManager() {
        if (empty($this->pricelistManage)) {
            $this->pricelistManage = $this->container->get('youppers.company.manager.pricelist');
        }
        return $this->pricelistManage;
    }

    /**
     * @var ProductPriceManager
     */
    private $productPriceManager;

    /**
     * @return ProductPriceManager
     */
    protected function getProductPriceManager() {
        if (empty($this->productPriceManager)) {
            $this->productPriceManager = $this->container->get('youppers.company.manager.product_price');
        }
        return $this->productPriceManager;
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
			
			$this->handleRow($row);
				
			if ($this->numRows % self::BATCH_SIZE == 0) {
				$this->logger->info(sprintf("Read %d rows",$this->numRows));
                $this->batch();
			}
		}
		
		$this->batch();
		
		$event = $stopwatch->stop('load');
		$this->logger->info(sprintf("Load done, read %d rows in %d mS",$this->numRows,$event->getDuration()));
	}

    public function batch()
    {
        if ($this->force) {
            $this->getProductManager()->getObjectManager()->flush();
            $this->getProductPriceManager()->getObjectManager()->flush();
        } else {
            $this->getProductManager()->getObjectManager()->clear();
            $this->getProductPriceManager()->getObjectManager()->clear();
        }
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
			$brand = $this->getBrandManager()->findOneBy(array('company' => $this->company, 'code' => $brandCode));
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
		
		$product = $this->getProductManager()
			->findOneBy(array('brand' => $brand, 'code' => $productCode));
		
		if (empty($product)) {
			$product = $this->getProductManager()->create();
			$product->setBrand($brand);
			$product->setCode($productCode);
		}
		
		if ($this->enable) {
			$product->setEnabled(true);
		}
		$name = $this->mapper->remove('name');
		if (empty($name) && empty($product->getName())) {
			throw new \Exception(sprintf("Product name not found in the column '%s'",$this->mapper->key('name')));
		}
		$product->setName($name);
			
		$productGtin = $this->mapper->remove('gtin');
		if (!empty($productGtin)) {
            $product->setGtin($productGtin);
            if (!$this->checkUniqueGtin($product)) {
                $this->logger->error(sprintf("Duplicated gtin '%s'",$productGtin));
                $product->setGtin(null);
            }
		}
		
		$info = json_encode($this->mapper->getData());
		$product->setInfo($info);
		
		if (empty($product->getId())) {
            $this->getProductManager()->save($product,false);
            $this->logger->info("New product: " . $product);
		} else {
			$this->logger->debug("Updated product: " . $product);
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
		$price = $this->getProductPriceManager()
		    ->findOneBy(array('product' => $product, 'pricelist' => $this->pricelist));
		if ($this->force && !empty($price)) {
			throw new \Exception(sprintf("Duplicated price at row %d: %s",$this->numRows,implode(',',$this->mapper)));
		}
		
		$price = $this->getProductPriceManager()->create();
		$price->setPriceList($this->pricelist);
		$price->setProduct($product);
		$price->setPrice(strtr($this->mapper->remove('price'),array(" " => "", "€" => "","." => "","," => ".")));
		$price->setUom($this->mapper->remove('uom'));
		$this->getProductPriceManager()->save($price,false);
		return $price;
	}

	public function handleRow($row) {
		
		$this->mapper->setData($row);
		
		$brand = $this->handleBrand();

		if ($brand) {
			$product = $this->handleProduct($brand);
			$price = $this->handlePrice($product);
		}
		
	}
	
	
}
