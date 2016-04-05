<?php
namespace Youppers\CompanyBundle\Loader\JACUZZI;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\An6PricelistLoader;

class PricelistLoader extends An6PricelistLoader
{

	private $newCollectionProductType;

	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->getProductTypeManager()
				->findOneBy(array('code' => 'BATHTUB'));
		}
		return $this->newCollectionProductType;
	}

}