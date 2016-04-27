<?php
namespace Youppers\CompanyBundle\Loader\CP;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
            // 'brand' => 'MARCHIO',  // Brand unico, specificare il brand usando "loader CP CP"
			self::FIELD_CODE => 'ARTICOLO',
			self::FIELD_NAME => 'DESCRIZIONE',
			self::FIELD_COLLECTION => 'SERIE',
			self::FIELD_UOM => 'UM',
			self::FIELD_PRICE => '1A SC.',
			self::FIELD_QUANTITY => 'PZ/SC',
			self::FIELD_SURFACE => 'MQ',
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