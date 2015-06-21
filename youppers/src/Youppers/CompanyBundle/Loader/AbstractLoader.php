<?php

namespace Youppers\CompanyBundle\Loader;

use Doctrine\Common\Collections\Criteria;
use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Youppers\CompanyBundle\Entity\Company;
use Youppers\CompanyBundle\Manager\ProductManager;
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

    /**
     * @param ManagerRegistry $managerRegistry
     * @deprecated
     */
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

	protected $debug = false;
	
	public function setDebug($debug)
	{
		$this->debug = $debug;
	}
		
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Company
     * @deprecated
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
     * @deprecated
	 */
	protected function getBrandRepository()
	{
		if (null === $this->brandRepository) {
			$this->brandRepository = $this->managerRegistry->getRepository('YouppersCompanyBundle:Brand');
		}
		return $this->brandRepository;
	}
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersCompanyBundle:Product
     * @deprecated
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
     * @deprecated
	 */
	protected function getProductPriceRepository()
	{
		if (null === $this->productPriceRepository) {
			$this->productPriceRepository = $this->managerRegistry->getRepository('YouppersCompanyBundle:ProductPrice');
		}
		return $this->productPriceRepository;
	}

    public function setCompany(Company $company) {
        $this->company = $company;
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

    /*
    public function checkUniqueGtin($product) {
        $gtin = $product->getGtin();
        if ($gtin == null) {
            return true;
        }
        $check = $this->productManager->findOneByGtin($gtin);
        if ($check == null || $check == $product) {
            return true;
        } else {
            return false;
        }
    }
	*/

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
