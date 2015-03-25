<?php

namespace Youppers\CompanyBundle\Service;

use Doctrine\Common\Collections\Criteria;
use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use JMS\Serializer\Serializer;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Stopwatch\Stopwatch;
use JMS\Serializer\Tests\Serializer\SerializableClass;

class PricelistLoaderMapper
{
	/**
	 * @JMS\Type("array<string, string>")
	 */
	protected $mapping = array();
	
	/**
	 * @JMS\Type("array<string, string>")
	 */
	private $data;

	public function setData($data)
	{
		$this->data=$data;
	}
	
	public function get($what)
	{
		if (array_key_exists($what,$this->mapping)) {
			$key = $this->mapping[$what];			
		} elseif (array_key_exists($what,$this->data)) {
			$key = $what;
		} else {
			return null;
		}
		return $this->data[$key];
	}
	
	public function remove($what)
	{
		if (array_key_exists($what,$this->mapping)) {
			$key = $this->mapping[$what];
		} elseif (array_key_exists($what,$this->data)) {
			$key = $what;
		} else {
			return null;
		}
		$value = $this->data[$key];
		unset($this->data[$key]);
		return $value;
	}
	
	public function key($what)
	{
		if (array_key_exists($what,$this->mapping)) {
			return $this->mapping[$what];
		} elseif (array_key_exists($what,$this->data)) {
			return $what;
		} else {
			return null;
		}
	}
}

class PricelistLoaderService extends ContainerAware
{
	protected $managerRegistry;
	protected $em;
	protected $logger;
	
	protected $pricelist;
	protected $company;
	protected $brand;
	protected $fs;
	 	
	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;
		$this->logger = $logger;
		$this->em = $managerRegistry->getManager();
	}
	
	public function setFs($fs)
	{
		$this->fs = $fs;
	}
	
	public function setPricelistByCode($code)
	{
		$this->pricelist = $this->getPricelistRepository()
			->findOneBy(array('code' => $code));
		
		if (empty($this->pricelist)) {
			throw new \Exception('Pricelist not found');				
		}
		
		$this->company = $this->pricelist->getCompany();
		
		$this->logger->debug(sprintf("Code: '%s' Pricelist: '%s'", $code, $this->pricelist));		
	}

	public function setBrandByCode($code)
	{
		if (empty($this->company)) {
			throw new \Exception('Set Company before Brand');
		}
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq("code", $code));
		
		$this->brand = $this->company->getBrands()->matching($criteria)->first();

		$this->logger->debug(sprintf("Code: '%s' Brand: '%s'", $code, $this->brand));
	}

	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Company
	 */
	protected function getCompanyRepository()
	{
		return $this->managerRegistry->getRepository('YouppersCompanyBundle:Company');
	}

	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Brand
	 */
	protected function getBrandRepository()
	{
		return $this->managerRegistry->getRepository('YouppersCompanyBundle:Brand');
	}
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Pricelist
	 */
	protected function getPricelistRepository()
	{
		return $this->managerRegistry->getRepository('YouppersCompanyBundle:Pricelist');
	}

	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Product
	 */
	protected function getProductRepository()
	{
		return $this->managerRegistry->getRepository('YouppersCompanyBundle:Product');
	}

	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Pricelist
	 */
	protected function getProductPriceRepository()
	{
		return $this->managerRegistry->getRepository('YouppersCompanyBundle:ProductPrice');
	}
	
	public function load($filename,$skip)
	{
		if (empty($this->pricelist)) {
			throw new \Exception("Pricelist MUST be set before loading prices.");			
		}
		$this->logger->info(sprintf("Loading pricelist from '%s'.",$filename));
		
		$file = new \SplFileObject($filename);
		
		$reader = new CsvReader($file, $this->fs);
		
		$numRows = 0;
		
		$reader->setHeaderRowNumber(0);
		
		$productRepository = $this->getProductRepository();
		$productPriceRepository = $this->getProductPriceRepository();
		$brandRepository = $this->getBrandRepository();
				
		$serializer = $this->container->get('serializer');		
		$mapper = $serializer->deserialize('{"mapping":'.$this->pricelist->getMapping().'}','Youppers\CompanyBundle\Service\PricelistLoaderMapper','json');

		$this->logger->info("Using mapper: " . print_r($mapper,true));

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
		
		$this->em->getConnection()->getConfiguration()->setSQLLogger(null);

		$stopwatch = new Stopwatch();
		// Inizia l'evento chiamato 'nomeEvento'
		$stopwatch->start('load');
		foreach ($reader as $row) {

			$numRows++;
			if ($numRows <= $skip) {
				continue;
			}
			
			$mapper->setData($row);
			
			$data = array();
			
			if (empty($this->brand)) {
				$brandCode = $mapper->remove('brand');
				if (empty($brandCode)) {
					throw new \Exception(sprintf("Brand column MUST be in the column '%s' OR must be set",$mapper->key('brand')));
				}
				$brand = $brandRepository->findOneBy(array('company' => $this->company, 'code' => $brandCode));
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
						
			$product = $productRepository
				->findOneBy(array('brand' => $brand, 'code' => $productCode));
				
			if (empty($product)) {
				$className =$productRepository->getClassName();  
				$product = new $className;
				$product->setBrand($brand);
				$product->setCode($productCode);
			}

			$name = $mapper->remove('name');			
			$product->setName($name);
			
			if (empty($product->getId())) {
				$this->em->persist($product);
			}

			$price = $productPriceRepository
				->findOneBy(array('product' => $product, 'pricelist' => $this->pricelist));
			if (!empty($price)) {
				throw new \Exception(sprintf("Duplicated price at row %d: %s",$numRows,implode(',',$row)));
			}
			
			$className = $productPriceRepository->getClassName();
			$price = new $className;
			$price->setPriceList($this->pricelist);
			$price->setProduct($product);
			$price->setPrice($mapper->remove('price'));
			$price->setUom($mapper->remove('uom'));
			try {
				$price->setInfo($serializer->serialize($mapper, 'json'));				
			} catch (Exception $e) {
				$this->logger->critical(sprintf("At row %d",$numRows),$e);
			}
			
			if ($numRows % 500 == 0) {
				$this->logger->debug(sprintf("Read %d rows",$numRows));
				$this->em->flush();
				$this->em->clear(get_class($price));
			}				
		}
		$event = $stopwatch->stop('load');		
		$this->logger->info(sprintf("Load done, read %d rows in %d mS",$numRows,$event->getDuration()));
		
		$this->em->flush();
				
	}
}
