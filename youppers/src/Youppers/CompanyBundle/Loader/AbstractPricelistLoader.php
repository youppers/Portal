<?php

namespace Youppers\CompanyBundle\Loader;

use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Reader\Factory\CsvReaderFactory;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class AbstractPricelistLoader extends AbstractLoader
{
	protected $pricelist;
	
	public function setPricelist($pricelist)
	{
		$this->pricelist = $pricelist;
		
		$this->company = $pricelist->getCompany();
	}
	
	public function load($filename,$skip,$enable)
	{
		if (empty($this->pricelist)) {
			throw new \Exception("Pricelist MUST be set before loading prices.");
		}
	
		$this->logger->info(sprintf("Loading pricelist from '%s'.",$filename));
		if ($enable) {
			$this->logger->info("And enable products");
		}
	
		$reader = $this->createReader($filename);
	
		$numRows = 0;
	
		$reader->setHeaderRowNumber(0);
	
		$serializer = $this->container->get('serializer');
	
		$mapper = $this->createMapper();
	
		$this->logger->info("Using mapper: " . $mapper);
	
		if ($skip>0) {
			$this->logger->info(sprintf("Skip '%d' rows",$skip));
		} else {
			$query = $this->em->createQuery('DELETE Youppers\CompanyBundle\Entity\ProductPrice p WHERE p.pricelist = :pricelist');
			$query->setParameter('pricelist', $this->pricelist);
			$numDeleted = $query->execute();
			if ($numDeleted > 0) {
				$this->logger->info(sprintf("Deleted '%d' rows before reloading pricelist.",$numDeleted));
			}
		}
	
		// speed up
		$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
	
		$stopwatch = new Stopwatch();
		$stopwatch->start('load');
		foreach ($reader as $row) {
	
			$numRows++;
			if ($numRows <= $skip) {
				continue;
			}
				
			$mapper->setData($row);
				
			$brandCode = $mapper->remove('brand');
			if (null !== $brandCode) {
				$this->setBrandByCode($brandCode);
			}
	
			if (empty($this->brand)) {
				if (empty($brandCode)) {
					throw new \Exception(sprintf("Brand column MUST be in the column '%s' OR must be set manually",$mapper->key('brand')));
				}
				$brand = $this->getBrandRepository()->findOneBy(array('company' => $this->company, 'code' => $brandCode));
				if (empty($brand)) {
					throw new \Exception(sprintf("At row '%d': Brand '%s' not found for Company '%s'",$numRows,$brandCode,$this->company));
				}
			} else {
				$brand = $this->brand;
			}
				
			$productCode = $mapper->remove('code');
				
			if (empty($productCode)) {
				throw new \Exception(sprintf("Code column MUST be in the column '%s'",$mapper->key('code')));
			}
	
			$this->product = $this->getProductRepository()
			->findOneBy(array('brand' => $brand, 'code' => $productCode));
	
			if (empty($this->product)) {
				$className = $this->getProductRepository()->getClassName();
				$this->product = new $className;
				$this->product->setBrand($brand);
				$this->product->setCode($productCode);
			}
	
			if ($enable) {
				$this->product->setEnabled(true);
			}
			$name = $mapper->remove('name');
			$this->product->setName($name);
			
			$productGtin = $mapper->remove('gtin');
			if (!empty($productGtin)) {
				$this->product->setGtin($productGtin);
			}
				
			if (empty($this->product->getId())) {
				$this->em->persist($this->product);
			}
	
			$price = $this->getProductPriceRepository()
			->findOneBy(array('product' => $this->product, 'pricelist' => $this->pricelist));
			if (!empty($price)) {
				throw new \Exception(sprintf("Duplicated price at row %d: %s",$numRows,implode(',',$row)));
			}
				
			$className = $this->getProductPriceRepository()->getClassName();
			$price = new $className;
			$price->setPriceList($this->pricelist);
			$price->setProduct($this->product);
			$price->setPrice($mapper->remove('price'));
			$price->setUom($mapper->remove('uom'));
			try {
				$price->setInfo($serializer->serialize($mapper, 'json'));
			} catch (Exception $e) {
				$this->logger->critical(sprintf("At row %d",$numRows),$e);
			}
			$this->em->persist($price);
				
			if ($numRows % 500 == 0) {
				$this->logger->info(sprintf("Read %d rows",$numRows));
				$this->em->flush();
				$this->em->clear(get_class($price));
			}
		}
		$event = $stopwatch->stop('load');
		$this->logger->info(sprintf("Load done, read %d rows in %d mS",$numRows,$event->getDuration()));
	
		$this->em->flush();
	
	}
	
}
