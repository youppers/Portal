<?php
namespace Youppers\CompanyBundle\Scraper;

use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

use Youppers\CompanyBundle\Entity\Company;
use Youppers\CompanyBundle\Entity\Brand;
use Youppers\ProductBundle\Entity\ProductCollection;

use GuzzleHttp\Message\Request;

abstract class AbstractScraper extends ContainerAware
{
	public abstract function scrape();

    public abstract function scrapeBrand(Brand $brand);

    public abstract function scrapeCollection(ProductCollection $collection);

    private $parent = null;
	
	protected function setParent(AbstractScraper $parent)
	{
		$this->parent = $parent;
	}
	
	protected $managerRegistry;
	protected $em;
	
	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
		$this->em = $managerRegistry->getManager();
	}
	
	public function getManagerRegistry()
	{
		if ($this->parent) {
			return $this->parent->getManagerRegistry();
		} else {
			return $this->managerRegistry;
		}
	}
	
	protected $logger;
	
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
	
	public function getLogger()
	{
		if ($this->parent) {
			return $this->parent->getLogger();
		} else {
			return $this->logger;
		}
	}
		
	protected $force = false;
	
	public function setForce($force) {
		$this->force = $force;
	}

	protected $enable = false;
	
	public function setEnable($enable) {
		$this->enable = $enable;
	}
	
	
	protected $debug = false;
	
	public function setDebug($debug)
	{
		$this->debug = $debug;
	}
	
	protected $company = null;
	
	public function setCompany(Company $company)
	{
		$this->company = $company;
	}
	
	protected $brand = null;
	
	public function setBrand(Brand $brand)
	{
		$this->brand = $brand;
	}

    protected $collection = null;

    public function setCollection(ProductCollection $collection)
    {
        $this->collection = $collection;
    }

}
