<?php

namespace Youppers\CompanyBundle\Loader;

use Doctrine\Common\Collections\Criteria;
use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Youppers\CompanyBundle\Entity\Company;
use Youppers\CompanyBundle\Manager\BrandManager;
use Youppers\CompanyBundle\Manager\ProductManager;
use Youppers\CompanyBundle\Manager\CompanyManager;
use Youppers\CompanyBundle\YouppersCompanyBundle;
use Monolog\Logger;
use Youppers\CompanyBundle\Entity\ProductPrice;

abstract class AbstractLoader extends ContainerAware
{
    const BATCH_SIZE = 500;

    const FIELD_BRAND = 'brand';
    const FIELD_COLLECTION = 'collection';
    const FIELD_NAME = 'name';
    const FIELD_DESCRIPTION = 'desciption';
    const FIELD_CODE = 'code';
    const FIELD_GTIN = 'gtin';
    const FIELD_TYPE = 'type';
    const FIELD_RES = 'uri';

    /**
	 * @return \Youppers\CompanyBundle\Loader\LoaderMapper
	 */
	public abstract function createMapper();

    /**
     * @var LoaderMapper
     */
    protected $mapper;

    protected $managerRegistry;
	protected $em;
    /**
     * @var LoggerInterface
     */
	protected $logger;
    /**
     * @var Company
     */
	protected $company;
	protected $brand;
	protected $product;
	protected $fs;
	protected $enable;
	protected $force;

    protected $numRows;


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
     * @var CompanyManager
     */
    private $companyManager;

    /**
     * @return CompanyManager
     */
    protected function getCompanyManager() {
        if (empty($this->companyManager)) {
            $this->companyManager = $this->container->get('youppers.company.manager.company');
        }
        return $this->companyManager;
    }

    /**
     * @var ProductManager
     */
    private $productManager;

    /**
     * @return ProductManager
     */
    protected function getProductManager() {
        if (empty($this->productManager)) {
            $this->productManager = $this->container->get('youppers.company.manager.product');
        }
        return $this->productManager;
    }

    /**
     * @var BrandManager
     */
    private $brandManager;

    /**
     * @return BrandManager
     */
    protected function getBrandManager() {
        if (empty($this->brandManager)) {
            $this->brandManager = $this->container->get('youppers.company.manager.brand');
        }
        return $this->brandManager;
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

    public function checkUniqueGtin($product) {
        $gtin = $product->getGtin();
        if ($gtin == null) {
            return true;
        }
        $check = $this->getProductManager()->findOneByGtin($gtin);
        if ($check == null || $check == $product) {
            return true;
        } else {
            return false;
        }
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
