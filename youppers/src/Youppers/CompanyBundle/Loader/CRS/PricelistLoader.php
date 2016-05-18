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
			self::FIELD_CODE => 'Articolo',
			self::FIELD_NAME => 'Descrizione',
			self::FIELD_COLLECTION => 'Serie',
			self::FIELD_GTIN => 'Barcode',
			self::FIELD_PRICE => 'Prezzo',
			self::FIELD_UOM => 'Unità misura prezzo',
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	private $newCollectionProductType;

	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->getProductTypeManager()
				->findOneBy(array('code' => 'TAP'));
		}
		return $this->newCollectionProductType;
	}

}