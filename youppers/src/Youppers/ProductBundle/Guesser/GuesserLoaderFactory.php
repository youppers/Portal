<?php
namespace Youppers\ProductBundle\Guesser;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\Criteria;

class GuesserLoaderFactory extends ContainerAware
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
	 * @param string $code The pricelist code
	 */
	public function create($companyCode,$brandCode,$collectionCode=null)
	{
		$company = $this->managerRegistry->getRepository('YouppersCompanyBundle:Company')->findOneBy(array('code' => $companyCode));

		if (empty($company)) {
			throw new \Exception(sprintf("Company '%s' not found",$companyCode));
		}

		$this->logger->debug(sprintf("Code: '%s' Company: '%s'", $companyCode, $company));
		
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq("code", $brandCode));
		
		$brand = $company->getBrands()->matching($criteria)->first();
		
		if (empty($brand)) {
			throw new \Exception(sprintf("Brand '%s' not found",$brandCode));
		}
		
		$this->logger->debug(sprintf("Code: '%s' Brand: '%s'", $brandCode, $brand));
				
		$classname = sprintf("Youppers\ProductBundle\Guesser\%s\VariantGuesser",$company->getCode());

        if (class_exists($classname)) {
            $this->logger->info(sprintf("Guesser: '%s'", $classname));
            $guesser = new $classname;
        } else {
            $this->logger->info("Using default Guesser");
            $guesser = new VariantGuesser();
        }
		$guesser->setManagerRegistry($this->managerRegistry);
		$guesser->setLogger($this->logger);
		$guesser->setContainer($this->container);
		$guesser->setCompany($company);
		$guesser->setBrand($brand);
		$guesser->setCollection($collectionCode);
		
		return $guesser;
		
	}
	
}
