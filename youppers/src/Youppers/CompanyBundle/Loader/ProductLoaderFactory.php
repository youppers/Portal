<?php
namespace Youppers\CompanyBundle\Loader;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class ProductLoaderFactory extends ContainerAware
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
	 * @param string $code The Company Code
	 */
	public function create($code)
	{
		$company = $this->container->get('youppers.company.manager.company')->findOneBy(array('code' => $code));

		if (empty($company)) {
			throw new \Exception('Company not found');
		}

		$classname = sprintf("Youppers\CompanyBundle\Loader\%s\ProductLoader",$company->getCode());
		
		$this->logger->debug(sprintf("Code: '%s' Company: '%s' Loader: '%s'", $code, $company,$classname));
				
		$loader = new $classname;
        $loader->setContainer($this->container);
        $loader->setLogger($this->logger);

		$loader->setCompany($company);
		
		return $loader;
		
	}
	
}
