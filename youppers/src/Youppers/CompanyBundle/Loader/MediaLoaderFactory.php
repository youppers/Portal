<?php
namespace Youppers\CompanyBundle\Loader;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class MediaLoaderFactory extends ContainerAware
{

	/**
	 * 
	 * @param string $code The Company Code
	 */
	public function create($code)
	{
        $logger = $this->container->get('logger');
		$company = $this->container->get('youppers.company.manager.company')->findOneBy(array('code' => $code));

		if (empty($company)) {
			throw new \Exception('Company not found');
		}

		$classname = sprintf("Youppers\CompanyBundle\Loader\%s\MediaLoader",$company->getCode());
		
		$logger->debug(sprintf("Code: '%s' Company: '%s' Loader: '%s'", $code, $company,$classname));
				
		$loader = new $classname;
        $loader->setContainer($this->container);
        $loader->setLogger($logger);

		$loader->setCompany($company);
		
		return $loader;
		
	}
	
}
