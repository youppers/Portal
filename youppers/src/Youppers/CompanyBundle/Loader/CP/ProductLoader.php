<?php
namespace Youppers\CompanyBundle\Loader\CP;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractProductLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class ProductLoader extends AbstractProductLoader
{
	public function createMapper()
	{
		$mapping = array(
			// 'brand' => 'MARCHIO',  // Brand unico, specificare il brand usando "-b CP"
			self::FIELD_CODE => 'ARTICOLO',
			self::FIELD_COLLECTION => 'SERIE'
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	private $newCollectionProductType;

	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->getProductTypeManager()
				->findOneBy(array('code' => 'TILE'));
		}
		return $this->newCollectionProductType;
	}
	
}