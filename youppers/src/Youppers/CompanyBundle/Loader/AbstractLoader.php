<?php

namespace Youppers\CompanyBundle\Loader;

use Doctrine\Common\Collections\Criteria;
use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Stopwatch\Stopwatch;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Youppers\CompanyBundle\Entity\Company;
use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\CompanyBundle\Manager\BrandManager;
use Youppers\CompanyBundle\Manager\ProductManager;
use Youppers\CompanyBundle\Manager\CompanyManager;
use Youppers\ProductBundle\Manager\ProductCollectionManager;
use Youppers\ProductBundle\Manager\ProductTypeManager;
use Youppers\ProductBundle\Manager\ProductVariantManager;

use Youppers\CompanyBundle\YouppersCompanyBundle;
use Youppers\CompanyBundle\Entity\ProductPrice;

abstract class AbstractLoader extends ContainerAware
{
    const BATCH_SIZE = 500;

    const FIELD_BRAND = 'brand';
    const FIELD_COLLECTION = 'collection';
	const FIELD_COLLECTION_CODE = 'collection_code';
    const FIELD_NAME = 'name';
    const FIELD_DESCRIPTION = 'desciption';
    const FIELD_CODE = 'code';
    const FIELD_GTIN = 'gtin';
    const FIELD_TYPE = 'type';
    const FIELD_RES = 'uri';

	protected $disabledBrands = array();

	/**
	 * @var integer $skip
	 */
	protected $skip;

	/**
	 * @return \Youppers\CompanyBundle\Loader\LoaderMapper
	 */
	public abstract function createMapper();

    /**
     * @var LoaderMapper
     */
    protected $mapper;

    protected $managerRegistry;

	/**
	 * @var ObjectManager $em
	 */
	protected $em;

    /**
     * @var LoggerInterface
     */
	protected $logger;

    /**
     * @var Company $company
     */
	protected $company;

	/**
	 * @var Brand $brand
	 */
	protected $brand;

	/**
	 * @var Product
	 */
	protected $product;

	/**
	 * @var string $fs
	 */
	protected $fs;

	/**
	 * @var boolean $append
	 */
	protected $append;

	/**
	 * @var boolean $enable
	 */
	protected $enable;

	/**
	 * @var boolean $force
	 */
	protected $force;

	/**
	 * @var integer $numRows
	 */
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

	public function setAppend($append) {
		$this->append = $append;
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

    public function checkUniqueGtin(Product $product) {
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

	/**l
	 * @var ProductCollectionManager
	 */
	private $productCollectionManager;

	/**
	 * @return ProductCollectionManager
	 */
	protected function getProductCollectionManager() {
		if (empty($this->productCollectionManager)) {
			$this->productCollectionManager = $this->container->get('youppers.product.manager.product_collection');
		}
		return $this->productCollectionManager;
	}

	/**
	 * @var ProductVariantManager
	 */
	private $productVariantManager;

	/**
	 * @return ProductVariantManager
	 */
	protected function getProductVariantManager() {
		if (empty($this->productVariantManager)) {
			$this->productVariantManager = $this->container->get('youppers.product.manager.product_variant');
		}
		return $this->productVariantManager;
	}

	/**
	 * @var ProductTypeManager $productTypeManager
	 */
	private $productTypeManager;

	/**
	 * @return ProductTypeManager
	 */
	protected function getProductTypeManager() {
		if (empty($this->productTypeManager)) {
			$this->productTypeManager = $this->container->get('youppers.product.manager.product_type');
		}
		return $this->productTypeManager;
	}

	/**
	 * @param $filename
	 * @return CsvReader
	 */
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
	public function load($filename,$skip=0)
	{
		$this->skip = $skip;

		$reader = $this->createCsvReader($filename);

		$this->numRows = 0;

		$reader->setHeaderRowNumber(0);

		$this->serializer = $this->container->get('serializer');

		$this->mapper = $this->createMapper();

		$this->logger->info("Using mapper: " . $this->mapper);

		// speed up
		if (!$this->debug) {
			$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
		}

		$stopwatch = new Stopwatch();
		$stopwatch->start('load');
		foreach ($reader as $row) {

			$this->numRows++;

			if ($this->numRows == 1) {
				$this->logger->info('Column headers: ' . var_export($reader->getColumnHeaders(), true));
			}

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

	/**
	 * @return Brand
	 * @throws \Exception
	 */
	protected function handleBrand()
	{
		$brandCode = $this->mapper->remove(self::FIELD_BRAND);
		if (null !== $brandCode) {
			$this->setBrandByCode($brandCode);
		}

		if (empty($this->brand)) {
			if (empty($brandCode)) {
				throw new \Exception(sprintf("Brand column MUST be in the column '%s' OR must be set manually", $this->mapper->key(self::FIELD_BRAND)));
			}
			$brand = $this->getBrandManager()->findOneBy(array('company' => $this->company, 'code' => $brandCode));
			if (empty($brand)) {
				throw new \Exception(sprintf("At row '%d': Brand '%s' not found for Company '%s'", $this->numRows, $brandCode, $this->company));
			}
			$this->brand = $brand;
		} else {
			return $this->brand;
		}
	}

	/**
	 * @param $row
	 * @return mixed
	 * Called for each row
	 */
	protected abstract function handleRow($row);

	/**
	 * @return mixed
	 * Called each BATCH_SIZE rows and after the last row
	 * Should perform clear or flush
	 */
	protected abstract function batch();
}
