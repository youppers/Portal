<?php
namespace Youppers\CompanyBundle\Loader;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class PricelistLoaderFactory extends ContainerAware
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
	public function create($code)
	{
		$pricelist = $this->managerRegistry->getRepository('YouppersCompanyBundle:Pricelist')->findOneBy(array('code' => $code));

		if (empty($pricelist)) {
			throw new \Exception('Pricelist not found');
		}

		$classname = sprintf("Youppers\CompanyBundle\Loader\%s\PricelistLoader",$pricelist->getCompany()->getCode());
		
		$this->logger->debug(sprintf("Code: '%s' Pricelist: '%s' Loader: '%s'", $code, $pricelist,$classname));
				
		$loader = new $classname;
		$loader->setManagerRegistry($this->managerRegistry);
		$loader->setLogger($this->logger);
		$loader->setContainer($this->container);
		$loader->setPricelist($pricelist);
		
		return $loader;
		
	}
	
}
