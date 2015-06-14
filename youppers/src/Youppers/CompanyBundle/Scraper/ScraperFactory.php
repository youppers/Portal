<?php
namespace Youppers\CompanyBundle\Scraper;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\Criteria;

class ScraperFactory extends ContainerAware
{
	protected $managerRegistry;
	protected $logger;

	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;
		$this->logger = $logger;
	}

	/**
	 * 
	 * @param string $companyCode
	 * @param string $brandCode
	 * @param string $collectionCode
	 * @throws \Exception
	 * @return AbstractScraper
	 */
	public function create($companyCode,$brandCode,$collectionCode)
	{
		$company = $this->managerRegistry->getRepository('YouppersCompanyBundle:Company')->findOneBy(array('code' => $companyCode));

		if (empty($company)) {
			throw new \Exception(sprintf("Company '%s' not found",$companyCode));
		}

		$this->logger->debug(sprintf("Code: '%s' Company: '%s'", $companyCode, $company));

        if ($brandCode) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq("code", $brandCode));

            $brand = $company->getBrands()->matching($criteria)->first();

            if (empty($brand)) {
                throw new \Exception(sprintf("Brand '%s' not found", $brandCode));
            }

            $this->logger->debug(sprintf("Code: '%s' Brand: '%s'", $brandCode, $brand));

            if ($collectionCode) {
                $collection = $this->managerRegistry->getRepository('YouppersProductBundle:ProductCollection')->findOneBy(array('brand' => $brand, 'code' => $collectionCode));
                if (empty($collection)) {
                    throw new \Exception(sprintf("Collection '%s' not found", $collectionCode));
                }
            } else {
                $collection = null;
            }
        } else {
            $brand = null;
            $collection = null;
        }
				
		$classname = sprintf("Youppers\CompanyBundle\Scraper\%s\Scraper",$company->getCode());
		
		$this->logger->debug(sprintf("Scraper: '%s'", $classname));
				
		$scraper = new $classname;
		$scraper->setManagerRegistry($this->managerRegistry);
		$scraper->setLogger($this->logger);
		$scraper->setContainer($this->container);
		$scraper->setCompany($company);
		if ($brand) $scraper->setBrand($brand);
        if ($collection) $scraper->setCollection($collection);

		return $scraper;
		
	}
	
}
