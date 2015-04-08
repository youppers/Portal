<?php

namespace Youppers\CompanyBundle\Loader;

use Doctrine\Common\Collections\Criteria;
use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Youppers\CompanyBundle\YouppersCompanyBundle;
use Monolog\Logger;
use Youppers\CompanyBundle\Entity\ProductPrice;

abstract class AbstractLoader extends ContainerAware
{
	/**
	 * @return \Youppers\CompanyBundle\Loader\LoaderMapper
	 */
	public abstract function createMapper();
	
	protected $managerRegistry;
	protected $em;
	protected $logger;

	private $companyRepository;
	protected $company;
	
	private $brandRepository;
	protected $brand;
	
	private $productRepository;
	protected $product;
	
	private $productPriceRepository;
	
	protected $fs;
	protected $enable;
	protected $force;
	
	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
		$this->em = $managerRegistry->getManager();
	}
	
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
	
	public function setFs($fs)
	{
		$this->fs = $fs;
	}
	
	public function setEnable($enable) {
		$this->enable = $enable;
	}
	
	public function setForce($force) {
		$this->force = $force;
	}

	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Company
	 */
	protected function getCompanyRepository()
	{
		if (null === $this->companyRepository) {
			$this->companyRepository = $this->managerRegistry->getRepository('YouppersCompanyBundle:Company');
		}
		return $this->companyRepository;
	}

	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Brand
	 */
	protected function getBrandRepository()
	{
		if (null === $this->brandRepository) {
			$this->brandRepository = $this->managerRegistry->getRepository('YouppersCompanyBundle:Brand');
		}
		return $this->brandRepository;
	}
	
	/**
	 * @return \Doct8rine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Product
	 */
	protected function getProductRepository()
	{
		if (null === $this->productRepository) {
			$this->productRepository = $this->managerRegistry->getRepository('YouppersCompanyBundle:Product');
		}
		return $this->productRepository;
	}
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:ProductPrice
	 */
	protected function getProductPriceRepository()
	{
		if (null === $this->productPriceRepository) {
			$this->productPriceRepository = $this->managerRegistry->getRepository('YouppersCompanyBundle:ProductPrice');
		}
		return $this->productPriceRepository;
	}
		
	/**
	 * Set the brand of the company 
	 * @param string $code The Brand Code
	 * @throws \Exception
	 */
	public function setBrandByCode($code)
	{
		if (empty($this->company)) {
			throw new \Exception('Must Set Company before Brand');
		}
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq("code", $code));
		
		$this->brand = $this->company->getBrands()->matching($criteria)->first();
		
		if (empty($this->brand)) {
			throw new \Exception(sprintf("Brand '%s' not found",$code));
		}
		
		$this->logger->debug(sprintf("Code: '%s' Brand: '%s'", $code, $this->brand));
	}

	/**
	 * Set the product of the brand
	 * @param string $code The Product Code
	 * @throws \Exception
	 */
	public function setProductByCode($code)
	{
		if (empty($this->brand)) {
			throw new \Exception('Must Set Brafore before Product');
		}
		$criteria = Criteria::create()
		->where(Criteria::expr()->eq("code", $code));
	
		$this->product = $this->brand->getProducts()->matching($criteria)->first();
	
		if (empty($this->producct)) {
			throw new \Exception(sprtintf("Product '%s' not found",$code));
		}
	
		$this->logger->debug(sprintf("Code: '%s' Product: '%s'", $code, $this->product));
	}
	
	public function createReader($filename)
	{
		return $this->createCsvReader($filename);
	}
	
	public function createCsvReader($filename)
	{
		$file = new \SplFileObject($filename);	
		return new CsvReader($file, $this->fs);
	}
	
	/**
	 * 
	 * @param string $filename Where to read
	 * @param int $skip How many rows skip
	 * @param boolean $force Write data on database
	 * @param boolean $enable Enable product
	 */
	public abstract function load($filename,$skip);	

}
