<?php
namespace Youppers\CompanyBundle\Loader;

use JMS\Serializer\Serializer;

class MappingPricelistLoader extends AbstractPricelistLoader
{
	/**
	 * Load mapper from mapping field in priclist entity
	 * @see \Youppers\CompanyBundle\Loader\AbstractLoader::createMapper()
	 */
	public function createMapper()
	{
		$serializer = $this->container->get('serializer');
		$mapper = $serializer->deserialize('{"mapping":'.$this->pricelist->getMapping().'}','Youppers\CompanyBundle\Loader\LoaderMapper','json');
		print_r($mapper);
		return $mapper;
	}
	
}
