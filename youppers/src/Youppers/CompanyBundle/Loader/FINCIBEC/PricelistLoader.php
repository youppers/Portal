<?php
namespace Youppers\CompanyBundle\Loader\FINCIBEC;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	/**
	 * @return LoaderMapper
	 */
	// MARCHIO;SERIE;FORMATO;CODICE;COLORE;U.M.;1° € PALLET.; € SE;Pc. BOXES;m2 BOXES;Kg BOXES;COLLI BOXES PER PALLET;m2 PALLET;Kg PALLET;COLLI EUR;m2 EUR;Kg EUR;MLXCO;CODICE A BARRE
	public function createMapper()
	{
		$mapping = array(
			self::FIELD_BRAND => 'MARCHIO',
			self::FIELD_COLLECTION => 'SERIE',
			self::FIELD_CODE => 'CODICE',
			self::FIELD_NAME => 'COLORE', // la prima parola, segue descrizione
			self::FIELD_UOM => 'U.M.',
			self::FIELD_PRICE => '1° € PALLET.',
			self::FIELD_QUANTITY => 'Pc. BOXES',
			self::FIELD_SURFACE => 'm2 BOXES',
			self::FIELD_GTIN => 'CODICE A BARRE',
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