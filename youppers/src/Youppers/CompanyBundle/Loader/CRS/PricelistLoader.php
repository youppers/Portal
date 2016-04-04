<?php
namespace Youppers\CompanyBundle\Loader\CRS;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		// ID;Nr. articolo;SERIE;DEC ITA; L 2015/16 ;Data inizio E15; Peso netto kg
		$mapping = array(
			self::FIELD_CODE => 'Nr. articolo',
			self::FIELD_NAME => 'DEC ITA',
			self::FIELD_COLLECTION => 'SERIE',
			self::FIELD_PRICE => 'L 2015/16',
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	private $newCollectionProductType;

	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->getProductTypeManager()
				->findOneBy(array('code' => 'Rubinetteria'));
		}
		return $this->newCollectionProductType;
	}

}