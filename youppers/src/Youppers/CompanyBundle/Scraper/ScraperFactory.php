<?php
namespace Youppers\ScraperBundle\Scraper;

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
	public function create($companyCode,$brandCode)
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
        } else {
            $brand = null;
        }
				
		$classname = sprintf("Youppers\ScraperBundle\Scraper\%s\Scraper",$company->getCode());
		
		$this->logger->debug(sprintf("Scraper: '%s'", $classname));
				
		$scraper = new $classname;
		$scraper->setManagerRegistry($this->managerRegistry);
		$scraper->setLogger($this->logger);
		$scraper->setContainer($this->container);
		$scraper->setCompany($company);
		$scraper->setBrand($brand);
		
		return $scraper;
		
	}
	
}
