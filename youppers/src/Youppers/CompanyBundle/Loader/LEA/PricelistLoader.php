<?php
namespace Youppers\CompanyBundle\Loader\LEA;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
			self::FIELD_CODE => 'CODICE ART',
			self::FIELD_NAME => 'DESCRIZIONE ART',
			self::FIELD_COLLECTION => 'SERIE',
			self::FIELD_UOM => 'UM_VEN',
			self::FIELD_PRICE => 'PREZZO',
			self::FIELD_QUANTITY => 'PZ_SCA',
            self::FIELD_SURFACE => 'MQ_SCA',
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